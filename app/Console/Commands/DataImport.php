<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoDataSeeder;
use Database\Seeders\ShopSeeder;

class DataImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed demo data for Velstore.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting data import...');

        $seeders = [
            DatabaseSeeder::class => 'Base application seeders',
            ShopSeeder::class => 'Vendor shop fixtures',
            DemoDataSeeder::class => 'Comprehensive multilingual demo data',
        ];

        foreach ($seeders as $seeder => $description) {
            $this->info("Seeding: {$description}...");

            try {
                $this->call('db:seed', ['--class' => $seeder]);
            } catch (BindingResolutionException $exception) {
                $this->error("Unable to resolve {$seeder}. Please run composer dump-autoload and try again.");
                $this->error($exception->getMessage());

                return Command::FAILURE;
            }
        }

        $this->info('All seeders executed successfully!');
        $this->info('Data import completed successfully!');
    }

    protected function createCategoriesAndProducts()
    {
        $seller = Vendor::firstOrCreate(
            ['email' => 'seller@example.com'],
            [
                'name' => 'Seller',
                'email' => 'seller@example.com',
                'password' => Hash::make('abc123'),
                'phone' => '+923001234567',
            ]
        );

        $shop = Shop::firstOrCreate(
            ['name' => 'Soft Shoes'],
            [
                'vendor_id' => $seller->id,
                'name' => 'Soft Shoes',
                'logo' => 'N/A',
                'description' => 'Luxurious comfort in every step. Crafted with premium materials for a soft, stylish, and effortless walking experience. ',
            ]
        );

        return Command::SUCCESS;
    }
}
