<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ClubsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('clubs')->delete();
        
        \DB::table('clubs')->insert(array (
            0 => 
            array (
                'id' => 1,
                'name' => 'Computer Programming Club',
                'type' => 'tech',
                'description' => 'DIU CPC is the most primitive and extensive club of our University. We work together to explore every field of Computer science. Join us to know more.',
                'image_path' => 'clubs/01KKYBSH0ZVSNG7BZPQNVYZVF8.jpeg',
                'website_link' => 'https://clubs.daffodilvarsity.edu.bd/club/diucpc',
                'created_at' => '2026-03-17 22:54:26',
                'updated_at' => '2026-03-17 22:59:15',
            ),
            1 => 
            array (
                'id' => 2,
                'name' => 'Robotics Innovation Lab',
                'type' => 'tech',
                'description' => 'DIU Robotics Club is a dream to improve skills and inspire generations of young innovative Engineering students with seminars and workshops.',
                'image_path' => 'clubs/01KKYC82RFVKFG405365MCQNKH.jpeg',
                'website_link' => 'https://clubs.daffodilvarsity.edu.bd/club/diurc',
                'created_at' => '2026-03-17 22:54:26',
                'updated_at' => '2026-03-17 23:07:12',
            ),
            2 => 
            array (
                'id' => 3,
                'name' => 'Change Together Club',
                'type' => 'arts',
                'description' => 'Our vision is to create a community baseline that can change this world, mitigate negativity and bring happiness for everyone.',
                'image_path' => 'clubs/01KKYC8QXJ76SFT1Z40NN9EM8J.png',
                'website_link' => 'https://clubs.daffodilvarsity.edu.bd/club/ctc',
                'created_at' => '2026-03-17 22:54:26',
                'updated_at' => '2026-03-17 23:07:33',
            ),
            3 => 
            array (
                'id' => 4,
                'name' => 'DIU Photographic Society',
                'type' => 'arts',
                'description' => 'Founded in 2011 to organize photographers in the University and promote the art of photography through exhibitions.',
                'image_path' => 'clubs/01KKYC9AF7BME6X2W5JWGNBAFB.png',
                'website_link' => 'https://clubs.daffodilvarsity.edu.bd/club/diups',
                'created_at' => '2026-03-17 22:54:26',
                'updated_at' => '2026-03-17 23:07:52',
            ),
            4 => 
            array (
                'id' => 5,
                'name' => 'DIU Cultural Club',
                'type' => 'arts',
                'description' => 'Our mission is to promote & enrich our tradition and culture in and beyond the country through music, dance and art.',
                'image_path' => 'clubs/01KKYC9V8B7XJ4966PAVF2BJWH.png',
                'website_link' => 'https://clubs.daffodilvarsity.edu.bd/club/DIUCC',
                'created_at' => '2026-03-17 22:54:26',
                'updated_at' => '2026-03-17 23:08:10',
            ),
            5 => 
            array (
                'id' => 6,
                'name' => 'All Stars Daffodil',
                'type' => 'arts',
                'description' => 'All Stars Daffodil is a drama organization. It is the only theater club at DIU, practicing pure Bengali culture through various plays.',
                'image_path' => 'clubs/01KKYCXC1ZQJ0W11KNMW8D8JR7.png',
                'website_link' => 'https://clubs.daffodilvarsity.edu.bd/club/asd',
                'created_at' => '2026-03-17 22:54:26',
                'updated_at' => '2026-03-17 23:18:49',
            ),
            6 => 
            array (
                'id' => 7,
                'name' => 'Debate & Model UN',
                'type' => 'academic',
                'description' => 'DIU DC has a reputation for participating in various national and international tournaments. We believe in reasoning.',
                'image_path' => 'images/clubs/debate.png',
                'website_link' => 'https://clubs.daffodilvarsity.edu.bd/club/diudc',
                'created_at' => '2026-03-17 22:54:26',
                'updated_at' => '2026-03-17 22:54:26',
            ),
            7 => 
            array (
                'id' => 8,
                'name' => 'University Sports Athletics',
                'type' => 'sports',
                'description' => 'From soccer to basketball, join our community of student athletes to stay fit and compete representing DIU.',
                'image_path' => 'clubs/01KM61676K53FAFZBE7WNFV3YW.png',
                'website_link' => 'https://clubs.daffodilvarsity.edu.bd/club/diusports',
                'created_at' => '2026-03-17 22:54:26',
                'updated_at' => '2026-03-20 22:27:52',
            ),
        ));
        
        
    }
}