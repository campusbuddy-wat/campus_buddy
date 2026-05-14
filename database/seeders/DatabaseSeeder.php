<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        $this->call(UsersTableSeeder::class);
        $this->call(AlumniRegistrationsTableSeeder::class);
        $this->call(AnnouncementsTableSeeder::class);
        $this->call(ClassTasksTableSeeder::class);
        $this->call(ClubsTableSeeder::class);
        $this->call(CommentLikesTableSeeder::class);
        $this->call(CommentsTableSeeder::class);
        $this->call(DistrictAssociationsTableSeeder::class);
        $this->call(DocsSectionsTableSeeder::class);
        $this->call(DocsSettingsTableSeeder::class);
        $this->call(DocsTeamMembersTableSeeder::class);
        $this->call(EventsTableSeeder::class);
        $this->call(LikesTableSeeder::class);
        $this->call(MaterialsTableSeeder::class);
        $this->call(PostsTableSeeder::class);
        $this->call(QuestionBanksTableSeeder::class);
        $this->call(SchedulesTableSeeder::class);
        $this->call(TalentsTableSeeder::class);
        $this->call(AiChatsTableSeeder::class);
    }
}