<?php

namespace Modules\Multiservices\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class MultiservicesDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $this->call([
            DefaultTransactionTypesSeeder::class,
            // Autres seeders ici si nécessaire
        ]);
    }
}