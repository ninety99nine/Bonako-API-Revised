<?php

namespace App\Services\Filtering;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Exceptions\InvalidFilterException;

/**
 *  How To Use The Filter Service
 *  -----------------------------
 *
 *  In order to filter, the url must contain a query parameter called "filter".
 *  This query must contain the filtering logic in the form of Json data e.g
 *
 *  (1) JAVASCRIPT
 *
 *  var filters = {
 *      "amount": {
 *          "gte" : "10.00"
 *      }
 *  }
 *
 *  The above means that we want results were the "amount" is greater than or equal to "10.00"
 *
 *  var filters = {
 *      "industry": {
 *          "whereIn" : ["software,finance"]
 *      }
 *  }
 *
 *  The above means that we want results were the "industry" is "software" or "finance"
 *
 *  About Parsing
 *  ---------------------
 *
 *  Remember that this Json data will be attached to the URL e.g
 *
 *  "/api?filters={JSON OBJECT}"
 *
 *  But we must first url encode the "{JSON OBJECT}" so that it can be parsed correctly.
 *  When attaching the "{JSON OBJECT}" on javascript we need to use the JSON.stringify()
 *  to convert the "{JSON OBJECT}" to a literal "JSON String". We must then use
 *  encodeURIComponent() to parse the JSON String to a valid paramemter on
 *  our url. The final result on Javascript would look something like this:
 *
 *  var myJsonFilter = {
 *      "industry: {
 *          "whereIn" : ["software,finance"]
 *      }
 *  }
 *
 * "/api?filters=" + encodeURIComponent(JSON.stringify(myJsonFilter));
 *
 *  (2) PHP
 *
 *  To do the same thing we just did but on PHP we would start with an array
 *  such as the following:
 *
 *  $myArrayFilter = [
 *      "industry" => [
 *          "whereIn" => ["software,finance"]
 *      ]
 *  ]
 *
 * "/api?filters=" . urlencode(json_encode($myArrayFilter));
 *
 *  The above first convert the array to a valid JSON String using json_encode(),
 *  then we parse this JSON String into a valid url parameter using urlencode()
 *
 *  Examples Of Filtering
 *  ---------------------
 *
 *  We can filter our data in the following ways. Each example assumes parsing the
 *  url parameters from JSON format, but its easy to redo everything in Array
 *  format:
 *
 *  (1) Comparison Operators:
 *
 *  "...?filters={"amount: { "gte" : "10.00" }}    Amount must be greater than or equal to 10.00
 *  "...?filters={"amount: { "gt" : "10.00" }}     Amount must be greater than 10.00
 *  "...?filters={"amount: { "lte" : "10.00" }}    Amount must be less than or equal 10.00
 *  "...?filters={"amount: { "lt" : "10.00" }}     Amount must be less than 10.00
 *  "...?filters={"amount: { "eq" : "10.00" }}     Amount must be equal to 10.00
 *  "...?filters={"amount: { "neq" : "10.00" }}    Amount must not be equal to 10.00
 *
 *  (2) Like Operators:
 *
 *  "...?filters={"name: { "contains" : "food" }}            Name must contain the word "food"
 *  "...?filters={"name: { "doesntContain" : "food" }}       Name must not contain the word "food"
 *  "...?filters={"name: { "startsWith" : "food" }}          Name must start with the word "food"
 *  "...?filters={"name: { "endsWith" : "food" }}            Name must end with the word "food"
 *  "...?filters={"name: { "doesntStartWith" : "food" }}     Name must not start with the word "food"
 *  "...?filters={"name: { "doesntEndWith" : "food" }}       Name must not end with the word "food"
 *
 *  (3) Group Operators:
 *
 *  "...?filters={"industry: { whereIn : ["software, finance"] }}         Industry must be "software" or "finance"
 *  "...?filters={"industry: { whereNotIn : ["software, finance"] }}      Industry must not be "software" or "finance"
 *  "...?filters={"amount: { whereBetween : ["50, 100"] }}                Amount must be between "50" and "100"
 *  "...?filters={"amount: { whereNotBetween : ["50, 100"] }}             Amount must not be between "50" and "100"
 *
 *  NOTE: You can always use more than one operator e.g
 *
 *  If we want entities where the industry must be "software" or "finance" and
 *  the amount must be between "50" and "100", then do the following:
 *
 *  (1) Multiple filters on the same column name
 *
 *  filters = {
 *      "amount: {
 *          "gt : "10.00",
 *          "lt : "100.00",
 *      },
 *  }
 *
 *  (2) Multiple filters on different column names
 *
 *  filters = {
 *      "amount: {
 *          "whereIn" : ["50, 100"]
 *      },
 *      "industry: {
 *          "whereIn" : ["software, finance"]
 *      }
 *  }
 *
 *  or
 *
 *  filters = {
 *      "name: {
 *          "startsWith" : "Communication"
 *      },
 *      "amount: {
 *          "whereIn" : ["50, 100"]
 *      },
 *      "industry: {
 *          "whereIn" : ["software, finance"]
 *      }
 *  }
 *
 *  Nested Relationships
 *  --------------------
 *
 *  Consider the following examples. The first is how we would filter
 *  assuming that we wanted to target the column on the current model,
 *  however the 2nd scenerio assumes that we first need to target the
 *  "orders" relationship then query that, while the 3rd scenerio
 *  assumes that we first need to target the default location,
 *  then the orders of that location, then we perform the
 *  actual query to filter.
 *
 *  (1) ...?filters={"amount" : { "whereNotBetween" : "50,100" }}
 *
 *  (2) ...?filters={"orders.amount" : { "whereNotBetween" : "50,100" }}
 *
 *      $query->whereHas('orders', function (Builder $query) {
 *          //  Do the query stuff here
 *      });
 *
 *  (3) ...?filters={"defaultLocation.orders.amount" : { "whereNotBetween" : "50,100" }}
 *
 *      $query->whereHas('defaultLocation.orders', function (Builder $query) {
 *          //  Do the query stuff here
 *      });
 *
 *  This means we need to pass the nested relationship as
 *  part of our parameter naming convention e.g
 *
 *  filters = {
 *      "orders.amount": {
 *          "whereNotBetween" : ["50, 100"]
 *      }
 *  }
 *
 *  or
 *
 *  filters = {
 *      "defaultLocation.orders.amount": {
 *          "whereNotBetween" : ["50, 100"]
 *      }
 *  }
 */
