<?php

namespace App\Services\Sorting;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Exceptions\InvalidSortingException;

/**
 *  How To Use The Sorting Service
 *  ------------------------------
 *
 *  In order to sort, the url must contain a query parameter called "sort".
 *  This query must contain the sorting logic in the form of Json data e.g
 *
 *  (1) JAVASCRIPT
 *
 *  var sort = {
 *      "amount": "desc"
 *  }
 *
 *  The above means that we want to sort the records by the "amount" in descending order
 *
 *  var sort = {
 *      "name": "asc",
 *      "amount": "desc",
 *  }
 *
 *  The above means that we want to sort the records by the "name" in asscending order
 *  and then by the "amount" in descending order
 *
 *  You can also sort by dates in the following ways:
 *
 *  var sort = {
 *      "updated_at": "latest"
 *  }
 *
 *  This will assume that the "updated_at" is a "date" and will be converted to Carbon instance
 *
 *  var sort = {
 *      "created_at": "oldest"
 *  }
 *
 *  This will assume that the "created_at" is a "date" and will be converted to Carbon instance
 *
 *  And you can sort the records randomly in this way:
 *
 *  var sort = "random"
 *
 *  About Parsing
 *  ---------------------
 *
 *  Remember that this Json data will be attached to the URL
 *  (Unless you are using sort=random) e.g
 *
 *  "/api?sort=random"
 *
 *  or
 *
 *  "/api?sort={JSON OBJECT}"
 *
 *  But we must first url encode the "{JSON OBJECT}" so that it can be parsed correctly.
 *  When attaching the "{JSON OBJECT}" on javascript we need to use the JSON.stringify()
 *  to convert the "{JSON OBJECT}" to a literal "JSON String". We must then use
 *  encodeURIComponent() to parse the JSON String to a valid paramemter on
 *  our url. The final result on Javascript would look something like this:
 *
 *  var myJsonSorting = {
 *      "name": "asc",
 *      "amount": "desc",
 *  }
 *
 * "/api?sort=" + encodeURIComponent(JSON.stringify(myJsonSorting));
 *
 *  (2) PHP
 *
 *  To do the same thing we just did but on PHP we would start with an array
 *  such as the following:
 *
 *  $myArraySorting = [
 *      "name": "asc",
 *      "amount": "desc"
 *  ]
 *
 * "/api?sort=" . urlencode(json_encode($myArraySorting));
 *
 *  The above first convert the array to a valid JSON String using json_encode(),
 *  then we parse this JSON String into a valid url parameter using urlencode()
 *
 *  Examples Of Sorting
 *  ---------------------
 *
 *  We can sort our data in the following ways. Each example assumes parsing the
 *  url parameters from JSON format, but its easy to redo everything in Array
 *  format:
 *
 *  "...?sort={"name: "asc"}                    Order by the "name" in ascending order
 *  "...?sort={"amount: "desc"}                 Order by the "amount" in descending order
 *  "...?sort={ "updated_at": "latest"}         Order by the "updated_at" date in descending order
 *  "...?sort={ "created_at": "oldest"}         Order by the "created_at" date in ascending order
 *  "...?sort="random"                          Order randomly
 *
 *  NOTE: You can always use more than one sorting  e.g
 *
 *  If we want to sort entities by "name" and then by "amount",
 *  then we can do the following:
 *
 *  sort = {
 *      "name: "asc"
 *      "amount: "desc"
 *  }
 *
 *  Nested Relationships
 *  --------------------
 *
 *  Consider the following examples. The first is how we would sort
 *  assuming that we wanted to target the column on the current model,
 *  however the 2nd scenerio assumes that we first need to target the
 *  "orders" relationship then query that, while the 3rd scenerio
 *  assumes that we first need to target the default location,
 *  then the orders of that location, then we perform the
 *  actual query to sort.
 *
 *  (1) ...?sort={"amount" : "desc"}
 *
 *  (2) ...?sort={"orders.amount" : "desc"}
 *
 *      $query->with('orders', function (Builder $query) {
 *          //  Do the query stuff here
 *      });
 *
 *  (3) ...?sort={"defaultLocation.orders.amount" : "desc"}
 *
 *      $query->with('defaultLocation.orders', function (Builder $query) {
 *          //  Do the query stuff here
 *      });
 *
 *  This means we need to pass the nested relationship as
 *  part of our parameter naming convention e.g
 *
 *  sort = {
 *      "orders.amount" : "desc"
 *  }
 *
 *  or
 *
 *  sort = {
 *      "defaultLocation.orders.amount" : "desc"
 *  }
 */
class RepositorySorting
{
    private $operations = [];

