<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\File;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AlertMessagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Get the path to the JSON file
        $jsonPath = database_path('database/json/alertmsg.json');

        // 2. Read the file contents
        $jsonString = File::get($jsonPath);

        // 3. Decode JSON string into a PHP associative array
        $data = json_decode($jsonString, true);

        // 4. Insert directly into the table bypassing Eloquent models
        DB::table('alert_messages')->insert($data);
    }
}
