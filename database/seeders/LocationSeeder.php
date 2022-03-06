<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;
use Database\Seeders\Traits\SeederHelper;

class LocationSeeder extends Seeder
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

        $this->truncate('locations');

        Location::factory(10)->create();

        $this->enableForeignKeyChecks();
    }
}
