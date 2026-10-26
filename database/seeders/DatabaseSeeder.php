<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Buat user demo untuk testing login
        $users = [
            ['admin',    'Administrator',   'admin@kprbeta.com',    'admin'],
            ['user1',    'Budi Staf',       'user1@kprbeta.com',    'user'],
            ['teknisi1', 'Andi Teknisi',    'teknisi1@kprbeta.com', 'teknisi'],
            ['engadmin', 'Citra Eng Admin', 'engadmin@kprbeta.com', 'eng_admin'],
            ['engspv',   'Dedi Eng Spv',    'engspv@kprbeta.com',   'eng_spv'],
            ['gm',       'Bapak Hendra (General Manager)', 'gm@kprbeta.com', 'gm'],
        ];

        foreach ($users as [$username, $name, $email, $role]) {
            User::updateOrCreate(
                ['username' => $username],
                [
                    'name'     => $name,
                    'username' => $username,
                    'email'    => $email,
                    'role'     => $role,
                    'password' => Hash::make('password123'),
                ]
            );
        }

        $this->command->info('User demo berhasil dibuat (password: password123):');
        $this->command->table(['Username', 'Nama', 'Role'], array_map(fn ($u) => [$u[0], $u[1], $u[3]], $users));
    }
}
