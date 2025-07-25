<?php

namespace Database\Seeders;

use App\Models\TokenAdmin;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class TokenAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $data = [
            ['id_admin' => '1', 'api_token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vbG9jYWxob3N0OjgwMDAvYXBpL3JlZ2lzdGVyLWFkbWluIiwiaWF0IjoxNzIwNzU5NDE4LCJleHAiOjIwMzYxMTk0MTgsIm5iZiI6MTcyMDc1OTQxOCwianRpIjoiSXRJWEl1aENxTWt5YmxXTyIsInN1YiI6IjEiLCJwcnYiOiJkZjg4M2RiOTdiZDA1ZWY4ZmY4NTA4MmQ2ODZjNDVlODMyZTU5M2E5In0.89w7w3-yEsf61SDPr8-zzdb3Vl_0kaxq5ikhQ4vJn7Q', 'expired_at' => '2034-07-12 04:43:38'],
            ['id_admin' => '2', 'api_token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vc3BhcmthLWJlLWZnenVzd2htMnEtZXQuYS5ydW4uYXBwL2FwaS9sb2dpbi1hZG1pbiIsImlhdCI6MTcyMDc2MDAzOSwiZXhwIjoyMDM2MTIwMDM5LCJuYmYiOjE3MjA3NjAwMzksImp0aSI6Im82ZGw0TTk2NHhBZWRteUUiLCJzdWIiOiIyIiwicHJ2IjoiZGY4ODNkYjk3YmQwNWVmOGZmODUwODJkNjg2YzQ1ZTgzMmU1OTNhOSJ9.Lk2dCD7crX6Wgybsq8QeI_foCV7p63oNZw2DSqU5XME', 'expired_at' => '2034-07-12 04:53:59'],
            ['id_admin' => '3', 'api_token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vc3BhcmthLWJlLWZnenVzd2htMnEtZXQuYS5ydW4uYXBwL2FwaS9sb2dpbi1hZG1pbiIsImlhdCI6MTcyMDc2MjQwMiwiZXhwIjoyMDM2MTIyNDAyLCJuYmYiOjE3MjA3NjI0MDIsImp0aSI6Im9qdXpuRmFsaG5xQ2F3MkoiLCJzdWIiOiIzIiwicHJ2IjoiZGY4ODNkYjk3YmQwNWVmOGZmODUwODJkNjg2YzQ1ZTgzMmU1OTNhOSJ9.YPxU72KH9sUzcCYeiNVrvYEYh2AdmPeoUeaPswWcO6I', 'expired_at' => '2034-07-12 05:33:22'],
        ];

        foreach ($data as $value) {
            TokenAdmin::insert([
                'id_admin' => $value['id_admin'],
                'api_token' => $value['api_token'],
                'expired_at' => $value['expired_at'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        }
    }
}