class RepositoryFilter
{
    /**
     *  These are the operators that will be used to
     *  determine the query structure and operator
     *  to use:
     *
     *  If the url is "/api?filters={"type: {whereIn|paid.unpaid"
     *  then we must perform a "whereIn" on the query
     *  against the values ["paid", "unpaid"]
     */
    private $operators = [
        'gt' => '>',
        'gte' => '>=',
        'lt' => '<',
        'lte' => '<=',
        'eq' => '=',
        'neq' => '!=',

        'whereIn' => 'whereIn',
        'whereNotIn' => 'whereNotIn',
        'whereBetween' => 'whereBetween',
        'whereNotBetween' => 'whereNotBetween',

        'contains' => 'contains',
        'doesntContain' => 'doesntContain',
        'startsWith' => 'startsWith',
        'endsWith' => 'endsWith',
        'doesntStartWith' => 'doesntStartWith',
        'doesntEndWith' => 'doesntEndWith',

    ];

    private $operations = [];

    public function __construct() {

        // dd(
        //     urlencode(json_encode([
        //         'location.name' => [
        //             'startsWith' => 'location 2'
        //         ]
        //     ]))
        // );

        //  Set the filter operations
        $this->setFilterOperations();

    }

    private function setFilterOperations(){

        /**
         *  For a given request e.g "/api/stores?filters=['orders.amount'=>['gte|10.00','lte|100.00',]]"
         *  when we access the filters request input, we return an array as follows:
         *
         *  [
         *      'orders.amount' => [    //  This is the target (We want to filter the order relationship amount)
         *          'gte' => 10.00,     //  This is the first filter (Must be greater than or equal to 10.00)
         *          'lte' => 100.00     //  This is the second filter (Must be less than or equal to 100.00)
         *      ],
         *      ...                     //  If we have more filters
         *  ]
         *
         *  Heres another example using more filters (filtering the order amount and status at the same time)
         *
         *  [
         *      'orders.amount' => [                      //  This is the target (We want to filter the order relationship amount)
         *          'whereIn' => ['pending', 'unpaid'],   //  This is the second filter (Must be a status of paid or pending),
         *      ],
         *      'orders.status' => [                      //  This is the target (We want to filter the order relationship amount)
         *          'whereBetween' => [50.00, 100.00],    //  This is the first filter (Must be between 50.00 and 100.00),
         *      ],
         *      ...                     //  If we have more filters
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
         *  Lets first get the filters
         */

        // If we have the filters query param (Checks if the query param exists)
        if( request()->has('filters') ){

            // If we have the filter information (Checks if the query param is not empty)
            if( request()->filled('filters') ){

                //  Get the filter information
                $filters = request()->input('filters');

                //  Decode the url to a valid JSON String
                $filters = urldecode( $filters );

                //  Decode the JSON String to a valid Associative Array
                $filters = json_decode( $filters, true );

                //  If we have filters
                if( is_array($filters) && count($filters) ){

                    /**
                     *  Foreach filter we extract the components e.g
                     *
                     *   $filterKey = "amount"
                     *
                     *   or
                     *
                     *   $filterKey = "orders.amount"
                     *
                     *   and
                     *
                     *   $filterValues = [
                     *      'gte' => 10.00,
                     *      'lte' => 100.00
                     *   ]
                     *
                     *  or
                     *
                     *   $filterValues = [
                     *      'whereIn' => ['pending', 'unpaid']
                     *      'whereBetween' => [50.00, 100.00]
                     *   ]
                     */
                    foreach($filters as $filterKey => $filterValues){

                        /**
                         *  We need to separate the filter key to determine
                         *  if we have been provided with a column name
                         *  or a relationship leading to a column name
                         *
                         *  (1)  A column name alone looks like this:
                         *
                         *      $filterKey = "amount"
                         *
                         *  (2)  A relationship leading to a column name looks like this:
                         *
                         *      $filterKey = "orders.amount"
                         *
                         *  Let as split the filter key into parts
                         *
                         *  "amount"            to  ["amount"]
                         *
                         *  or
                         *
                         *  "orders.amount"     to  ["orders", "amount"]
                         */
                        $parts = Str::of($filterKey)->explode('.')->toArray();

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

                        //  If we have filter values
                        if( is_array($filterValues) && count($filterValues) ){

                            /**
                             *  Foreach filter we extract the components e.g
                             *
                             *   $operatorKey = 'gte' or 'whereIn' or 'whereBetween'
                             *
                             *   and
                             *
                             *   $queryValue = 10.00
                             *
                             *  or
                             *
                             *   $queryValue = ['pending', 'unpaid']
                             */
                            foreach($filterValues as $operatorKey => $queryValue) {

                                /**
                                 *  Check if the operator key we have matches the existing keys
                                 *  e.g a valid key must be 'gt', 'gte', 'whereIn', e.t.c
                                 *
                                 *  An invalid key is anything that does not exist e.g 'greater_than'
                                 */
                                $hasValidOperatorkey = collect($this->operators)->contains(function ($value, $existingOperatorKey) use ($operatorKey) {
                                    return $existingOperatorKey == $operatorKey;
                                });

                                //  If we have a valid operator key
                                if( $hasValidOperatorkey ){

                                    //  Get the operator e.g '>', '>=', '<',  'contains', 'doesntContain' e.t.c
                                    $operator = $this->operators[$operatorKey];

                                    //  If the operator matches any of the following
                                    if( collect(['>', '>=', '<', '<=', '=', '!=', 'contains', 'doesntContain', 'startsWith', 'endsWith', 'doesntStartWith', 'doesntEndWith'])->contains($operator) ){

                                        /**
                                         *  Then we know that this query value must only have one value,
                                         *  which means that we cannot have an array as our value
                                         *  We must have a string, boolean, integer or float
                                         *  otherwise we do not process this.
                                         *
                                         *  $queryValue = 10
                                         *
                                         *  or
                                         *
                                         *  $queryValue = 10.00
                                         *
                                         *  or
                                         *
                                         *  $queryValue = false
                                         *
                                         *  or
                                         *
                                         *  $queryValue = 'paid'
                                         *
                                         */
                                        if( is_string($queryValue) || is_bool($queryValue) || is_int($queryValue) || is_float($queryValue) ){

                                            //  Add the new operation
                                            $this->addOperation($columnName, $nestedRelationships, $operator, $queryValue);

                                        }else{

                                            throw new InvalidFilterException('The filter applied for column "'.$columnName.'" is not valid. This is because the operator "'.$operatorKey.'" requires that the value must be a String, Boolean, Integer or Float value.');

                                        }

                                    }else{

                                        /**
                                         *  Then we know that this query value must be an array,
                                         *  which means that we cannot have a single value. If
                                         *  we do not have an array we do not process this.
                                         *
                                         *  $queryValue = ['pending', 'unpaid', 'cancelled']
                                         *
                                         *  or
                                         *
                                         *  $queryValue = [50.00, 100.00]
                                         */
                                        if( is_array($queryValue) ){

                                            //  Make sure that every value is a string, boolean, integer or float
                                            $queryValue = collect($queryValue)->filter(function ($value) {
                                                return (is_string($value) || is_bool($value) || is_int($value) || is_float($value));
                                            })->values()->all();

                                            //  If we are using the "whereBetween" or "whereNotBetween"
                                            if( collect(['whereBetween', 'whereNotBetween'])->contains($operator) ){

                                                //  Make sure we have two values provided otherwise do not proceed
                                                if( count($queryValue) != 2 ){

                                                    throw new InvalidFilterException('The filter applied for column "'.$columnName.'" is not valid. This is because the operator "'.$operatorKey.'" requires that the value must be an array of exactly 2 values.');

                                                };

                                            }

                                            //  Add the new operation
                                            $this->addOperation($columnName, $nestedRelationships, $operator, $queryValue);

                                        }else{

                                            throw new InvalidFilterException('The filter applied for column "'.$columnName.'" is not valid. This is because the operator "'.$operatorKey.'" requires that the value must be an array of values.');

                                        }

                                    }

                                }else{

                                    throw new InvalidFilterException('The filter applied for column "'.$columnName.'" is not valid. This is because the operator "'.$operatorKey.'" is not recognised to filter the results.');

                                }

                            }

                        }

                    }

                }else{

                    if( !is_array($filters) ){

                        throw new InvalidFilterException('The filter applied is not valid because we could not make sense of it. The format is incorrect');

                    }elseif(!count($filters)) {

                        throw new InvalidFilterException('The filter applied is not valid because it does not have filtering operations');

                    }

                }

            }else{

                throw new InvalidFilterException('The filter applied is not valid because it does not have filtering operations');

            }
        }

    }

