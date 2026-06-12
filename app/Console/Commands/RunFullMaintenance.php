<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class RunFullMaintenance extends Command
{
    protected $signature = 'maintenance:full';
    protected $description = 'Run all maintenance tasks in sequence: duplicate cleaner (AI), category cleanup (AI), stream link checker';

    public function handle()
    {
        $this->info('');
        $this->info('╔══════════════════════════════════════════╗');
        $this->info('║       Full Maintenance Run Started       ║');
        $this->info('╚══════════════════════════════════════════╝');
        $this->info('Started at: ' . now()->toDateTimeString());
        $this->info('');

        $steps = [
            [
                'label'   => '1. Smart Duplicate Cleaner (AI-assisted)',
                'command' => 'streams:clean-duplicates',
                'args'    => ['--ai' => true],
            ],
            [
                'label'   => '2. AI Category Cleanup',
                'command' => 'categories:cleanup',
                'args'    => [],
            ],
            [
                'label'   => '3. Stream Link Checker & Cleaner',
                'command' => 'streams:check-links',
                'args'    => [],
            ],
        ];

        foreach ($steps as $step) {
            $this->info('──────────────────────────────────────────');
            $this->info("▶ {$step['label']}");
            $this->info('──────────────────────────────────────────');

            try {
                $exitCode = Artisan::call($step['command'], $step['args']);
                $output = Artisan::output();
                $this->line(trim($output));

                if ($exitCode === 0) {
                    $this->info("✓ {$step['label']} — completed successfully.");
                } else {
                    $this->warn("⚠ {$step['label']} — finished with exit code {$exitCode}.");
                }
            } catch (\Exception $e) {
                $this->error("✗ {$step['label']} — failed: " . $e->getMessage());
            }

            $this->line('');
        }

        $this->info('╔══════════════════════════════════════════╗');
        $this->info('║         All Tasks Completed              ║');
        $this->info('╚══════════════════════════════════════════╝');
        $this->info('Finished at: ' . now()->toDateTimeString());

        return 0;
    }
}
