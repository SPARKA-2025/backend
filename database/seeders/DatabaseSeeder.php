<?php

namespace Database\Seeders;

use App\Models\Part;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(AdminSeeder::class);
        $this->call(TokenAdminSeeder::class);
        $this->call(FakultasSeeder::class);
        $this->call(BlokSeeder::class);
        $this->call(PartSeeder::class);        // Move this BEFORE SlotParkirSeeder
        $this->call(SlotParkirSeeder::class);  // This depends on parts table data
        $this->call(CctvDataSeeder::class);
        $this->call(UserSeeder::class);
        // $this->call(PesananSeeder::class);  // Seeder not found
        // $this->call(ParkirKhususSeeder::class);  // Seeder not found
    }
}
