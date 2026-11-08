<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TenantBillingSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(October2026BillingSeeder::class);
    }
}
