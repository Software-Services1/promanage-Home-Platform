<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'محمد شمسان',   'email' => 'mohammed@fikra.sa', 'role' => 'admin',      'salary' => 14000, 'target' => 0,   'join' => '2023-02-01'],
            ['name' => 'معتز الشاحذي',  'email' => 'moataz@fikra.sa',   'role' => 'supervisor', 'salary' => 9500,  'target' => 120, 'join' => '2023-03-15'],
            ['name' => 'عبدالله العذيبي','email' => 'abdullah@fikra.sa', 'role' => 'designer',   'salary' => 6500,  'target' => 100, 'join' => '2023-05-20'],
            ['name' => 'أشرف حنفي',     'email' => 'ashraf@fikra.sa',   'role' => 'editor',     'salary' => 7000,  'target' => 100, 'join' => '2023-06-10'],
        ];

        foreach ($users as $u) {
            $user = User::updateOrCreate(
                ['email' => $u['email']],
                [
                    'name'      => $u['name'],
                    'password'  => Hash::make('password'), // كلمة المرور الافتراضية: password
                    'salary'    => $u['salary'],
                    'target'    => $u['target'],
                    'join_date' => $u['join'],
                    'is_active' => true,
                ]
            );
            $user->syncRoles([$u['role']]);
        }
    }
}
