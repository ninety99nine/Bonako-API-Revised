<?php

namespace App\Repositories;

use App\Models\Product;
use App\Models\Variable;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use App\Repositories\BaseRepository;

class ProductRepository extends BaseRepository
{
    protected $requiresConfirmationBeforeDelete = true;

    /**
     *  Show the product variations
     */
    public function showVariations()
    {
        $variations = $this->model->variations();

        return $this->setModel($variations)->get();
    }

    /**
     *  Create or update the product variations
     */
    public function createVariations(Request $request)
    {
        /**
         *  Get the variant attributes data e.g
         *
         *  $variantAttributes = [
         *    [
         *      'name' => 'Color',
         *      'values' => ["Red", "Green", "Blue"],
         *      'instruction' => 'Select color'
         *    ],
         *    [
         *      'name' => 'Size',
         *      'values' => ["L", "M", 'SM'],
         *      'instruction' => 'Select size'
         *    ]
         *  ]
         */
        $variantAttributes = $request->input('variant_attributes');

        /**
         *  Lets make sure that the first letter of every name is capitalized
         *  and that we have instructions foreach variant attribute otherwise
         *  set a default instruction.
         */
        $variantAttributes = collect($variantAttributes)->map(function($variantAttribute){

            $variantAttribute['name'] = ucfirst($variantAttribute['name']);

            if(!isset($variantAttribute['instruction']) || empty($variantAttribute['instruction'])) {
                $variantAttribute['instruction'] = 'Select option';
            }

            return $variantAttribute;

        });

        /**
         *  Restructure the variant attribute e.g
         *
         *  $variantAttributes = [
         *    "Color" => ["Red", "Green", "Blue"],
         *    "Size" => ["Large", "Medium", 'Small']
         *  ]
         */
        $variantAttributesRestructured = $variantAttributes->mapWithKeys(function($variantAttribute, $key) {
            return [$variantAttribute['name'] => $variantAttribute['values']];
        });

        /**
         *  Cross join the values of each variant attribute values to
         *  return a cartesian product with all possible permutations
         *
         * [
         *    ["Red","Large"],
         *    ["Red","Medium"],
         *    ["Red","Small"],
         *
         *    ["Green","Large"],
         *    ["Green","Medium"],
         *    ["Green","Small"],
         *
         *    ["Blue","Large"],
         *    ["Blue","Medium"],
         *    ["Blue","Small"]
         * ]
         *
         *  Cross join the variant attribute values into an Matrix
         */
        $variantAttributeMatrix = Arr::crossJoin(...$variantAttributesRestructured->values());

        //  Create the product variation templates
        $productVariationTemplates = collect($variantAttributeMatrix)->map(function($options) use ($variantAttributesRestructured) {

            /**
             *  Foreach matrix entry let us create a product variation template.
             *
             *  If the main product is called "Summer Dress" and the
             *
             *  $options = ["Red", "Large"]
             *
             *  Then the variation product is named using both the parent
             *  product name and the variation options. For example:
             *
             *  "Summer Dress (Red and Large)"
             */
            $name = $this->model->name.' ('.trim( collect($options)->map(fn($option) => ucfirst($option))->join(', ') ).')';

            $template = [
                'name' => $name,
                'user_id' => auth()->user()->id,
                'parent_product_id' => $this->model->id,
                'location_id' => $this->model->location_id,
                'created_at' => now(),
                'updated_at' => now(),

                //  Define the variable templates
                'variableTemplates' => collect($options)->map(function($option, $key) use ($variantAttributesRestructured) {

                    /**
                     *  $option = "Red" or "Large"
                     *
                     *  $variantAttributeNames = ["Color", "Size"]
                     *
                     *  $variantAttributeNames->get($key = 0) returns "Color"
                     *  $variantAttributeNames->get($key = 1) returns "Size"
                     */
                    $variantAttributeNames = $variantAttributesRestructured->keys();

                    return [
                        'name' => $variantAttributeNames->get($key),
                        'value' => $option
                    ];
                })
            ];

            return $template;

        });

        //  Get existing product variations and their respective variables
        $existingProductVariations = $this->model->variations()->with('variables')->get();

        /**
         *  Group the existing product variations into two groups:
         *
         *  (1) Those that have matching variant attributes (Must not be deleted)
         *  (2) Those that do not have matching variant attributes (Must be deleted)
         */
        [$matchedProductVariations, $unMatchedProductVariations] = $existingProductVariations->partition(function ($existingProductVariation) use (&$productVariationTemplates) {

            /**
             *  Get the name and value for the existing variation
             *
             *  $result1 = ["Sizes", "Large"]
             *
             */
            $result1 = $existingProductVariation->variables->flatMap(function($variable){
                return $variable->only(['name', 'value']);
            })->values();

            /**
             *  If the variation exists then move it to the $matchedProductVariations,
             *  but If it does not exist then move it to the $unMatchedProductVariations
             */
            return collect($productVariationTemplates)->contains(function($productVariationTemplate, $key) use ($result1, &$productVariationTemplates) {

                /**
                 *  Get the name and value for the new variation template
                 *
                 *  $result2 = ["Sizes", "Large"]
                 *
                 */
                $result2 = collect($productVariationTemplate['variableTemplates'])->flatMap(function($variable){
                    return collect($variable)->only(['name', 'value']);
                })->values();

                /**
                 *  If the following checks pass
                 *
                 *  (1) There is no difference between the set of result2 vs result1
                 *  (2) There is no difference between the set of result1 vs result2
                 *  (3) The total items found in result1 match with those in result2
                 *
                 *  Then we can easily assume that this $productVariationTemplate
                 *  already exists as $existingProductVariation and must be
                 *  excluded from the list of new variations to create.
                 *
                 */
                $exists = $result1->diff($result2)->count() == 0 &&
                          $result2->diff($result1)->count() == 0 &&
                          ($result1->count() === $result2->count());

                //  If the variation does exist
                if( $exists === true ) {

                    //  Then we must remove the assosiated $productVariationTemplate
                    $productVariationTemplates->forget($key);

                }

                return $exists;

            });

        });

        //  If we have existing variations that have no match
        if( $unMatchedProductVariations->count() ) {

            //  Delete each variation
            $unMatchedProductVariations->each(fn($unMatchedProductVariation) => $unMatchedProductVariation->delete());

        }

        //  Update the product
        $this->model->update([
            'allow_variations' => true,
            'variant_attributes' => $variantAttributes
        ]);

        //  If we have new variations
        if($productVariationTemplates->count()) {

            //  Create the new product variations
            Product::insert(

                //  Extract only the Product fillable fields
                $productVariationTemplates->map(
                    fn($productVariationTemplate) => collect($productVariationTemplate)->only(
                        resolve(Product::class)->getFillable()
                    )
                )->toArray()

            );

            //  Get the updated product variations
            $existingProductVariations = $this->model->variations()->get();

            //  Update the product variable templates
            $variableTemplates = $existingProductVariations->flatMap(function($existingProductVariation) use ($productVariationTemplates) {

                /**
                 *  Search the product variation template whose name matches this newly created product variation.
                 *  After finding this match, extract the "variableTemplates" from the "productVariationTemplate"
                 *
                 *  $variableTemplates = [
                 *      [
                 *          "name": "Colors",
                 *          "value": "Red"
                 *      ],
                 *      [
                 *          "name": "Sizes",
                 *          "value": "Large"
                 *      ]
                 *  ];
                 */
                $productVariationTemplate = $productVariationTemplates->first(function($productVariationTemplate) use ($existingProductVariation) {

                    //  The names must match
                    return $existingProductVariation->name === $productVariationTemplate['name'];

                });

                //  If we have a matching $productVariationTemplate
                if( $productVariationTemplate ) {

                    //  Get the $variableTemplates
                    $variableTemplates = $productVariationTemplate['variableTemplates'];

                    /**
                     *  Set the product id that this variable template must relate to
                     *
                     *  $variableTemplates = [
                     *      [
                     *          "name": "Colors",
                     *          "value": "Red",
                     *          "product_id": 2
                     *      ],
                     *      [
                     *          "name": "Sizes",
                     *          "value": "Large",
                     *          "product_id": 2
                     *      ]
                     *  ];
                     */
                    return collect($variableTemplates)->map(function($variableTemplate) use ($existingProductVariation) {

                        //  Set the parent product id
                        $variableTemplate['product_id'] = $existingProductVariation->id;

                        return $variableTemplate;

                    });

                }

                //  Incase we don't have a match, return an empty array
                return [];

            });

            //  Create the new variables
            Variable::insert($variableTemplates->toArray());

        }

        return $this->showVariations();

    }
}
