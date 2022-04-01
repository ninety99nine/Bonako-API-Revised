<?php

namespace App\Repositories;

use Exception;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use App\Exceptions\InvalidPerPageException;
use App\Services\Sorting\RepositorySorting;
use App\Services\Filtering\RepositoryFilter;
use App\Exceptions\InvalidPaginateException;
use App\Exceptions\DeleteConfirmationCodeInvalid;
use App\Exceptions\RepositoryQueryFailedException;
use App\Exceptions\RepositoryModelNotFoundException;
use Illuminate\Http\Request;

abstract class BaseRepository
{
    protected $model;
    protected $formRequest;
    protected $paginate = true;
    protected $transform = true;
    protected $collection = null;
    protected $createIgnoreFields = [];
    protected $updateIgnoreFields = [];
    protected $requiresConfirmationBeforeDelete = false;

    //  Limit the total results to 15 items by default
    protected $perPage = 15;

    /**
     *  Overide this repository Eloquent class name.
     *  This represents the Model name to use
     *  whenever we want to refer to this Model
     *  plainly. This name is also used to
     *  describe exception messages.
     */
    protected $modelName;

    /**
     *  Overide this repository Eloquent Model class name.
     *  This represents the Model to target for this
     *  repository instance.  If this is not provided
     *  we will implicity define the class name
     */
    protected $modelClass;

    /**
     *  Overide this repository Resource class name.
     *  This represents the Resource used to transform
     *  the Model repository instance.  If this is not
     *  provided we will implicity define the class
     *  name
     */
    protected $resourceClass;

    /**
     *  Overide this repository Resources class name.
     *  This represents the Resource used to transform
     *  a collection of Model repository instances. If
     *  this is not provided we will implicity define
     *  the class name
     */
    protected $resourceCollectionClass;

    /**
     *  First thing is first, we need to set the Eloquent Model Instance of
     *  the target model class so that we can use the repository methods
     */
    public function __construct()
    {
        $this->setModel();
    }

    /**
     *  Set the model by resolving the provided Eloquent
     *  class name from the service container e.g
     *
     *  $this->model = resolve(App\Models\Store)
     *
     *  This means that our property "$this->model" is
     *  now an Eloquent Model Instance of Store.
     *
     *  Sometimes we can just pass our own specific model
     *  instance by passing it as a parameter e.g passing
     *  a "Store" model with id "1"
     *
     *  e.g $model = Store::find(1)
     *
     *  Or we can pass a model Eloquent Builder
     *
     *  e.g $model = User::find(1)->stores()
     *
     *  This is helpful to set an Eloquent Builder instance
     *  then chain the get() method to pull the query results.
     */
    public function setModel($model = null) {

        if( ($model !== null) || ($this->model === null) ) {

            $this->model = $model ? $model : resolve($this->getModelClass());

        }

        /**
         *  Return the Repository Class instance. This is so that we can chain other
         *  methods if necessary
         */
        return $this;
    }

    private function getModelClass() {
        if( $this->modelClass === null ) {
            return $this->getFallbackModelClass();
        }
        return $this->getProvidedModelClass();
    }

    private function getProvidedModelClass() {
        /**
         *  Get the sub-class Eloquent Model class name, for instance,
         *  $this->resourceClass = Store::class"
         */
        return $this->modelClass;
    }

    private function getFallbackModelClass() {
        /**
         *  If the sub-class name is "StoreRepository", then replace the
         *  word "Repository" with nothing and append the class path.
         *
         *  Return a fully qualified class path e.g App\Models\Store
         */
        return 'App\Models\\' . Str::replace('Repository', '', class_basename($this));
    }

    private function getResourceClass() {
        if( $this->resourceClass === null ) {
            return $this->getFallbackResourceClass();
        }
        return $this->getProvidedResourceClass();
    }

    private function getProvidedResourceClass() {
        /**
         *  Get the sub-class Resource class name, for instance,
         *  $this->resourceClass = Store::class"
         */
        return $this->resourceClass;
    }

