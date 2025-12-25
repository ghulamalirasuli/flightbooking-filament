<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

            \App\Models\User::factory()->create([
            'uid'        =>'SUPERUSER'.date('ymdhis'),
            'photo'      =>'avatar.png',
            'name'       => 'Your name',
            // 'username'  => 'admin',
            'email'     => 'admin@email.com',
            'password'  => bcrypt('12345678'),
            'is_admin'  =>1,
            'user_type' =>'Superuser',
            'user_access'=> '',
            'is_active' =>1,
            'status'    =>1,
            'user_id'   => 'SUPERUSER'.date('ymdhis'),
        ]);

    }
}
