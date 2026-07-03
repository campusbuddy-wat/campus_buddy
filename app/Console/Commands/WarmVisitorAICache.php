<?php

namespace App\Console\Commands;

use App\Services\DIUWebScraperService;
use Illuminate\Console\Command;

/**
 * WarmVisitorAICache
 *
 * Pre-fetches all DIU source pages and stores them in the Laravel cache
 * so the first visitor never waits for a cold HTTP fetch (3–5s per source).
 *
 * Usage:
 *   php artisan visitor-ai:warm-cache
 *
 * Scheduling (routes/console.php):
 *   Schedule::command('visitor-ai:warm-cache')->everySixHours();
 */
class WarmVisitorAICache extends Command
{
    protected $signature = 'visitor-ai:warm-cache';

    protected $description = 'Pre-warm the Visitor AI cache by fetching all official DIU web sources';

    public function handle(DIUWebScraperService $scraper): int
    {
        $this->info('🚀 Warming Visitor AI cache from DIU websites...');
        $this->newLine();

        $report = $scraper->warmCache();

        $table = [];
        $failCount = 0;

        foreach ($report as $label => $status) {
            $icon = $status === 'ok' ? '✅' : '❌';
            $table[] = [$icon, $label, strtoupper($status)];
            if ($status !== 'ok') {
                $failCount++;
            }
        }

        $this->table(['Status', 'Source', 'Result'], $table);
        $this->newLine();

        $total   = count($report);
        $success = $total - $failCount;

        if ($failCount === 0) {
            $this->info("✅ All {$total} sources cached successfully. Cache valid for 6 hours.");
        } elseif ($success > 0) {
            $this->warn("⚠️  {$success}/{$total} sources cached. {$failCount} source(s) failed — AI will fall back to static facts for those.");
        } else {
            $this->error("❌ All {$total} sources failed. Visitor AI will use static knowledge base only.");
        }

        return $failCount === 0 ? self::SUCCESS : self::FAILURE;
    }
}
