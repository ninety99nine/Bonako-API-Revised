<?php

namespace App\Repositories;

use Exception;
use Illuminate\Support\Str;
use App\Exceptions\InvalidPerPageException;
use App\Services\Sorting\RepositorySorting;
use App\Services\Filtering\RepositoryFilter;
use App\Exceptions\InvalidPaginateException;
use App\Exceptions\RepositoryQueryFailedException;
use App\Exceptions\RepositoryModelNotFoundException;
use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository
{
    protected $model;
    protected $formRequest;
    protected $paginate = true;
    protected $transform = false;
    protected $createIgnoreFields = [];
    protected $updateIgnoreFields = [];

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

    public function __construct() {
        /**
         *  First thing is first, we need to set the Eloquent Model Instance of
         *  the target model class so that we can use the repository methods
         */
        $this->setModel();

    }

    private function setModel() {

        //  If we do not have a model set
        if( !$this->model ){

            /**
             *  Set the model by resolving the provided Eloquent
             *  class name from the service container e.g
             *
             *  $this->model = resolve(App\Models\Car)
             *
             *  This means that our property "$this->model" is
             *  now an Eloquent Model Instance of Car.
             */
            $this->model = resolve($this->getModelClass());

        }
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
         *  $this->resourceClass = Car::class"
         */
        return $this->modelClass;
    }

    private function getFallbackModelClass() {
        /**
         *  If the sub-class name is "CarRepository", then replace the
         *  word "Repository" with nothing and append the class path.
         *
         *  Return a fully qualified class path e.g App\Models\Car
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
         *  $this->resourceClass = Car::class"
         */
        return $this->resourceClass;
    }

    private function getFallbackResourceClass() {
        /**
         *  If the sub-class name is "CarRepository", then replace the
         *  word "Repository" with "Resource" and append the class path.
         *
         *  Return a fully qualified class path e.g App\Http\Resources\CarResource
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
         *  $this->resourceCollectionClass = Car::class"
         */
        return $this->resourceCollectionClass;
    }

    private function getFallbackResourceCollectionClass() {
        /**
         *  If the sub-class name is "CarRepository", then replace the
         *  word "Repository" with "Resources" and append the class path.
         *
         *  Return a fully qualified class path e.g App\Http\Resources\CarResources
         */
        return '\App\Http\Resources\\' . Str::replace('Repository', 'Resources', class_basename($this));
    }




    private function getModelName() {
        if( $this->modelName === null ) {
            return $this->getFallbackModelName();
        }
        return $this->getProvidedModelName();
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
         *  If the sub-class name is "CarRepository", then remove the
         *  word "Repository" from the class base name and assume
         *  the remaining characters to be the name of the
         *  Eloquent Model Name to target i.e "Car"
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
                $output = $this->model->paginate($this->perPage);

            }else{

                //  Initialise a collection
                $output = $this->model->take($this->perPage)->get();

            }

            //  If we want to transform the collection
            if( $this->transform ) {

                //  Initialise a transformed collection
                $output = $this->transformCollection( $output );

            }

            return $output;

        //  If we failed to perform the query
        } catch (\Illuminate\Database\QueryException $e){

            report($e);

            throw new RepositoryQueryFailedException('Could not get the '.strtolower($this->getModelName()).' records because the Database Query failed. Make sure that your filters and sorting functionality is correctly set especially when targeting nested relationships');

        }

    }

    /**
     *  Create new repository model instance.
     */
    public function create($data = []) {
        try{

            //  Get the permitted fields for creating a model
            $data = collect($data)->except($this->createIgnoreFields)->all();

            //  If we have data
            if( !empty($data) ){

                //  Set repository model after creating and retrieving a fresh model instance
                $this->model = $this->model->create($data)->fresh();

            }else{

                throw new Exception('Could not create '.strtolower($this->getModelName()).' because no data was provided.');

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
    public function update($id, $data = []) {
        try {

            //  Get the permitted fields for updating a model
            $data = collect($data)->except($this->updateIgnoreFields)->all();

            //  If we have data
            if( !empty($data) ){

                //  Update repository model
                $updated = $this->findModel($id)->update($data);

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

                throw new Exception('Could not update '.strtolower($this->getModelName()).' because no data was provided.');

            }

        } catch (RepositoryModelNotFoundException $e) {

            throw new RepositoryModelNotFoundException('Could not update '.strtolower($this->getModelName()).' because it does not exist.');

        } catch (\Throwable $th) {

            throw $th;

        }
    }

    /**
     *  Delete existing repository model instance.
     */
    public function delete($id) {
        try {

            return $this->findModel($id)->delete();

        } catch (RepositoryModelNotFoundException $e) {

            throw new RepositoryModelNotFoundException('Could not delete '.strtolower($this->getModelName()).' because it does not exist.');

        } catch (\Throwable $th) {

            throw $th;

        }
    }

    /**
     *  Transform the repository model instance.
     */
    public function transform($additionalParams = []) {

        /**
         *  The resource parameter must be set to the current model instance
         *  so that we can transform the model data for external consumption.
         *
         *  Additional params can be passed on to the constructor of the
         *  resource transformer to offer more flexibility
         *
         *  e.g
         *
         *  $additionalParams = ['viewAsGuest' => true];
         *
         *  The "key" of the additional param must match the constructor variable
         *  name while the value must be the intended value to pass on.
         *
         *  e.g Inside a Resource Class we can overide the contructor like so:
         *
         *  public function __construct($resource, $viewAsGuest = false){
         *      ...
         *  }
         */
        $params = array_merge(['resource' => $this->model], $additionalParams);

        return resolve($this->getResourceClass(), $params);

    }

    /**
     *  Transform the repository model collection.
     */
    public function transformCollection($collection) {

        return resolve($this->getResourceCollectionClass(), ['resource' => $collection]);

    }

}