    public function __construct() {

        // dd(
        //     urlencode(json_encode([
        //         'name' => 'desc'
        //     ]))
        // );

        //  Set the sort operations
        $this->setSortingOperations();

    }

    private function setSortingOperations(){

        /**
         *  For a given request we can have one of the following scenerios:
         *
         *  "...?sort={"name: "asc"}                    Order by the "name" in ascending order
         *  "...?sort={"amount: "desc"}                 Order by the "amount" in descending order
         *  "...?sort={ "updated_at": "latest"}         Order by the "updated_at" date in descending order
         *  "...?sort={ "created_at": "oldest"}         Order by the "created_at" date in ascending order
         *  "...?sort="random"                          Order randomly
         *
         * when we access the sort request input, we return "random" or an array as follows:
         *
         *  [
         *      'orders.amount' =>                          //  This is the target (We want to sort using the order relationship amount)
         *          'desc'   //  This is the direction (We want to sort in descending order)
         *      ],
         *      ...                                         //  If we have more sorting operations
         *  ]
         *
         *  Heres another example using more sorting operations (sorting the order amount and status at the same time)
         *
         *  [
         *      'orders.amount' => [                        //  This is the target (We want to sort the order relationship amount)
         *          'whereIn' => ['pending', 'unpaid'],     //  This is the second sort (Must be a status of paid or pending),
         *      ],
         *      'orders.status' => [                        //  This is the target (We want to sort the order relationship amount)
         *          'whereBetween' => [50.00, 100.00],      //  This is the first sort (Must be between 50.00 and 100.00),
         *      ],
         *      ...                                         //  If we have more sorting operations
         *  ]
         *
         *  From the above scenerios the url field name could be one of two scenerios
         *
         *  (1) The field name is one word e.g "amount"
         *  (2) The field name is multiple words e.g "orders.amount"
         *      or "defaultLocation.orders.amount", with each word
         *      separated by the "." symbol
         *
         *  We need to check if we have one or multiple words as the field name.
         *  If we have multiple words, then the last word is the model column name
         *  while the rest are nested relationships.
         *
         *  We need to convert the url field to an array by splitting the value on the "." symbol
         *
         *  "amount"                            to      ["amount"]
         *  "orders.amount"                     to      ["orders", "amount"]
         *  "defaultLocation.orders.amount"     to      ["defaultLocation", "orders", "amount"]
         *
         *  Lets first get the sort
         */

        // If we have the sort query param
        if( request()->has('sort') ){

            //  Get the sort information
            $sort = request()->input('sort');

            //  If we have sort and this is a string
            if( is_string($sort) && $sort == 'random' ){

                /**
                 *  Indicate that we want to sort randomly.
                 *  The column name and nested relationships
                 *  do not matter since we are sorting the
                 *  results randomly.
                 */
                $this->addOperation(null, null, 'random');

                return;

            }

            //  Decode the url to a valid JSON String
            $sort = urldecode( $sort );

            //  Decode the JSON String to a valid Associative Array
            $sort = json_decode( $sort, true );

            //  If we have the sorting operations and this is an array
            if( is_array($sort) && count($sort) ){

                /**
                 *  Foreach sort we extract the components e.g
                 *
                 *   $sortKey = "amount"
                 *
                 *   or
                 *
                 *   $sortKey = "orders.amount"
                 *
                 *   and
                 *
                 *   $sortValue = "asc"
                 *
                 *  or
                 *
                 *   $sortValue = "desc"
                 *
                 */
                foreach($sort as $sortKey => $sortValue){

                    /**
                     *  We need to separate the sort key to determine
                     *  if we have been provided with a column name
                     *  or a relationship leading to a column name
                     *
                     *  (1)  A column name alone looks like this:
                     *
                     *       $sortKey = "amount"
                     *
                     *  (2)  A relationship leading to a column name looks like this:
                     *
                     *      $sortKey = "orders.amount"
                     *
                     *  Let as split the sort key into parts
                     *
                     *  "amount"            to  ["amount"]
                     *
                     *  or
                     *
                     *  "orders.amount"     to  ["orders", "amount"]
                     */
                    $parts = Str::of($sortKey)->explode('.')->toArray();

                    //  Capture the last entry as the column name
                    $columnName = array_pop($parts);

                    //  If we still have remaining parts
                    if( count($parts) ){

                        /**
                         *  Rejoin the remaining parts e.g
                         *
                         *  "orders" or "defaultLocation.orders"
                         *
                         *  These are the nested relationships
                         */
                        $nestedRelationships = collect($parts)->implode('.');

                    }else{

                        $nestedRelationships = [];

                    }

                    /**
                     *  The $sortValue must be a string value that is equal to the
                     *  value of "asc" or "desc". If this is not the case then we
                     *  do not process this.
                     *
                     *  $sortValue = "asc"
                     *
                     *  or
                     *
                     *  $sortValue = "desc"
                     */
                    if( is_string($sortValue) ){

                        if( ( $sortValue == 'asc' || $sortValue == 'desc' ) ){

                            //  Add the new operation
                            $this->addOperation($columnName, $nestedRelationships, $sortValue);

                            return;

                        }

                    }

                    throw new InvalidSortingException('The sort applied for column "'.$columnName.'" is not valid. This is because the value must be a String equal to "asc" or "desc" to indicate the sorting direction.');

                }

            }else{

                if( !is_array($sort) ){

                    throw new InvalidSortingException('The sort applied is not valid because we could not make sense of it');

                }elseif(!count($sort)) {

                    throw new InvalidSortingException('The sort applied is not valid because it does not provide sorting operations');

                }

            }
        }

    }

