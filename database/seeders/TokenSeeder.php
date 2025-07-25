<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use App\Models\Token;

class TokenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $data = [
            ['id_user' => '1', 'api_token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vc3BhcmthLWJlLWV2ZnB0aHN1dnEtZXQuYS5ydW4uYXBwL2FwaS9yZWdpc3RlciIsImlhdCI6MTcyMjE4MjAxNSwiZXhwIjoxNzIyMTg1NjE1LCJuYmYiOjE3MjIxODIwMTUsImp0aSI6IjRHeFFsRXU3SzU4d0lydEYiLCJzdWIiOiIxIiwicHJ2IjoiMjNiZDVjODk0OWY2MDBhZGIzOWU3MDFjNDAwODcyZGI3YTU5NzZmNyJ9.bvbwvFLl5b7LWcmg_Q53oFkki94mPIXQBt5hmKjSsI8', 'expired_at' => '2024-07-28 16:53:35'],
            // ['nama' => 'Sean', 'email' => 'sean@gmail.com', 'password' => 'sean12345678', 'alamat' => 'Pati', 'phone' => '08724319'],
            // ['nama' => 'Mark', 'email' => 'mark@gmail.com', 'password' => 'mark12345678', 'alamat' => 'Pati', 'phone' => '08724319'],
        ];

        foreach ($data as $value) {
            Token::insert([
                'id_user' => $value['id_user'],
                'api_token' => $value['api_token'],
                'expired_at' => $value['expired_at'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        }
    }
}
