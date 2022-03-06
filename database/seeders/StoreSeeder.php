<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Store;
use App\Models\Location;
use Illuminate\Database\Seeder;
use Database\Seeders\Traits\SeederHelper;

class StoreSeeder extends Seeder
{
    use SeederHelper;

    /**
     *  Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->disableForeignKeyChecks();

        $this->truncate('stores', 'locations', 'orders');

        //  Each location must have atleast one order
        $orders = [Order::factory(1), 'orders'];

        //  Each store must have atleast one location
        $locations = [Location::factory(1)->has(...$orders), 'locations'];

        //  Create stores
        Store::factory(10)->has(...$locations)->create();

        $this->enableForeignKeyChecks();
    }
}