    public function addOperation($columnName, $nestedRelationships, $operator, $queryValue) {

        //  Create a new operation
        $operation = [
            'nestedRelationships' => $nestedRelationships,
            'columnName' => $columnName,
            'operator' => $operator,
            'value' => $queryValue,
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
         *          'nestedRelationships' => 'defaultLocation.orders',
         *          'columnName' => 'amount',
         *          'operator' => '>',
         *          'value' => '10.00',
         *      ],
         *      [
         *
         *          'nestedRelationships' => 'orders',
         *          'columnName' => 'status',
         *          'operator' => 'whereIn',
         *          'value' => ['paid', 'paid'],
         *      ],
         *      ...
         *  ]
         */
        foreach ($this->operations as $operation) {

            //  If we can filter using this operation
            if($this->canFilter($model, $operation)){

                //  If we have nested relationships
                if( !empty($operation['nestedRelationships']) ){

                    //  In this case we must run the filter query on the nested relationships
                    $model = $model->whereHas($operation['nestedRelationships'], function (Builder $query) use ($operation) {
                        $this->runFilterQuery($query, $operation);
                    });

                }else{

                    //  In this case we must run the filter query on the model itself
                    $model = $this->runFilterQuery($model, $operation);

                }

            }

        }

        //  Return the model as is
        return $model;

    }

    public function canFilter($model, $operation) {

        /**
         *  If we do not have nested relationships, then we can first make a simple check
         *  to see if the column name exists on the table schema to avoid wasted queries
         *  on column names that do not exist.
         */
        if( empty( $operation['nestedRelationships'] ) ) {

            //  Get the table associated with the given Eloquent Model or Eloquent Builder
            $table = $model instanceof Model ? $model->getTable() : $model->getModel()->getTable();

            //  Check if the model column name was provided on the Model Schema (True / False)
            $exists = Schema::hasColumn($table, $operation['columnName']);

            if( !$exists ) {

                throw new InvalidFilterException('The filter applied for column "'.$operation['columnName'].'" is not valid. This is because the column name does not exist.');

            }

        }

        //  Otherwise let us attempt to query on the relationship
        return true;

    }

    public function runFilterQuery($model, $operation){

        //  Get the operator e.g ">" or "whereIn"
        $operator = $operation['operator'];

        switch ($operator) {
            case '>':
                return $this->filterGreaterThan($model, $operation);
                break;
            case '>=':
                return $this->filterGreaterThanOrEqualTo($model, $operation);
                break;
            case '<':
                return $this->filterLessThan($model, $operation);
                break;
            case '<=':
                return $this->filterLessThanOrEqualTo($model, $operation);
                break;
            case '=':
                return $this->filterEqualTo($model, $operation);
                break;
            case '!=':
                return $this->filterNotEqualTo($model, $operation);
                break;
            case 'whereIn':
                return $this->filterWhereIn($model, $operation);
                break;
            case 'whereNotIn':
                return $this->filterWhereNotIn($model, $operation);
                break;
            case 'whereBetween':
                return $this->filterWhereBetween($model, $operation);
                break;
            case 'whereNotBetween':
                return $this->filterWhereNotBetween($model, $operation);
                break;
            case 'contains':
                return $this->filterContains($model, $operation);
                break;
            case 'doesntContain':
                return $this->filterDoesntContain($model, $operation);
                break;
            case 'startsWith':
                return $this->filterStartsWith($model, $operation);
                break;
            case 'endsWith':
                return $this->filterEndsWith($model, $operation);
                break;
            case 'doesntStartWith':
                return $this->filterDoesntStartWith($model, $operation);
                break;
            case 'doesntEndWith':
                return $this->filterDoesntEndWith($model, $operation);
                break;
        }

        //  Return the model as is (If no operation was triggered somehow)
        return $model;

    }

    //  Filter Using Comparison Operators

    public function filterGreaterThan($model, $operation) {

        return $this->filterUsingComparisonOperator($model, $operation);

    }

    public function filterGreaterThanOrEqualTo($model, $operation) {

        return $this->filterUsingComparisonOperator($model, $operation);

    }

    public function filterLessThan($model, $operation) {

        return $this->filterUsingComparisonOperator($model, $operation);

    }

    public function filterLessThanOrEqualTo($model, $operation) {

        return $this->filterUsingComparisonOperator($model, $operation);

    }

    public function filterEqualTo($model, $operation) {

        return $this->filterUsingComparisonOperator($model, $operation);

    }

    public function filterNotEqualTo($model, $operation) {

        return $this->filterUsingComparisonOperator($model, $operation);

    }

    public function filterUsingComparisonOperator($model, $operation) {

        return $model->where($operation['columnName'], $operation['operator'], $operation['value']);

    }

    //  Filter Using Like Operator

    public function filterContains($model, $operation) {

        return $this->filterUsingLikeOperator($model, $operation);

    }

    public function filterDoesntContain($model, $operation) {

        return $this->filterUsingLikeOperator($model, $operation);

    }

    public function filterStartsWith($model, $operation) {

        return $this->filterUsingLikeOperator($model, $operation);

    }

    public function filterEndsWith($model, $operation) {

        return $this->filterUsingLikeOperator($model, $operation);

    }

    public function filterDoesntStartWith($model, $operation) {

        return $this->filterUsingLikeOperator($model, $operation);

    }

    public function filterDoesntEndWith($model, $operation) {

        return $this->filterUsingLikeOperator($model, $operation);

    }

    public function filterUsingLikeOperator($model, $operation) {

        if($operation['operator'] == 'contains'){

            return $model->where($operation['columnName'], 'like', '%'.$operation['value'].'%');

        }elseif($operation['operator'] == 'doesntContain'){

            return $model->where($operation['columnName'], 'not like', '%'.$operation['value'].'%');

        }elseif($operation['operator'] == 'startsWith'){

            return $model->where($operation['columnName'], 'like', $operation['value'].'%');

        }elseif($operation['operator'] == 'endsWith'){

            return $model->where($operation['columnName'], 'like', '%'.$operation['value']);

        }elseif($operation['operator'] == 'doesntStartWith'){

            return $model->where($operation['columnName'], 'not like', $operation['value'].'%');

        }elseif($operation['operator'] == 'doesntEndWith'){

            return $model->where($operation['columnName'], 'not like', '%'.$operation['value']);

        }else{

            return $model;

        }

    }

    //  Filter Using Group Operators

    public function filterWhereIn($model, $operation) {

        return $model->whereIn($operation['columnName'], $operation['value']);

    }

    public function filterWhereNotIn($model, $operation) {

        return $model->whereNotIn($operation['columnName'], $operation['value']);

    }

    public function filterWhereBetween($model, $operation) {

        return $model->whereBetween($operation['columnName'], $operation['value']);

    }

    public function filterWhereNotBetween($model, $operation) {

        return $model->whereNotBetween($operation['columnName'], $operation['value']);

    }

}
