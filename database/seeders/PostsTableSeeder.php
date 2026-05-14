<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PostsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('posts')->delete();
        
        \DB::table('posts')->insert(array (
            0 => 
            array (
                'id' => 1,
                'user_id' => 1,
                'content' => 'We have create a Study group from Dept. "SWE"
Batch 40',
                'attachment' => 'posts/attachments/on1jjgaAlNOnXERw6RZtMgyc9GLtkDUNTshGtXRk.jpg',
                'type' => 'study_group',
                'created_at' => '2026-03-24 15:39:11',
                'updated_at' => '2026-03-24 15:39:11',
                'action_text' => NULL,
                'action_link' => NULL,
            ),
            1 => 
            array (
                'id' => 2,
                'user_id' => 8,
                'content' => 'We are looking for members for Caltural club.',
                'attachment' => 'posts/attachments/CV1OkmZLFCIplVf3u2OxT5OqUbRBi3nkWV8uHeMz.png',
                'type' => 'announcement',
                'created_at' => '2026-03-24 15:56:12',
                'updated_at' => '2026-03-24 15:56:12',
                'action_text' => 'Register',
                'action_link' => 'https://clubs.daffodilvarsity.edu.bd/club/DIUCC',
            ),
            2 => 
            array (
                'id' => 3,
                'user_id' => 9,
                'content' => 'DIU SWE department has been declared as IEB Member',
                'attachment' => 'posts/attachments/uxwyN6LhyV0JyGIXpgY3jpV8opQxSOLZVeC0Vrpd.jpg',
                'type' => 'general',
                'created_at' => '2026-03-30 13:06:33',
                'updated_at' => '2026-03-30 13:06:33',
                'action_text' => NULL,
                'action_link' => NULL,
            ),
            3 => 
            array (
                'id' => 4,
                'user_id' => 1,
                'content' => 'pohela Boishakh Niye kiso krte cassi na',
                'attachment' => 'posts/attachments/TNybC96m3y1od4Tr5Z9fQxSmnDoZoTrl1eDr0RBb.jpg',
                'type' => 'general',
                'created_at' => '2026-04-12 00:09:40',
                'updated_at' => '2026-04-12 00:09:40',
                'action_text' => NULL,
                'action_link' => NULL,
            ),
        ));
        
        
    }
}