<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class LikesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('likes')->delete();
        
        \DB::table('likes')->insert(array (
            0 => 
            array (
                'id' => 1,
                'post_id' => 1,
                'user_id' => 1,
                'created_at' => '2026-03-24 15:46:47',
                'updated_at' => '2026-03-24 15:46:47',
            ),
            1 => 
            array (
                'id' => 2,
                'post_id' => 1,
                'user_id' => 8,
                'created_at' => '2026-03-24 15:51:18',
                'updated_at' => '2026-03-24 15:51:18',
            ),
            2 => 
            array (
                'id' => 3,
                'post_id' => 2,
                'user_id' => 8,
                'created_at' => '2026-03-24 15:56:39',
                'updated_at' => '2026-03-24 15:56:39',
            ),
            3 => 
            array (
                'id' => 6,
                'post_id' => 2,
                'user_id' => 9,
                'created_at' => '2026-03-30 13:02:37',
                'updated_at' => '2026-03-30 13:02:37',
            ),
            4 => 
            array (
                'id' => 7,
                'post_id' => 3,
                'user_id' => 1,
                'created_at' => '2026-04-03 00:51:06',
                'updated_at' => '2026-04-03 00:51:06',
            ),
            5 => 
            array (
                'id' => 9,
                'post_id' => 2,
                'user_id' => 1,
                'created_at' => '2026-04-12 00:17:44',
                'updated_at' => '2026-04-12 00:17:44',
            ),
        ));
        
        
    }
}