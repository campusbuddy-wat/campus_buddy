<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TalentsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('talents')->delete();
        
        \DB::table('talents')->insert(array (
            0 => 
            array (
                'id' => 1,
                'user_id' => 1,
                'designation' => 'Web Development',
                'id_no' => '0242310005341134',
                'blood_group' => '0+',
                'phone' => '0130990345',
                'email' => 'washimakram013099@gmail.com',
                'address' => 'Mirpur 2',
                'website' => 'https://web.facebook.com/M.islam07',
                'facebook_link' => NULL,
                'status' => 'pending',
                'created_at' => '2026-03-25 22:47:15',
                'updated_at' => '2026-03-30 13:15:18',
            ),
            1 => 
            array (
                'id' => 2,
                'user_id' => 2,
                'designation' => 'Any assignment,Presentation',
                'id_no' => '0242310005341298',
                'blood_group' => NULL,
                'phone' => '0130990345',
                'email' => 'islam2305341298@diu.edu.bd',
                'address' => NULL,
                'website' => 'https://www.linkedin.com/in/tanveer-m-islam-15321425a/',
                'facebook_link' => 'https://web.facebook.com/M.islam07',
                'status' => 'approved',
                'created_at' => '2026-03-25 23:20:43',
                'updated_at' => '2026-03-29 23:56:49',
            ),
            2 => 
            array (
                'id' => 3,
                'user_id' => 6,
                'designation' => 'Mobile App Dev',
                'id_no' => '0242310005341323',
                'blood_group' => NULL,
                'phone' => '+8801440889366',
                'email' => 'mayaz23105341323@diu.edu.bd',
                'address' => 'Chittagong City',
                'website' => 'https://linkedin.com/in/adnanmayaz',
                'facebook_link' => NULL,
                'status' => 'approved',
                'created_at' => '2026-03-25 23:27:36',
                'updated_at' => '2026-03-25 23:27:36',
            ),
            3 => 
            array (
                'id' => 4,
                'user_id' => 7,
                'designation' => 'Graphics Design',
                'id_no' => '0242310005341133',
                'blood_group' => '0+',
                'phone' => '+8801309903313',
                'email' => 'akram23105341134@diu.edu.bd',
                'address' => 'Mirpur, Dhaka',
                'website' => 'https://www.linkedin.com/in/washim-akram-a60a72361/',
                'facebook_link' => 'https://web.facebook.com/washim.akram.309534',
                'status' => 'approved',
                'created_at' => '2026-03-25 23:27:36',
                'updated_at' => '2026-03-29 23:54:28',
            ),
            4 => 
            array (
                'id' => 5,
                'user_id' => 8,
                'designation' => 'Public Speaker',
                'id_no' => '0242310005341289',
                'blood_group' => NULL,
                'phone' => '+8801584599781',
                'email' => 'taba23105341289@diu.edu.bd',
                'address' => 'Sylhet Town',
                'website' => 'https://linkedin.com/in/tabassumaktersadia',
                'facebook_link' => NULL,
                'status' => 'approved',
                'created_at' => '2026-03-25 23:27:36',
                'updated_at' => '2026-03-25 23:27:36',
            ),
            5 => 
            array (
                'id' => 6,
                'user_id' => 9,
                'designation' => 'Mobile App Dev',
                'id_no' => '0242310005341026',
                'blood_group' => NULL,
                'phone' => '+8801919082593',
                'email' => 'mahmud23105341134@diu.edu.bd',
                'address' => 'Rajshahi',
                'website' => 'https://linkedin.com/in/shihabmahmud',
                'facebook_link' => NULL,
                'status' => 'approved',
                'created_at' => '2026-03-25 23:27:36',
                'updated_at' => '2026-03-25 23:27:36',
            ),
        ));
        
        
    }
}