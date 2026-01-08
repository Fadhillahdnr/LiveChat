<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;


class BotUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $email = 'bot@chat.com';
        $user = User::where('email', $email)->first();

        if ($user) {
            // Pastikan flag is_bot di‑set jika user sudah ada
            $user->update([
                'name' => 'Admin Bot',
                'is_bot' => true,
            ]);
        } else {
            User::create([
                'name' => 'Admin Bot',
                'email' => $email,
                'password' => Hash::make('password'),
                'is_bot' => true,
            ]);
        }
    }
}