    private function getFallbackResourceClass() {
        /**
         *  If the sub-class name is "StoreRepository", then replace the
         *  word "Repository" with "Resource" and append the class path.
         *
         *  Return a fully qualified class path e.g App\Http\Resources\StoreResource
         */
        return '\App\Http\Resources\\' . Str::replace('Repository', 'Resource', class_basename($this));
    }



    private function getResourceCollectionClass() {
        if( $this->resourceCollectionClass === null ) {
            return $this->getFallbackResourceCollectionClass();
        }
        return $this->getProvidedResourceCollectionClass();
    }

    private function getProvidedResourceCollectionClass() {
        /**
         *  Get the sub-class Resource class name, for instance,
         *  $this->resourceCollectionClass = Store::class"
         */
        return $this->resourceCollectionClass;
    }

    private function getFallbackResourceCollectionClass() {
        /**
         *  If the sub-class name is "StoreRepository", then replace the
         *  word "Repository" with "Resources" and append the class path.
         *
         *  Return a fully qualified class path e.g App\Http\Resources\StoreResources
         */
        return '\App\Http\Resources\\' . Str::replace('Repository', 'Resources', class_basename($this));
    }




    private function getModelName() {
        if( $this->modelName === null ) {
            return $this->getFallbackModelName();
        }
        return $this->getProvidedModelName();
    }

    public function getModelNameInLowercase() {
        return strtolower($this->getModelName());
    }

    private function getProvidedModelName() {
        /**
         *  Get the provided model name e.g user, store, order
         *  Trim and lowercase the model name
         */
        return Str::of(Str::lower($this->modelName))->trim();
    }

    private function getFallbackModelName() {
        /**
         *  If the sub-class name is "StoreRepository", then remove the
         *  word "Repository" from the class base name and assume
         *  the remaining characters to be the name of the
         *  Eloquent Model Name to target i.e "Store"
         */
        return Str::of(Str::replace('Repository', '', class_basename($this)))->trim();
    }

    private function handleSearch() {

        //  Get the search word
        $searchWord = request()->input('search');

        //  if we have a search word
        if( !empty( $searchWord ) ){

            //  Limit the model to the search scope
            $this->model = $this->model->search($searchWord);

        }

    }

    private function handleFilters() {

        //  Resolve and attempt to apply filters on this repository model instance
        $this->model = resolve(RepositoryFilter::class)->apply($this->model);

    }

    private function handleSorting() {

        //  Resolve and attempt to apply sorting on this repository model instance
        $this->model = resolve(RepositorySorting::class)->apply($this->model);

    }

    private function handlePaginationInput() {

        //  If we want to overide the pagination
        if( request()->has('paginate') ) {

            //  If the paginate value is not provided
            if( !request()->filled('paginate') ) throw new InvalidPaginateException();

            //  Check if the paginate value is true or false
            $canPaginate = in_array(request()->input('paginate'), [true, false, 'true', 'false'], true);

            //  If the paginate value is not true or false
            if( !$canPaginate ) throw new InvalidPaginateException();

            //  Set the overide paginate value
            $this->paginate = in_array(request()->input('paginate'), [true, 'true'], true) ? true : false;

        }

    }

    private function handleTransformation() {


    }

    private function handlePerPageInput() {

        //  If we want to overide the default total
        if( request()->has('per_page') ) {

            //  If the per page value is not provided
            if( !request()->filled('per_page') ) throw new InvalidPerPageException();

            //  If the per page value is not a valid number
            if( !is_numeric( request()->input('per_page') ) ) throw new InvalidPerPageException();

            //  If the per page value is 0 or less
            if( request()->input('per_page') <= 0 ) throw new InvalidPerPageException('The per page value must be greater than zero (0) in order to limit the results');

            //  If the per page value must not exceed 100
            if( request()->input('per_page') > 100 ) throw new InvalidPerPageException('The per page value must not exceed 100 in order to limit the results');

            //  Set the overide per page value
            $this->perPage = (int) request()->input('per_page');

        }

    }

