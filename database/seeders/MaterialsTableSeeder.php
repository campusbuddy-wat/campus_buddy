<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class MaterialsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('materials')->delete();
        
        \DB::table('materials')->insert(array (
            0 => 
            array (
                'id' => 1,
                'user_id' => 1,
                'type' => 'class_material',
                'department' => 'SWE',
                'major' => 'DS',
                'section' => 'B',
                'batch' => '40',
                'course_code' => 'CSE421',
                'title' => 'Lecture-1',
                'file_path' => 'materials/V0fPW2zj9iOellkf4jOva08GKNO4NJ9JaJuF6KXh.pdf',
                'file_extension' => 'pdf',
                'created_at' => '2026-03-10 19:54:20',
                'updated_at' => '2026-03-10 19:54:20',
            ),
            1 => 
            array (
                'id' => 2,
                'user_id' => 1,
                'type' => 'hand_note',
                'department' => 'SWE',
                'major' => 'DS',
                'section' => 'B',
                'batch' => '40',
                'course_code' => 'SE331',
                'title' => 'Hand note of AI lecture -1',
                'file_path' => 'materials/yezlf6XfnrPALLCnJnldRldsKAJ8bHF9G9ymdV5E.pdf',
                'file_extension' => 'pdf',
                'created_at' => '2026-03-10 20:05:23',
                'updated_at' => '2026-03-10 20:05:23',
            ),
            2 => 
            array (
                'id' => 3,
                'user_id' => 1,
                'type' => 'hand_note',
                'department' => 'SWE',
                'major' => 'DS',
                'section' => 'B',
                'batch' => '40',
                'course_code' => 'CSE421',
                'title' => 'ML 2nd Lecture',
                'file_path' => 'materials/5eLO8Juy61DYXEok7z0PofcMf0Nvl4rCCGSwBHrb.pdf',
                'file_extension' => 'pdf',
                'created_at' => '2026-03-28 10:13:51',
                'updated_at' => '2026-03-28 10:13:51',
            ),
            3 => 
            array (
                'id' => 4,
                'user_id' => 1,
                'type' => 'class_material',
                'department' => 'SWE',
                'major' => 'DS',
                'section' => 'B',
                'batch' => '40',
            'course_code' => 'DS4১২(২)',
                'title' => 'LAb Report',
                'file_path' => 'materials/2besPWyQa5zlneAuRKf4ujcCZdUgQoYFZkr8fumk.pdf',
                'file_extension' => 'pdf',
                'created_at' => '2026-04-12 00:11:54',
                'updated_at' => '2026-04-12 00:11:54',
            ),
            4 => 
            array (
                'id' => 5,
                'user_id' => 1,
                'type' => 'class_material',
                'department' => 'SWE',
                'major' => 'DS',
                'section' => 'B',
                'batch' => '40',
                'course_code' => 'DS441',
                'title' => 'Lecture on DL',
                'file_path' => 'materials/kiOb6eeobBNzEoF4pJ8Dz9o09nSdCTlvKyjg2Dew.pptx',
                'file_extension' => 'pptx',
                'created_at' => '2026-05-14 00:06:32',
                'updated_at' => '2026-05-14 00:06:32',
            ),
        ));
        
        
    }
}