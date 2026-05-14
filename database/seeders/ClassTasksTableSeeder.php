<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ClassTasksTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('class_tasks')->delete();
        
        \DB::table('class_tasks')->insert(array (
            0 => 
            array (
                'id' => 1,
                'type' => 'assignment',
                'user_id' => 1,
                'course_code' => 'DS335',
                'title' => 'Statistical Data Analysis',
                'topic' => 'Statistic in AI',
                'description' => NULL,
                'tip_1' => 'Nothing',
                'tip_2' => 'Another tip',
                'deadline' => '2026-03-27 10:57:00',
                'duration_or_slot' => NULL,
                'progress_status' => 'Completed',
                'department' => 'SWE',
                'batch' => '40',
                'section' => 'B',
                'major' => 'DS',
                'created_at' => '2026-03-04 22:59:22',
                'updated_at' => '2026-03-28 09:32:03',
            ),
            1 => 
            array (
                'id' => 2,
                'type' => 'presentation',
                'user_id' => 1,
            'course_code' => 'SE331(B1)',
                'title' => 'Introduction to data Sceince',
                'topic' => 'Data sceince revolution',
                'description' => NULL,
                'tip_1' => NULL,
                'tip_2' => NULL,
                'deadline' => '2026-03-07 23:02:00',
                'duration_or_slot' => '11.30 am-1.00pm',
                'progress_status' => 'Completed',
                'department' => 'SWE',
                'batch' => '40',
                'section' => 'B',
                'major' => 'DS',
                'created_at' => '2026-03-04 23:02:41',
                'updated_at' => '2026-03-30 12:55:28',
            ),
            2 => 
            array (
                'id' => 3,
                'type' => 'assignment',
                'user_id' => 1,
                'course_code' => 'DS441',
                'title' => 'Introduction to Data sceince',
                'topic' => 'Statistic in AI',
                'description' => NULL,
                'tip_1' => NULL,
                'tip_2' => NULL,
                'deadline' => '2026-04-03 17:06:00',
                'duration_or_slot' => NULL,
                'progress_status' => 'Completed',
                'department' => 'SWE',
                'batch' => '40',
                'section' => 'B',
                'major' => 'DS',
                'created_at' => '2026-03-04 23:07:04',
                'updated_at' => '2026-03-28 09:32:33',
            ),
            3 => 
            array (
                'id' => 4,
                'type' => 'quiz',
                'user_id' => 1,
                'course_code' => 'SE331',
                'title' => 'Statistical Data Analysis',
                'topic' => 'Sample,Population',
                'description' => NULL,
                'tip_1' => 'Mean,varience',
                'tip_2' => 'Difference between sample & Mean',
                'deadline' => '2026-03-17 03:30:00',
                'duration_or_slot' => '30 min',
                'progress_status' => NULL,
                'department' => 'SWE',
                'batch' => '40',
                'section' => 'B',
                'major' => 'DS',
                'created_at' => '2026-03-04 23:23:58',
                'updated_at' => '2026-03-04 23:24:30',
            ),
            4 => 
            array (
                'id' => 5,
                'type' => 'quiz',
                'user_id' => 1,
                'course_code' => 'CSE421',
                'title' => 'Artificial Inteligence',
                'topic' => 'Machine Learning Model',
                'description' => NULL,
                'tip_1' => NULL,
                'tip_2' => NULL,
                'deadline' => '2026-03-06 11:29:00',
                'duration_or_slot' => NULL,
                'progress_status' => NULL,
                'department' => 'SWE',
                'batch' => '40',
                'section' => 'B',
                'major' => 'DS',
                'created_at' => '2026-03-04 23:30:09',
                'updated_at' => '2026-03-05 17:48:42',
            ),
            5 => 
            array (
                'id' => 6,
                'type' => 'presentation',
                'user_id' => 1,
            'course_code' => 'SE331(B2)',
                'title' => 'Nothing',
                'topic' => 'Nothing',
                'description' => NULL,
                'tip_1' => 'Notthing',
                'tip_2' => 'Nothing',
                'deadline' => '2026-03-09 17:46:00',
                'duration_or_slot' => '11.30 am-1.00pm',
                'progress_status' => NULL,
                'department' => 'SWE',
                'batch' => '40',
                'section' => 'B',
                'major' => 'DS',
                'created_at' => '2026-03-05 17:47:04',
                'updated_at' => '2026-03-05 17:47:04',
            ),
            6 => 
            array (
                'id' => 7,
                'type' => 'assignment',
                'user_id' => 1,
                'course_code' => 'CSE421',
                'title' => 'AI',
                'topic' => NULL,
                'description' => NULL,
                'tip_1' => 'Nothing now',
                'tip_2' => 'Nothing now 2',
                'deadline' => '2026-04-23 16:06:00',
                'duration_or_slot' => '11.30 am-1.00pm',
                'progress_status' => NULL,
                'department' => 'SWE',
                'batch' => '40',
                'section' => 'B',
                'major' => 'DS',
                'created_at' => '2026-04-08 16:07:03',
                'updated_at' => '2026-04-08 16:07:03',
            ),
            7 => 
            array (
                'id' => 8,
                'type' => 'assignment',
                'user_id' => 1,
                'course_code' => 'DS332',
                'title' => 'DSA',
                'topic' => 'Sorting Algotithm',
                'description' => NULL,
                'tip_1' => NULL,
                'tip_2' => NULL,
                'deadline' => '2026-05-22 15:06:00',
                'duration_or_slot' => '11.30 am-1.00pm',
                'progress_status' => NULL,
                'department' => 'SWE',
                'batch' => '40',
                'section' => 'B',
                'major' => 'DS',
                'created_at' => '2026-05-14 15:06:42',
                'updated_at' => '2026-05-14 15:06:42',
            ),
        ));
        
        
    }
}