<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CommentsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('comments')->delete();
        
        \DB::table('comments')->insert(array (
            0 => 
            array (
                'id' => 1,
                'post_id' => 1,
                'user_id' => 1,
                'content' => 'Interested',
                'created_at' => '2026-03-24 15:46:56',
                'updated_at' => '2026-03-24 15:46:56',
                'parent_id' => NULL,
            ),
            1 => 
            array (
                'id' => 2,
                'post_id' => 1,
                'user_id' => 1,
                'content' => 'Interested2',
                'created_at' => '2026-03-24 15:47:20',
                'updated_at' => '2026-03-24 15:47:20',
                'parent_id' => NULL,
            ),
            2 => 
            array (
                'id' => 3,
                'post_id' => 1,
                'user_id' => 1,
                'content' => 'Interested3',
                'created_at' => '2026-03-24 15:49:45',
                'updated_at' => '2026-03-24 15:49:45',
                'parent_id' => NULL,
            ),
            3 => 
            array (
                'id' => 4,
                'post_id' => 1,
                'user_id' => 8,
                'content' => 'Yes',
                'created_at' => '2026-03-24 15:53:31',
                'updated_at' => '2026-03-24 15:53:31',
                'parent_id' => NULL,
            ),
            4 => 
            array (
                'id' => 5,
                'post_id' => 2,
                'user_id' => 8,
                'content' => 'How can I register',
                'created_at' => '2026-03-24 15:58:53',
                'updated_at' => '2026-03-24 15:58:53',
                'parent_id' => NULL,
            ),
            5 => 
            array (
                'id' => 6,
                'post_id' => 2,
                'user_id' => 1,
                'content' => 'How can I register',
                'created_at' => '2026-03-24 15:59:56',
                'updated_at' => '2026-03-24 15:59:56',
                'parent_id' => NULL,
            ),
            6 => 
            array (
                'id' => 7,
                'post_id' => 2,
                'user_id' => 2,
                'content' => 'Interested',
                'created_at' => '2026-03-24 21:06:38',
                'updated_at' => '2026-03-24 21:06:38',
                'parent_id' => NULL,
            ),
            7 => 
            array (
                'id' => 8,
                'post_id' => 2,
                'user_id' => 2,
                'content' => 'Can you get any answer?',
                'created_at' => '2026-03-24 21:39:00',
                'updated_at' => '2026-03-24 21:39:00',
                'parent_id' => 6,
            ),
            8 => 
            array (
                'id' => 9,
                'post_id' => 2,
                'user_id' => 1,
                'content' => 'nice',
                'created_at' => '2026-03-25 21:46:58',
                'updated_at' => '2026-03-25 21:46:58',
                'parent_id' => 7,
            ),
            9 => 
            array (
                'id' => 11,
                'post_id' => 3,
                'user_id' => 1,
                'content' => 'Congress',
                'created_at' => '2026-04-12 00:17:35',
                'updated_at' => '2026-04-12 00:17:35',
                'parent_id' => NULL,
            ),
        ));
        
        
    }
}