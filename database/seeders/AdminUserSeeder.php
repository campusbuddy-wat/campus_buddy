<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::where('student_id', '0242310005341134')
            ->update([
            'role' => 'admin',
            'is_approved' => true,
        ]);

        $this->command->info('✅ User 0242310005341134 has been upgraded to admin role.');
    }
}