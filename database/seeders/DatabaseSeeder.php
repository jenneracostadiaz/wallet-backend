<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Call seeders in the correct order
        $this->call([
            UserSeeder::class,      // First create users
            CategorySeeder::class,  // Then create categories
            AccountSeeder::class,   // Then create accounts
            TransactionSeeder::class, // Finally create transactions
        ]);
    }
}