    public function addOperation($columnName, $nestedRelationships, $direction) {

        //  Create a new operation
        $operation = [
            'nestedRelationships' => $nestedRelationships,
            'columnName' => $columnName,
            'direction' => $direction
        ];

        //  Add the new operation
        array_push($this->operations, $operation);

    }

    /**
     *  @param \Illuminate\Database\Eloquent\Model | \Illuminate\Database\Eloquent\Builder $model
     *  @return \Illuminate\Database\Eloquent\Model | \Illuminate\Database\Eloquent\Builder $model
     */
    public function apply($model) {

        /**
         *  This is an example structure of $this->operations
         *  [
         *      [
         *
         *          'nestedRelationships' => '',
         *          'columnName' => 'name',
         *          'direction' => 'asc'
         *      ],
         *      [
         *
         *          'nestedRelationships' => 'orders',
         *          'columnName' => 'amount',
         *          'direction' => 'desc'
         *      ],
         *      ...
         *  ]
         */

        foreach ($this->operations as $key => $operation) {

            //  If we can sort using this operation
            if($this->canSort($model, $operation)) {

                //  Check if we are sorting randomly
                $sortRandomly = $operation['direction'] == 'random';

                //  Check if this is the last operation
                $isLastOperation = ($key == (count($this->operations) - 1));

                //  Check if this is the last operation
                $hasNestedRelationships = !empty($operation['nestedRelationships']);

                //  If we are not sorting in random order and we have nested relationships
                if( !$sortRandomly && $hasNestedRelationships  ) {

                    //  Capture the relationships that must be eager loaded
                    array_push($eagerLoadRelationships, $operation['nestedRelationships']);

                }

                //  Apply the sorting query
                $model = $this->runSortingQuery($model, $operation);

                /**
                 *  Since we want to chain the with method once,
                 *  we will only chain the method on the last
                 *  operation only if we have eager loaded
                 *  relationships
                 */
                if( $isLastOperation && $hasNestedRelationships ) {

                    //  In this case we must add the nested relationships to be eager loaded
                    $model = $model->with($operation['nestedRelationships']);

                 }

            }

        }

        //  Return the model as is
        return $model;

    }

    public function canSort($model, $operation) {

        /**
         *  If we are sorting in random order and we do not have nested relationships, then
         *  we can first make a simple check to see if the column name exists on the table
         *  schema to avoid wasted queries on column names that do not exist.
         */
        if( $operation['direction'] != 'random' && empty( $operation['nestedRelationships'] ) ) {

            //  Get the table associated with the given Eloquent Model or Eloquent Builder
            $table = $model instanceof Model ? $model->getTable() : $model->getModel()->getTable();

            //  Check if the model column name was provided on the Model Schema (True / False)
            $exists = Schema::hasColumn($table, $operation['columnName']);

            if( !$exists ) {

                throw new InvalidSortingException('The sort applied for column "'.$operation['columnName'].'" is not valid. This is because the column name does not exist.');

            }

        }

        //  Otherwise let us attempt to query on the relationship
        return true;

    }

    public function runSortingQuery($model, $operation){

        //  If we are sorting in random order
        if( $operation['direction'] == 'random' ) {

            return $model->inRandomOrder();

        }else{

            /**
             *  If we do not have any relationships then simply use the column name as
             *  the target. If we have relationships then append the relationship trail
             *  to the column name e.g
             *
             *  $target = "amount"                          (Without relationship)
             *  $target = "orders.amount"                   (With relationships)
             *  $target = "locations.orders.amount"         (With relationships)
             */
            $target = empty($operation['nestedRelationships']) ? $operation['columnName'] : $operation['nestedRelationships'] . '.' . $operation['columnName'];

            //  If we are sorting in ascending or descending order
            return $model->orderBy($target, $operation['direction']);

        }

    }

}
