<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed permissions and roles first
        $this->call(PermissionSeeder::class);

        // User::factory(10)->create();

        User::factory()->create([
            'name' => "Rajesh Kothekar",
            'email' => "rajesh@dishacompuworld.com",
            'password' => Hash::make('SuperClick@8765'),
       ])->assignRole('super-admin');
    }
}