    /**
     *  Find and set specific repository model instance.
     *  Return this instance if found.
     *  Return exception if not found.
     */
    public function find($id) {

        //  If we have the repository model instance
        if( $this->model = $this->model->where('id', $id)->first() ){

            /**
             *  Return the Repository Class instance. This is so that we can chain other
             *  methods e.g to Transform the Model for external consumption.
             */
            return $this;

        }else{

            //  Throw an Exception
            throw new RepositoryModelNotFoundException('This '.strtolower($this->getModelName(), ).' does not exist');

        }

    }

    /**
     *  Find and return specific repository model instance.
     *  Return exception if not found
     */
    public function findModel($id) {

        return $this->find($id)->model;

    }

    /**
     *  Find and return transformed repository model instance.
     *  Return exception if not found
     */
    public function findAndTranform($id) {

        return $this->find($id)->transform();

    }

    /**
     *  Retrieve a fresh instance of the model.
     *  Return this instance.
     */
    public function refreshModel() {

        $this->setModel( $this->model->fresh() );
        return $this;

    }

    /**
     *  Get repository model instances.
     */
    public function get() {

        try {

            //  Filter the model instance
            $output = $this->handleSearch();

            //  Filter the model instance
            $output = $this->handleFilters();

            //  Sort the model instance
            $output = $this->handleSorting();

            //  Filter the model instance
            $output = $this->handleTransformation();

            //  Handle the url per page input
            $this->handlePerPageInput();

            //  Handle the url paginate input
            $this->handlePaginationInput();

            //  If we want to paginate the collection
            if( $this->paginate ) {

                //  Initialise a paginated collection
                $this->collection = $this->model->paginate($this->perPage);

            }else{

                //  Initialise a collection
                $this->collection = $this->model->take($this->perPage)->get();

            }

            return $this;

        //  If we failed to perform the query
        } catch (\Illuminate\Database\QueryException $e){

            report($e);

            throw new RepositoryQueryFailedException('Could not get the '.$this->getModelNameInLowercase().' records because the Database Query failed. Make sure that your filters and sorting functionality is correctly set especially when targeting nested relationships');

        }

    }

    /**
     *  Create new repository model instance.
     */
    public function create($data = []) {
        try {

            if($data instanceof Request) {

                $data = $data->all();

            }elseif($data instanceof Model) {

                $fillables = $data->getFillable();
                $data = $data->getAttributes();

            }

            $fillables = isset($fillables) ? $fillables : $this->model->getFillable();

            //  Get the permitted fields for creating a model
            $data = collect($data)->only($fillables)->except($this->createIgnoreFields)->all();

            //  If we have data
            if( !empty($data) ){

                //  Set repository model after creating and retrieving a fresh model instance
                $this->model = $this->model->create($data)->fresh();

            }else{

                throw new Exception('Could not create '.$this->getModelNameInLowercase().' because no data was provided.');

            }

            /**
             *  Return the Repository Class instance. This is so that we can chain other
             *  methods e.g to Transform the Model for external consumption.
             */
            return $this;

        } catch (\Throwable $th) {

            throw $th;

        }
    }

    /**
     *  Update existing repository model instance.
     */
    public function update($data = []) {
        try {

            if($data instanceof Request) {

                $data = $data->all();

            }elseif($data instanceof Model) {

                $fillables = $data->getFillable();
                $data = $data->getAttributes();

            }

            $fillables = isset($fillables) ? $fillables : $this->model->getFillable();

            //  Get the permitted fields for creating a model
            $data = collect($data)->only($fillables)->except($this->createIgnoreFields)->all();

            //  If we have data
            if( !empty($data) ){

                //  Update repository model
                $updated = $this->model->update($data);

                //  If we updated this repository model
                if( $updated ){

                    //  Set repository model
                    $this->model = $this->model->fresh();

                }

                /**
                 *  Return the Repository Class instance. This is so that we can chain other
                 *  methods e.g to Transform the Model for external consumption.
                 */
                return $this;

            }else{

                throw new Exception('Could not update '.$this->getModelNameInLowercase().' because no data was provided.');

            }

        } catch (RepositoryModelNotFoundException $e) {

            throw new RepositoryModelNotFoundException('Could not update '.$this->getModelNameInLowercase().' because it does not exist.');

        } catch (\Throwable $th) {

            throw $th;

        }
    }

