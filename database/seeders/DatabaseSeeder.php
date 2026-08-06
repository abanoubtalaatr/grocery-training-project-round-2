<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            CategorySeeder::class,
            SubcategorySeeder::class,
            MealSeeder::class,
            FaqSeeder::class,
            StaticPageSeeder::class,
            ContactMessageSeeder::class,
            SettingSeeder::class,
            SpecialNoteSeeder::class,
        ]);
    }
}
