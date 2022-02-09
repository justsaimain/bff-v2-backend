<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Admin;
use App\Models\Option;
use Illuminate\Support\Str;
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
        User::create([
            'name' => "Test User",
            "phone" => "123",
            'phone_verified_at' => now(),
            'email' => 'test@gmail.com',
            'email_verified_at' => now(),
            'region' => "Yangon",
            'fav_team' => 1,
            'password' => '123',
            'remember_token' => Str::random(10),
        ]);

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
