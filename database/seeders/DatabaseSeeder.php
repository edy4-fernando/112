<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CatalogItemSeeder::class,
            PdfPriceListSeeder::class,
            CatalogItemImageSeeder::class,
            CatalogTraceabilitySeeder::class,
            DemoCustomerHistorySeeder::class,
            SampleSalesSeeder::class,
        ]);
    }
}
