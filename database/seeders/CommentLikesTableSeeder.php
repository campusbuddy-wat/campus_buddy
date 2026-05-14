<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CommentLikesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('comment_likes')->delete();
        
        \DB::table('comment_likes')->insert(array (
            0 => 
            array (
                'id' => 1,
                'comment_id' => 6,
                'user_id' => 2,
                'created_at' => '2026-03-24 21:38:38',
                'updated_at' => '2026-03-24 21:38:38',
            ),
            1 => 
            array (
                'id' => 2,
                'comment_id' => 7,
                'user_id' => 1,
                'created_at' => '2026-03-25 21:47:02',
                'updated_at' => '2026-03-25 21:47:02',
            ),
        ));
        
        
    }
}