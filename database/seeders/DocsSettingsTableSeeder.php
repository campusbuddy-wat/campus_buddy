<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DocsSettingsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('docs_settings')->delete();
        
        \DB::table('docs_settings')->insert(array (
            0 => 
            array (
                'id' => 1,
                'is_visible' => 1,
                'start_date' => '2026-06-10 00:00:00',
                'end_date' => '2026-06-14 23:59:59',
                'hero_title' => 'Campus Buddy',
                'hero_subtitle' => 'Your AI-Powered University Companion',
                'elevator_pitch' => 'University life is incredibly fragmented—students juggle scheduling apps, scattered WhatsApp groups for notes, missed deadlines, and disconnected alumni networks. Campus Buddy solves this by unifying the entire academic experience into one intelligent, AI-powered platform.',
                'created_at' => '2026-05-12 12:30:34',
                'updated_at' => '2026-05-12 12:30:34',
            ),
        ));
        
        
    }
}