<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Option;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\User::factory(100)->create();

        Option::create([
            'current_gameweek' => 24
        ]);

        Admin::create([
            'name' => 'BFF Admin',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('admin123')
        ]);
    }
}
