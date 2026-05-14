<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AnnouncementsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('announcements')->delete();
        
        \DB::table('announcements')->insert(array (
            0 => 
            array (
                'id' => 1,
                'user_id' => 1,
                'title' => 'Class Cancelled',
                'content' => 'Todays all claases cancelled due to some issues',
                'department' => 'SWE',
                'batch' => '40',
                'section' => 'B',
                'major' => 'DS',
                'created_at' => '2026-03-06 12:12:08',
                'updated_at' => '2026-03-06 12:12:08',
            ),
            1 => 
            array (
                'id' => 2,
                'user_id' => 1,
                'title' => 'Surprise Q',
                'content' => 'Today Ds441 has a surprise Quiz.Syllebus also surprise🥹',
                'department' => 'SWE',
                'batch' => '40',
                'section' => 'B',
                'major' => 'DS',
                'created_at' => '2026-03-06 12:21:00',
                'updated_at' => '2026-03-06 12:21:00',
            ),
            2 => 
            array (
                'id' => 3,
                'user_id' => 1,
                'title' => 'Class Cancelled',
                'content' => 'Deatils comming....',
                'department' => 'SWE',
                'batch' => '40',
                'section' => 'B',
                'major' => 'DS',
                'created_at' => '2026-03-13 22:41:52',
                'updated_at' => '2026-03-13 22:41:52',
            ),
            3 => 
            array (
                'id' => 4,
                'user_id' => 1,
                'title' => 'Statistical Data Analysis Quiz',
                'content' => 'Statistical Data Analysis wii be held on 2 April 2026.
Syllebus and Room no will announce very soon',
                'department' => 'SWE',
                'batch' => '40',
                'section' => 'B',
                'major' => 'DS',
                'created_at' => '2026-03-20 22:08:06',
                'updated_at' => '2026-03-20 22:08:06',
            ),
            4 => 
            array (
                'id' => 5,
                'user_id' => 1,
                'title' => 'Class Cacel for today',
                'content' => 'Comming soon',
                'department' => 'SWE',
                'batch' => '40',
                'section' => 'B',
                'major' => 'DS',
                'created_at' => '2026-04-12 00:15:37',
                'updated_at' => '2026-04-12 00:15:37',
            ),
        ));
        
        
    }
}