    /**
     *  Delete existing repository model instance.
     */
    public function delete() {
        try {

            //  If this requires confirmation before delete
            if( $this->requiresConfirmationBeforeDelete ){

                //  Confirm that the user can delete this model
                $this->confirmDeleteConfirmationCode();

                //  Remove the code
                $this->removeDeleteConfirmationCode();

            }

            return $this->model->delete();

        } catch (RepositoryModelNotFoundException $e) {

            throw new RepositoryModelNotFoundException('Could not delete '.$this->getModelNameInLowercase().' because it does not exist.');

        } catch (\Throwable $th) {

            throw $th;

        }
    }

    /**
     *  Generate the code required to delete important assets
     */
    public function generateDeleteConfirmationCode()
    {
        //  Generate random 6 digit number
        $code = $this->generateRandomSixDigitCode();

        //  Cache the new code for exactly 1 day
        Cache::add($this->getDeleteConfirmationCodeCacheName(), $code, now()->addDay());

        return [
            'message' => 'Enter the confirmation code "'.$code.'" to confirm deleting this ' . $this->getModelNameInLowercase(),
            'code' => $code
        ];
    }

    /**
     *  Confirm the code required to delete important assets
     */
    public function confirmDeleteConfirmationCode()
    {
        $code = request()->input('code');

        if( Cache::has($this->getDeleteConfirmationCodeCacheName()) ){

            if( $code == Cache::get($this->getDeleteConfirmationCodeCacheName()) ) {

                return true;

            }else{

                throw new DeleteConfirmationCodeInvalid('The confirmation code "'.$code.'" is invalid.');

            }

        }else{

            throw new DeleteConfirmationCodeInvalid('The confirmation code "'.$code.'" has expired.');

        }
    }

    /**
     *  Remove the code required to delete important assets
     */
    public function removeDeleteConfirmationCode()
    {
        Cache::forget($this->getDeleteConfirmationCodeCacheName());
    }

    /**
     *  Generate the code required to delete important assets
     */
    public function getDeleteConfirmationCodeCacheName()
    {
        /**
         *  If the $model is a store with id equal to "5", then
         *  the returned result must be "DELETE_STORE_5_1"
         *
         *  If the $model is an order with id equal to "5", then
         *  the returned result must be "DELETE_ORDER_5_1"
         */
        return 'DELETE_'.strtoupper(class_basename($this->model)).'_'.$this->model->id.'_'.auth()->user()->id;
    }

    /**
     *  Generate a random 6 digit number
     */
    public function generateRandomSixDigitCode()
    {
        return rand(100000, 999999);
    }

    /**
     *  Transform the repository model instance.
     */
    public function transform($additionalParams = []) {

        if( !is_null($this->collection) ) {

            /**
             *  The resource parameter must be set to the current collection instance
             *  so that we can transform the collection data for external consumption.
             */

            $class = $this->getResourceCollectionClass();
            $params = ['resource' => $this->collection];

        }else{

            /**
             *  The resource parameter must be set to the current model instance
             *  so that we can transform the model data for external consumption.
             *
             *  Additional params can be passed on to the constructor of the
             *  resource transformer to offer more flexibility
             *
             *  e.g
             *
             *  $additionalParams = ['checkingAccountExistence' => true];
             *
             *  The "key" of the additional param must match the constructor variable
             *  name while the value must be the intended value to pass on.
             *
             *  e.g Inside a Resource Class we can overide the contructor like so:
             *
             *  public function __construct($resource, $checkingAccountExistence = false){
             *      ...
             *  }
             */
            $class = $this->getResourceClass();
            $params = array_merge(['resource' => $this->model], $additionalParams);

        }

        return resolve($class, $params);

    }

}
