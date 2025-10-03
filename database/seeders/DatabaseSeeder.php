<?php

namespace Database\Seeders;

use App\Support\SeederRegistry;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        foreach (SeederRegistry::baseSeeders() as $seeder => $description) {
            $this->call($seeder);
        }
    }
}
