<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DocsTeamMembersTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('docs_team_members')->delete();
        
        \DB::table('docs_team_members')->insert(array (
            0 => 
            array (
                'id' => 1,
                'name' => 'Washim Akram',
                'role' => 'Lead Backend Developer & Core Architect',
                'email' => 'washimakram1134@gmail.com',
                'profile_image' => NULL,
                'github_url' => 'https://github.com/WashimAkram1134',
                'bio' => 'Built the core Laravel infrastructure, Buddy AI integration, admin panel, authentication system, and question bank module.',
                'sort_order' => 1,
                'created_at' => '2026-05-12 12:30:34',
                'updated_at' => '2026-05-12 12:30:34',
            ),
            1 => 
            array (
                'id' => 2,
                'name' => 'Tanveer M Islam',
                'role' => 'Lead UI/UX Designer & Layout Specialist',
                'email' => NULL,
                'profile_image' => NULL,
                'github_url' => 'https://github.com/Tanveer-M-Islam',
                'bio' => 'Architected the visual layout, premium aesthetics, dashboard design, landing page animations, and global branding system.',
                'sort_order' => 2,
                'created_at' => '2026-05-12 12:30:34',
                'updated_at' => '2026-05-12 12:30:34',
            ),
            2 => 
            array (
                'id' => 3,
                'name' => 'Mayazad',
                'role' => 'Frontend Engineer & Community Specialist',
                'email' => NULL,
                'profile_image' => NULL,
                'github_url' => 'https://github.com/mayazad',
                'bio' => 'Led the community module redesign, built dynamic UI components, micro-animations, and interactive sidebar controls.',
                'sort_order' => 3,
                'created_at' => '2026-05-12 12:30:34',
                'updated_at' => '2026-05-12 12:30:34',
            ),
        ));
        
        
    }
}