<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SyncTask;
use Symfony\Component\Process\Process;

class RunSyncTask extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:run-task {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Wrapper to run a sync task background process and track its output and database status';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $id = $this->argument('id');
        $task = SyncTask::find($id);

        if (!$task) {
            $this->error("SyncTask ID {$id} not found.");
            return 1;
        }

        $task->update([
            'status' => 'running',
            'started_at' => now(),
        ]);

        $this->info("=== Starting Task: {$task->name} (Type: {$task->type}) ===");
        if ($task->url) {
            $this->info("Source URL: {$task->url}");
        }
        $this->info("Started at: " . now()->toDateTimeString());
        $this->info("--------------------------------------------------");

        // Construct the underlying Artisan command to run
        $cmd = [PHP_BINARY, base_path('artisan')];
        if ($task->type === 'm3u') {
            $cmd[] = 'm3u:sync';
            $cmd[] = $task->url;
        } elseif ($task->type === 'fancode') {
            $cmd[] = 'fancode:sync';
        } elseif ($task->type === 'bdixtv24') {
            $cmd[] = 'bdixtv24:sync';
        } elseif ($task->type === 'redforce') {
            $cmd[] = 'redforce:sync';
        } elseif ($task->type === 'bdix198') {
            $cmd[] = 'bdix198:sync';
        } elseif ($task->type === 'link-checker') {
            $cmd[] = 'streams:check-links';
        } elseif ($task->type === 'clean-duplicates') {
            $cmd[] = 'streams:clean-duplicates';
        } elseif ($task->type === 'clean-duplicates-ai') {
            $cmd[] = 'streams:clean-duplicates';
            $cmd[] = '--ai';
        } elseif ($task->type === 'category-cleanup') {
            $cmd[] = 'categories:cleanup';
        } else {
            $errorMsg = "Unknown task type: {$task->type}";
            $this->error($errorMsg);
            $task->update([
                'status' => 'failed',
                'completed_at' => now(),
            ]);
            return 1;
        }

        try {
            // Spawn the command process
            $process = new Process($cmd, base_path());
            $process->setTimeout(null); // Disable timeout limit

            // Run the process and write output in real time
            $exitCode = $process->run(function ($type, $buffer) {
                // Echo directly to stdout/stderr so that it streams to the log file via shell redirect
                echo $buffer;
            });

            $status = ($exitCode === 0) ? 'completed' : 'failed';
            
            $task->update([
                'status' => $status,
                'completed_at' => now(),
            ]);

            $this->info("--------------------------------------------------");
            $this->info("Finished at: " . now()->toDateTimeString());
            $this->info("Exit Code: {$exitCode}");
            $this->info("Status: " . strtoupper($status));

            return $exitCode;
        } catch (\Exception $e) {
            $this->error("Execution error: " . $e->getMessage());
            $task->update([
                'status' => 'failed',
                'completed_at' => now(),
            ]);
            return 1;
        }
    }
}
