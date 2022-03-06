<?php

namespace Database\Seeders\Traits;

use Illuminate\Support\Facades\DB;

trait SeederHelper
{
    public function truncate(...$tables)
    {
        foreach($tables as $table){

            //  Truncate table
            DB::table($table)->truncate();

        }
    }

    public function enableForeignKeyChecks()
    {
        //  Turn on foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    public function disableForeignKeyChecks()
    {
        //  Turn off foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
    }
}
