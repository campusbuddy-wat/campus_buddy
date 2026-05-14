<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DistrictAssociationsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('district_associations')->delete();
        
        \DB::table('district_associations')->insert(array (
            0 => 
            array (
                'id' => 1,
            'name' => 'DIU Students Association of Barishal" (DIUSAB)',
                'division' => 'Barishal',
                'district' => 'Barishal',
                'image' => 'community/district_associations/logos/01KMJRTYNN377GCN73FN3WRA28.png',
                'link' => 'https://web.facebook.com/groups/185688217017559/?_rdc=1&_rdr#',
                'members_count' => 200,
                'created_at' => '2026-03-24 22:27:31',
                'updated_at' => '2026-04-08 16:21:17',
                'cover_image' => 'community/district_associations/covers/01KMJRTYNPWAR4RXM6D30DAN9C.jpeg',
            ),
            1 => 
            array (
                'id' => 2,
                'name' => 'Manikganj Students Association-DIU',
                'division' => 'Dhaka',
                'district' => 'Manikganj',
                'image' => 'community/district_associations/logos/01KMJSRER3DTW13EZ4JXA3YG6Y.png',
                'link' => 'https://www.facebook.com/MSADIU/',
                'members_count' => 90,
                'created_at' => '2026-03-24 22:41:40',
                'updated_at' => '2026-04-08 16:20:50',
                'cover_image' => 'community/district_associations/covers/01KMJSRER4H201KZF9Z1CTG07K.jpeg',
            ),
            2 => 
            array (
                'id' => 3,
                'name' => 'Chattogram Students Association DIU',
                'division' => 'Chattogram',
                'district' => 'Chattogram',
                'image' => '01KMGBMK23Z46SG3R139PS0QAY.png',
                'link' => 'https://www.facebook.com/profile.php?id=100089556866565',
                'members_count' => 100,
                'created_at' => '2026-03-24 22:42:53',
                'updated_at' => '2026-04-08 16:27:04',
                'cover_image' => 'community/district_associations/covers/01KMJSVV0QRG4WPX19VWZDF9SH.jpeg',
            ),
            3 => 
            array (
                'id' => 4,
                'name' => 'Netrakona Student Association Of DIU',
                'division' => 'Mymensingh',
                'district' => 'Netrokona',
                'image' => 'community/district_associations/logos/01KMJT62B61GW0AP8Y8F8ANC8F.png',
                'link' => 'https://web.facebook.com/people/Netrakona-Student-Association-Of-DIU/61557633962516/?_rdc=1&_rdr#',
                'members_count' => 50,
                'created_at' => '2026-03-25 21:35:35',
                'updated_at' => '2026-04-08 16:29:45',
                'cover_image' => 'community/district_associations/covers/01KMJT62B8JYM0KF8MEVWC8VA9.jpeg',
            ),
            4 => 
            array (
                'id' => 5,
                'name' => 'Gazipur District Student Association-DIU',
                'division' => 'Dhaka',
                'district' => 'Gazipur',
                'image' => 'community/district_associations/logos/01KMJTF5AN1CB954AZ1B7TZ0CC.png',
                'link' => 'https://web.facebook.com/diugazipurassociation/?_rdc=1&_rdr#',
                'members_count' => 77,
                'created_at' => '2026-03-25 21:40:32',
                'updated_at' => '2026-04-08 16:27:19',
                'cover_image' => 'community/district_associations/covers/01KMJTF5AN1CB954AZ1B7TZ0CD.jpeg',
            ),
            5 => 
            array (
                'id' => 6,
                'name' => 'Gaibandha Zilla Student Association DIU',
                'division' => 'Rangpur',
                'district' => 'Gaibandha',
                'image' => 'community/district_associations/logos/01KMJTSVSSVJY5425A1REX68AT.jpg',
                'link' => 'https://web.facebook.com/gzsadiu/?profile_tab_item_selected=about&_rdc=1&_rdr#',
                'members_count' => 94,
                'created_at' => '2026-03-25 21:46:23',
                'updated_at' => '2026-04-08 16:30:24',
                'cover_image' => 'community/district_associations/covers/01KMJTSVSSVJY5425A1REX68AV.jpg',
            ),
        ));
        
        
    }
}