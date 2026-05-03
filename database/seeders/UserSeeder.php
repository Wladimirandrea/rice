<?php
// database/seeders/UserSeeder.php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // ── Admin ──────────────────────────────────────────────
        $admin = User::create([
            'name'       => 'Admin Principal',
            'email'      => 'admin@sistema.com',
            'password'   => bcrypt('123456789'),
            'role'       => 'admin',
            'is_active'  => true,
            'created_by' => null,
        ]);

        // ── Case Manager ───────────────────────────────────────
        $manager = User::create([
            'name'       => 'Carlos Gestor',
            'email'      => 'manager@sistema.com',
            'password'   => bcrypt('123456789'),
            'role'       => 'case_manager',
            'is_active'  => true,
            'created_by' => $admin->id,
        ]);

        // ── Cliente ────────────────────────────────────────────
        User::create([
            'name'       => 'María Cliente',
            'email'      => 'cliente@sistema.com',
            'password'   => bcrypt('123456789'),
            'role'       => 'client',
            'is_active'  => true,
            'created_by' => $manager->id,
        ]);
    }
}