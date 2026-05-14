<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MockUserSeeder extends Seeder
{
    public function run(): void
    {
        $departments = ['SWE', 'CSE', 'EEE', 'Pharmacy', 'BBA', 'LLB', 'English'];
        $batches = ['38', '39', '40', '41', '42'];
        $sections = ['A', 'B', 'C', 'D'];
        $password = Hash::make('12345678');

        // Create 20 Students
        for ($i = 1; $i <= 20; $i++) {
            $firstName = fake()->firstName();
            $lastName = fake()->lastName();
            $dept = $departments[array_rand($departments)];
            $idPrefix = strtolower(substr($firstName, 0, 3) . substr($lastName, 0, 2));
            $randomId = rand(10000000, 99999999);
            
            User::create([
                'name' => "$firstName $lastName",
                'email' => "{$idPrefix}{$randomId}@diu.edu.bd",
                'student_id' => (string)$randomId, // Using the random part as student_id
                'role' => 'student',
                'is_approved' => true,
                'department' => $dept,
                'batch' => $batches[array_rand($batches)],
                'semester' => rand(1, 8) . 'th',
                'section' => $sections[array_rand($sections)],
                'password' => $password,
                'email_verified_at' => now(),
            ]);
        }

        // Create 5 CRs
        for ($i = 1; $i <= 5; $i++) {
            $firstName = fake()->firstName();
            $lastName = fake()->lastName();
            $dept = $departments[array_rand($departments)];
            $idPrefix = strtolower(substr($firstName, 0, 3) . substr($lastName, 0, 2));
            $randomId = rand(10000000, 99999999);
            
            User::create([
                'name' => "$firstName $lastName (CR)",
                'email' => "cr.{$idPrefix}{$randomId}@diu.edu.bd",
                'student_id' => (string)$randomId,
                'role' => 'cr',
                'is_approved' => true,
                'department' => $dept,
                'batch' => $batches[array_rand($batches)],
                'semester' => rand(1, 8) . 'th',
                'section' => $sections[array_rand($sections)],
                'password' => $password,
                'email_verified_at' => now(),
            ]);
        }
    }
}
