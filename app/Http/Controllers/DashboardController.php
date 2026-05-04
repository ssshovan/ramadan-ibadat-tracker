<?php

namespace App\Http\Controllers;

use App\Models\IbadatLog;
use App\Models\Streak;
use App\Models\Milestone;
use App\Services\StreakService;
use App\Services\ProgressService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

/**
 * Dashboard Controller
 * 
 * Handles the main dashboard view and daily overview.
 * Displays daily tracking, streaks, and reminders.
 */
class DashboardController extends Controller
{
    protected $streakService;
    protected $progressService;

    /**
     * Constructor with dependency injection.
     */
    public function __construct(
        StreakService $streakService,
        ProgressService $progressService
    ) {
        $this->streakService = $streakService;
        $this->progressService = $progressService;
    }

    /**
     * Display the main dashboard.
     * 
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();

        // Get or create today's ibadat log
        $todayLog = $this->getOrCreateTodayLog($user->id);

        // Get all streaks
        $streaks = $this->streakService->getAllStreaks($user->id);

        // Get weekly progress
        $weeklyProgress = $this->progressService->getWeeklyProgress($user->id);

        // Get recent milestones
        $recentMilestones = Milestone::forUser($user->id)
                                     ->recent(5)
                                     ->get();

        // Get motivational quote
        $quote = $this->getDailyQuote();

        // Get family info if user belongs to one
        $family = $user->primaryFamily();
        $familyProgress = null;
        if ($family) {
            $familyProgress = $family->family_progress;
        }

        // Get reminder
        $reminder = $this->getDailyReminder();

        return view('dashboard', compact(
            'todayLog',
            'streaks',
            'weeklyProgress',
            'recentMilestones',
            'quote',
            'family',
            'familyProgress',
            'reminder'
        ));
    }

    /**
     * Get or create today's ibadat log for user.
     *
     * @param int $userId
     * @return IbadatLog
     */
    private function getOrCreateTodayLog(int $userId): IbadatLog
    {
        $log = IbadatLog::forUser($userId)
                        ->forDate(today())
                        ->first();

        if (!$log) {
            $log = IbadatLog::create([
                'user_id' => $userId,
                'log_date' => today(),
                'roza_completed' => false,
                'progress_percentage' => 0,
            ]);
        }

        return $log;
    }

    /**
     * Get daily motivational quote.
     *
     * @return array
     */
    private function getDailyQuote(): array
    {
        $quotes = [
            [
                'text' => 'The best of you are those who learn the Quran and teach it.',
                'source' => 'Hadith - Bukhari',
            ],
            [
                'text' => 'Indeed, prayer prohibits immorality and wrongdoing.',
                'source' => 'Quran 29:45',
            ],
            [
                'text' => 'Charity does not decrease wealth.',
                'source' => 'Hadith - Muslim',
            ],
            [
                'text' => 'Whoever fasts Ramadan out of faith and hope, his sins are forgiven.',
                'source' => 'Hadith - Bukhari & Muslim',
            ],
            [
                'text' => 'Take advantage of five before five: youth before old age.',
                'source' => 'Hadith - Al-Hakim',
            ],
            [
                'text' => 'The most beloved deeds are those done consistently, even if small.',
                'source' => 'Hadith - Bukhari & Muslim',
            ],
            [
                'text' => 'Ramadan is the month of patience and the reward of patience is Paradise.',
                'source' => 'Hadith - Ahmad',
            ],
            [
                'text' => 'When Ramadan begins, the gates of Paradise are opened.',
                'source' => 'Hadith - Bukhari',
            ],
            [
                'text' => 'Allah does not burden a soul beyond that it can bear.',
                'source' => 'Quran 2:286',
            ],
            [
                'text' => 'So remember Me; I will remember you.',
                'source' => 'Quran 2:152',
            ],
            [
                'text' => 'Verily, with hardship comes ease.',
                'source' => 'Quran 94:6',
            ],
            [
                'text' => 'Do good deeds properly, sincerely and moderately.',
                'source' => 'Hadith - Bukhari',
            ],
            [
                'text' => 'The strong believer is better and more beloved to Allah than the weak believer.',
                'source' => 'Hadith - Muslim',
            ],
            [
                'text' => 'Smiling in your brother’s face is charity.',
                'source' => 'Hadith - Tirmidhi',
            ],
            [
                'text' => 'The best among you are those who have the best manners.',
                'source' => 'Hadith - Bukhari',
            ],
            [
                'text' => 'And He found you lost and guided you.',
                'source' => 'Quran 93:7',
            ],
            [
                'text' => 'Indeed, Allah is with those who are patient.',
                'source' => 'Quran 2:153',
            ],
            [
                'text' => 'Seek help through patience and prayer.',
                'source' => 'Quran 2:45',
            ],
            [
                'text' => 'The dunya is a prison for the believer and paradise for the disbeliever.',
                'source' => 'Hadith - Muslim',
            ],
            [
                'text' => 'A good word is charity.',
                'source' => 'Hadith - Bukhari & Muslim',
            ],
            [
                'text' => 'Keep your tongue moist with the remembrance of Allah.',
                'source' => 'Hadith - Tirmidhi',
            ],
            [
                'text' => 'Whoever believes in Allah and the Last Day should speak good or remain silent.',
                'source' => 'Hadith - Bukhari & Muslim',
            ],
            [
                'text' => 'And Allah loves those who purify themselves.',
                'source' => 'Quran 9:108',
            ],
            [
                'text' => 'Do not lose hope in the mercy of Allah.',
                'source' => 'Quran 39:53',
            ],
            [
                'text' => 'The best wealth is a rich soul.',
                'source' => 'Hadith - Bukhari & Muslim',
            ],
            [
                'text' => 'Help one another in righteousness and piety.',
                'source' => 'Quran 5:2',
            ],
            [
                'text' => 'The believer’s shade on the Day of Judgment will be his charity.',
                'source' => 'Hadith - Tirmidhi',
            ],
            [
                'text' => 'Allah is beautiful and loves beauty.',
                'source' => 'Hadith - Muslim',
            ],
            [
                'text' => 'Make things easy and do not make them difficult.',
                'source' => 'Hadith - Bukhari',
            ],
            [
                'text' => 'Every soul will taste death.',
                'source' => 'Quran 3:185',
            ],
        ];

        // Return quote based on minutes
        $minuteOfDay = now()->format('H') * 60 + now()->format('i');
        $index = $minuteOfDay % count($quotes);

        return $quotes[$index];
    }

    /**
     * Get daily reminder message.
     *
     * @return string
     */
    private function getDailyReminder(): string
    {
        $hour = now()->hour;

        if ($hour < 5) {
            return 'Don\'t forget to pray Fajr and start your day with barakah!';
        } elseif ($hour < 12) {
            return 'Have you completed your morning prayers and Quran reading?';
        } elseif ($hour < 15) {
            return 'Remember to pray Dhuhr and make dua in this blessed hour!';
        } elseif ($hour < 18) {
            return 'Time for Asr prayer. Keep your streak going!';
        } elseif ($hour < 20) {
            return 'Prepare for Maghrib and break your fast with dates and water.';
        } else {
            return 'Don\'t forget Isha prayer and your nightly Quran recitation!';
        }
    }

    /**
     * Get quick stats for AJAX requests.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getQuickStats()
    {
        $user = Auth::user();

        $stats = [
            'today_progress' => $this->progressService->getTodayProgress($user->id),
            'streaks' => $this->streakService->getAllStreaks($user->id),
            'weekly_average' => $this->progressService->getWeeklyAverage($user->id),
        ];

        return response()->json($stats);
    }
}
