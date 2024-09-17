<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Project;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

    $project1 = Project::firstOrCreate([
        'name' => 'Project A',
        'description' => 'Description for Project A',
    ]);

    $project2 = Project::firstOrCreate([
        'name' => 'Project B',
        'description' => 'Description for Project B',
    ]);

    $adminUser = User::firstOrCreate([
        'email' => 'safaa@gmail.com',
    ], [
        'name' => 'safaa',
        'password' => Hash::make(12345678),
    ]);

    $projects = Project::all();
    foreach ($projects as $project) {
        if (!$project->users()->where('user_id', $adminUser->id)->exists()) {
            $project->users()->attach($adminUser->id, [
                'role' => 'admin',
                'contribution_hours' => 0,
                'last_activity' => now(),
            ]);
        }
    }

    $user2 = User::firstOrCreate([
        'email' => 'safa@gmail.com',
    ], [
        'name' => 'safa',
        'password' => Hash::make(12345678),
    ]);

    $user3 = User::firstOrCreate([
        'email' => 'saf@gmail.com',
    ], [
        'name' => 'saf',
        'password' => Hash::make(12345678),
    ]);

    $user4 = User::firstOrCreate([
        'email' => 'sa@gmail.com',
    ], [
        'name' => 'sa',
        'password' => Hash::make(12345678),
    ]);

    $project1->users()->attach($user3->id, [
        'role' => 'manager',
        'contribution_hours' => 10,
        'last_activity' => now(),
    ]);

    $project1->users()->attach($user2->id, [
        'role' => 'developer',
        'contribution_hours' => 20,
        'last_activity' => now(),
    ]);

    $project2->users()->attach($user3->id, [
        'role' => 'developer',
        'contribution_hours' => 15,
        'last_activity' => now(),
    ]);

    $project2->users()->attach($user4->id, [
        'role' => 'tester',
        'contribution_hours' => 25,
        'last_activity' => now(),
    ]);

    }}
