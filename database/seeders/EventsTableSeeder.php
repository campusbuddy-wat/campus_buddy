<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class EventsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('events')->delete();
        
        \DB::table('events')->insert(array (
            0 => 
            array (
                'id' => 1,
                'title' => 'Foundation Say',
                'description' => 'Annouce in short time',
                'image_path' => 'events/01KKWZWZK3ZA6HZBR557HE84RA.jpeg',
                'created_at' => '2026-03-17 10:12:11',
                'updated_at' => '2026-03-17 16:13:10',
                'event_date' => '2026-03-27',
            ),
            1 => 
            array (
                'id' => 2,
                'title' => '!3th Convocation',
                'description' => 'Our graduates celebrated this special day with the friends they dreamed alongside for the last four years. The joy was made even more memorable with an electrifying performance by the popular band Warfaze, turning the celebration into an unforgettable experience.
Graduates, you can collect your photo from here: 
https://medialab.diu.edu.bd/13th-convocation-2026',
                'image_path' => 'events/01KKXK28XM8XCF75A73PX04S8E.jpg',
                'created_at' => '2026-03-17 15:47:07',
                'updated_at' => '2026-03-17 15:47:07',
                'event_date' => NULL,
            ),
            2 => 
            array (
                'id' => 3,
                'title' => 'Internation Mother Language Day',
                'description' => 'মহান শহীদ দিবস ও আন্তর্জাতিক মাতৃভাষা দিবসে ভাষা শহীদদের প্রতি ড্যাফোডিল ইন্টারন্যাশনাল ইউনিভার্সিটির পক্ষ থেকে গভীর শ্রদ্ধা ও কৃতজ্ঞতা। আপনারাই আমাদের ভাষা, পরিচয় ও আত্মমর্যাদার প্রেরণা।
',
                'image_path' => 'events/01KKXK4QSJM25G6JVMMB5WYEMT.jpg',
                'created_at' => '2026-03-17 15:48:28',
                'updated_at' => '2026-03-17 15:48:28',
                'event_date' => NULL,
            ),
            3 => 
            array (
                'id' => 4,
                'title' => 'I𝐧𝐭𝐞𝐫𝐧𝐚𝐭𝐢𝐨𝐧𝐚𝐥 𝐖𝐨𝐦𝐞𝐧’𝐬 𝐃𝐚𝐲 𝟐𝟎𝟐𝟔',
                'description' => 'Daffodil International University observed 𝐈𝐧𝐭𝐞𝐫𝐧𝐚𝐭𝐢𝐨𝐧𝐚𝐥 𝐖𝐨𝐦𝐞𝐧’𝐬 𝐃𝐚𝐲 𝟐𝟎𝟐𝟔 with a program titled “Rights in Action: Give Support, Gain Safety”, organized by the Complaint Committee to Prevent Sexual Harassment.
The day began with a rally where students, faculty members, and staff joined together to show their commitment to dignity, equality, and a safe campus environment.
The discussion session featured distinguished speakers including the Honorable Vice Chancellor Prof. Dr. M. R. Kabir, along with other respected academic leaders and guests, highlighting the importance of turning awareness of rights into meaningful action. The program emphasized that through mutual respect, empathy, and responsible behavior, a supportive and safe environment can be created for everyone.',
                'image_path' => 'events/01KKXK770DWRG698CAVE1AWP03.jpg',
                'created_at' => '2026-03-17 15:49:49',
                'updated_at' => '2026-03-17 16:13:29',
                'event_date' => '2026-04-11',
            ),
        ));
        
        
    }
}