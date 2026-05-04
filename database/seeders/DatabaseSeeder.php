<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\IbadatLog;
use App\Models\Prayer;
use App\Models\CharityRecord;
use App\Models\QuranLog;
use App\Models\Streak;
use App\Models\Family;
use App\Models\FamilyMember;
use App\Models\Note;
use App\Models\Milestone;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create test user
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'bio' => 'A dedicated Muslim trying to improve daily ibadat',
            'email_notifications' => true,
        ]);

        // Create ibadat logs for the past 7 days
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            
            $ibadatLog = IbadatLog::create([
                'user_id' => $user->id,
                'log_date' => $date,
                'roza_completed' => true,
                'progress_percentage' => rand(60, 100),
            ]);

            // Update prayers (random completion)
            foreach ($ibadatLog->prayers as $prayer) {
                if (rand(1, 10) > 2) { // 80% chance of completion
                    $prayer->markAsCompleted();
                }
            }

            // Update Quran log
            $ibadatLog->quranLog->update([
                'pages_read' => rand(1, 10),
            ]);

            // Update charity record
            $ibadatLog->charityRecord->update([
                'amount' => rand(0, 50),
                'acts_count' => rand(0, 3),
            ]);

            // Recalculate progress
            $ibadatLog->updateProgress();

            // Add a note for some days
            if (rand(1, 3) == 1) {
                Note::create([
                    'ibadat_log_id' => $ibadatLog->id,
                    'category' => 'reflection',
                    'content' => 'Alhamdulillah for another blessed day of Ramadan.',
                    'is_important' => rand(0, 1),
                ]);
            }
        }

        // Initialize streaks for user
        $streakTypes = ['prayer', 'fasting', 'quran', 'charity'];
        foreach ($streakTypes as $type) {
            Streak::create([
                'user_id' => $user->id,
                'streak_type' => $type,
                'current_streak' => rand(3, 7),
                'longest_streak' => rand(7, 14),
                'last_updated' => Carbon::today(),
            ]);
        }

        // Create a family
        $family = Family::create([
            'name' => 'The Test Family',
            'description' => 'A family dedicated to improving our ibadat together',
            'created_by' => $user->id,
            'family_streak' => 5,
        ]);

        // Add user as parent
        FamilyMember::create([
            'family_id' => $family->id,
            'user_id' => $user->id,
            'role' => 'parent',
            'joined_at' => Carbon::now(),
            'is_active' => true,
        ]);

        // Create another family member
        $member2 = User::create([
            'name' => 'Family Member',
            'email' => 'member@example.com',
            'password' => Hash::make('password'),
        ]);

        FamilyMember::create([
            'family_id' => $family->id,
            'user_id' => $member2->id,
            'role' => 'child',
            'joined_at' => Carbon::now(),
            'is_active' => true,
        ]);

        // Create some milestones
        Milestone::create([
            'user_id' => $user->id,
            'type' => 'prayer_streak_7',
            'name' => '7-Day Prayer Streak',
            'description' => 'Completed all 5 prayers for 7 consecutive days!',
            'icon' => '🥉',
            'earned_at' => Carbon::now()->subDays(3),
        ]);

        Milestone::create([
            'user_id' => $user->id,
            'type' => 'fasting_streak_7',
            'name' => '7-Day Fasting Streak',
            'description' => 'Fasted for 7 consecutive days!',
            'icon' => '🥉',
            'earned_at' => Carbon::now()->subDays(2),
        ]);

        $this->command->info('Database seeded successfully!');
        $this->command->info('Test user: test@example.com / password');
        $this->command->info('Family code: ' . $family->family_code);
    }
}
