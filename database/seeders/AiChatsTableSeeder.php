<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AiChatsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('ai_chats')->delete();
        
        \DB::table('ai_chats')->insert(array (
            0 => 
            array (
                'id' => 1,
                'user_id' => 1,
                'session_id' => NULL,
                'type' => 'buddy',
                'title' => 'What is my next class?',
            'history' => '[{"role":"user","content":"What is my next class?"},{"role":"assistant","content":"**Upcoming Class**\\nYour next class is:\\n- **Introduction to Data science and data Management & Analysis (DS331)**: 1.00 pm-2.30 pm | Room: 713 | Teacher: FH\\n\\nIt\'s currently 12:13 PM, so you have about 47 minutes before your next class starts. Make sure to head to Room 713 on time."},{"role":"user","content":"whats up bud"},{"role":"assistant","content":"**Hey Washim**\\nNot much, just here to help you with any questions or concerns you might have about your schedule or classes. How\'s your day going so far? You\'ve got a class coming up soon, **Introduction to Data science and data Management & Analysis (DS331)** at 1:00 PM. Need any help with anything?"}]',
                'created_at' => '2026-05-14 12:13:45',
                'updated_at' => '2026-05-14 12:13:58',
            ),
            1 => 
            array (
                'id' => 2,
                'user_id' => 1,
                'session_id' => NULL,
                'type' => 'buddy',
                'title' => 'dgbfgb',
                'history' => '[{"role":"user","content":"dgbfgb"},{"role":"assistant","content":"Mayaz, it seems like you might have accidentally typed something that doesn\'t make sense. Could you please rephrase or tell me what\'s on your mind? I\'m here to help you with any questions or concerns you might have, whether it\'s about your schedule, assignments, or anything else related to your studies at DIU."}]',
                'created_at' => '2026-05-14 12:14:24',
                'updated_at' => '2026-05-14 12:14:24',
            ),
        ));
        
        
    }
}