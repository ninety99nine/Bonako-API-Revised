<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Store;
use App\Repositories\StoreRepository;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StoreRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_store()
    {
        $data = [
            'name' => 'Big shop',
            "accepted_golden_rules" => true,
            'call_to_action' => 'Buy veggies',
        ];

        $response = $this->post(route('stores'), $data);

        $this->assertDatabaseCount('stores', 1);

        $response->assertCreated();
    }

    public function test_create_store_requires_name()
    {
        $response = $this->post(route('stores'), []);

        $response->assertJsonValidationErrorFor('name');
    }

    public function test_create_store_requires_call_to_action()
    {
        $response = $this->post(route('stores'), []);

        $response->assertJsonValidationErrorFor('call_to_action');
    }

    public function test_create_store_requires_accepting_golden_rules()
    {
        $response = $this->post(route('stores'), []);

        $response->assertJsonValidationErrorFor('accepted_golden_rules');
    }

    public function test_get_stores()
    {
        $total = 5;

        //  Create stores
        Store::factory($total)->create();

        $response = $this->get(route('stores'));

        $response->assertJsonCount($total, 'data')->assertOk();
    }

    public function test_get_stores_while_limiting_less_than_100()
    {
        $perPage = 5;
        $total = $perPage * 2;

        //  Create stores
        Store::factory($total)->create();

        //  Get paginated stores (5 per page)
        $response = $this->get(route('stores', ['per_page' => $perPage]));

        $response->assertJsonFragment(['per_page' => $perPage])->assertJsonFragment(['total' => $total])->assertOk();
    }

    public function test_get_stores_while_limiting_no_more_than_100()
    {
        $perPage = 150;         //  Exceed the 100 per page limit

        //  Create store(s)
        Store::factory(1)->create();

        //  Get paginated stores (150 per page)
        $response = $this->get(route('stores', ['per_page' => $perPage]));

        //  We expect an Exception because we exceeded the limit of 100 results per page
        $response->assertStatus(400);
    }

    public function test_get_stores_while_filtering()
    {
        //  Create 3 stores with the same name of "Small shop"
        Store::factory(3)->state(function (array $attributes) {
            return [
                'name' => 'Small shop',
            ];
        })->create();

        //  Create 2 stores with the same name of "Big shop"
        Store::factory(2)->state(function (array $attributes) {
            return [
                'name' => 'Big shop',
            ];
        })->create();

        //  Set the filter (Results must start with the word "Small")
        $filter = [
            'filters' => urlencode(json_encode([
                'name' => [
                    'startsWith' => 'Small'
                ]
            ]))
        ];

        //  Get stores that match the filter
        $response = $this->get(route('stores', $filter));

        //  Must only contain 3 results with a status 200
        $response->assertJsonCount(3, 'data')->assertOk();
    }

    public function test_get_stores_while_sorting()
    {
        $response = $this->get(route('stores'));

        $response->assertOk();
    }

    public function test_get_store()
    {
        $name = 'Big shop';

        $store = Store::factory()->state(function (array $attributes) use ($name ) {
            return [
                'name' => $name,
            ];
        })->create();

        $response = $this->get(route('stores.show', ['store' => $store->id]));

        $response->assertJsonFragment(['name' => $name])->assertOk();
    }

    public function test_update_store()
    {
        $name = 'Big shop';

        $data = [
            'name' => $name,
            "accepted_golden_rules" => true,
            'call_to_action' => 'Buy veggies'
        ];

        $store = Store::factory()->create();

        $response = $this->put(route('stores.update', ['store' => $store->id]), $data);

        $response->assertJsonFragment(['name' => $name]) ->assertOk();
    }

    public function test_delete_store()
    {
        $store = Store::factory()->create();

        $this->assertDatabaseCount('stores', 1);

        $response = $this->delete(route('stores.delete', ['store' => $store->id]));

        $this->assertDatabaseCount('stores', 0);

        $response->assertNoContent();
    }

    public function test_get_non_existing_store()
    {
        $response = $this->get(route('stores.show', ['store' => 1]));

        $response->assertNotFound();
    }
}
