<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class QuestionBanksTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('question_banks')->delete();
        
        \DB::table('question_banks')->insert(array (
            0 => 
            array (
                'id' => 1,
                'user_id' => 1,
                'department' => 'SWE',
                'course_code' => 'SE331',
                'course_name' => 'Object oriented Programming',
                'title' => 'OOP-Midterm',
                'difficulty' => 'Medium',
                'question_heading' => 'Nothings',
                'sub_questions' => 'Difference between ......',
                'tags' => 'Abstruction,Polymorphisom',
                'year_semester' => 'Fall 2025',
                'file_path' => 'question_banks/m08rwjoTbBHiIC6htg3U1pzSpfy869QPNgx7spkY.pdf',
                'status' => 'approved',
                'created_at' => '2026-03-14 22:42:43',
                'updated_at' => '2026-03-14 22:42:43',
            ),
            1 => 
            array (
                'id' => 2,
                'user_id' => 1,
                'department' => 'SWE',
                'course_code' => 'SE225',
                'course_name' => 'Data Communication & Computer networking',
                'title' => 'Data Communication_ midterm',
                'difficulty' => 'Hard',
                'question_heading' => 'Nothings',
                'sub_questions' => 'Explain the topology',
                'tags' => 'Networking,Protocol',
                'year_semester' => 'Spring 2026',
                'file_path' => '["question_banks\\/01KNP8HA0RCENGNCX07ZGQNB98.pdf"]',
                'status' => 'approved',
                'created_at' => '2026-04-08 15:22:33',
                'updated_at' => '2026-04-08 15:59:48',
            ),
            2 => 
            array (
                'id' => 3,
                'user_id' => 1,
                'department' => NULL,
                'course_code' => NULL,
                'course_name' => NULL,
                'title' => NULL,
                'difficulty' => 'Medium',
                'question_heading' => NULL,
                'sub_questions' => NULL,
                'tags' => NULL,
                'year_semester' => NULL,
                'file_path' => '["question_banks\\/qiaq8rBsqPhH6lk6enxfrONksoQdo0zy0TsnywsB.jpg"]',
                'status' => 'approved',
                'created_at' => '2026-04-08 15:48:03',
                'updated_at' => '2026-05-14 11:32:06',
            ),
            3 => 
            array (
                'id' => 4,
                'user_id' => 1,
                'department' => 'SWE',
                'course_code' => 'AOL101',
                'course_name' => 'Art of Living',
                'title' => 'AOl-Midterm',
                'difficulty' => 'Medium',
                'question_heading' => 'Nothings',
                'sub_questions' => 'Explain what is Ethics',
                'tags' => 'Ethics,Etiqute,Morality',
                'year_semester' => 'Spring 2026',
                'file_path' => '["question_banks\\/R9TPzB1oeu3cgssk8iMYMCKfAFYVpsAws23IHcda.jpg","question_banks\\/wb9F5uIAvRY0bd2ykpPLY6geQyo3xUpuESXInI7c.jpg"]',
                'status' => 'approved',
                'created_at' => '2026-04-08 15:48:58',
                'updated_at' => '2026-04-08 15:51:06',
            ),
            4 => 
            array (
                'id' => 6,
                'user_id' => 1,
                'department' => 'SWE',
                'course_code' => 'SE411',
                'course_name' => 'Statistical Data Analysis',
                'title' => 'Statistical Data Analysis',
                'difficulty' => 'Medium',
                'question_heading' => 'Nothings',
                'sub_questions' => 'uhferbjfn',
                'tags' => 'Ethics,Etiqute,Morality',
                'year_semester' => 'Spring 2026',
                'file_path' => '["question_banks\\/9IkArvzG00ntPEe2oCkKag7qGsnJpXVwyrc0dBgQ.pdf","question_banks\\/moowGskY2MpaeeNGZsxpNWSNLidtlINTELkE41jX.pdf"]',
                'status' => 'approved',
                'created_at' => '2026-05-14 10:49:43',
                'updated_at' => '2026-05-14 10:53:32',
            ),
            5 => 
            array (
                'id' => 7,
                'user_id' => 1,
                'department' => NULL,
                'course_code' => NULL,
                'course_name' => NULL,
                'title' => NULL,
                'difficulty' => 'Medium',
                'question_heading' => NULL,
                'sub_questions' => NULL,
                'tags' => NULL,
                'year_semester' => NULL,
                'file_path' => '["question_banks\\/ZUfqx4YwJ2pQmEAEIJg80l1q7G4HNzbEpBz6rXPN.pdf"]',
                'status' => 'pending',
                'created_at' => '2026-05-14 11:19:35',
                'updated_at' => '2026-05-14 11:19:35',
            ),
            6 => 
            array (
                'id' => 8,
                'user_id' => 1,
                'department' => 'SWE',
                'course_code' => 'SE411',
                'course_name' => 'Statistical Data Analysis',
                'title' => 'Quiz',
                'difficulty' => 'Medium',
                'question_heading' => 'Permutations and Combinations',
                'sub_questions' => '["Counting arrangements of letters","Selecting students for an industry tour","Calculating possible phone numbers with constraints"]',
                'tags' => 'permutations, combinations, probability, statistics',
                'year_semester' => 'Spring-2026',
                'file_path' => '["question_banks\\/xlVEZ6tL92rVHvr4937oUmGcBs3FFGDcGO4vSQty.pdf"]',
                'status' => 'approved',
                'created_at' => '2026-05-14 11:38:16',
                'updated_at' => '2026-05-14 11:49:33',
            ),
        ));
        
        
    }
}