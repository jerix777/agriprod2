<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // Insérer l'utilisateur admin par defaut dans la table users
        User::factory()->create([
            'name' => 'Jerix',
            'email' => 'admin@agriprod.com',
            'password' => Hash::make('azerty'),
        ]);
    }
}
