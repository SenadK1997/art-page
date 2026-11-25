<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(PaintingStyleSeeder::class);

        if ($email = env('ADMIN_EMAIL')) {
            User::firstOrCreate(['email' => $email], [
                'name' => env('ADMIN_NAME', 'Admin'),
                'password' => env('ADMIN_PASSWORD'),
            ]);
        }

        if (app()->isLocal()) {
            $this->call(DemoSeeder::class);
        }
    }
}
