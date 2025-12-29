<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class DatabaseBackup extends Command
{
    protected $signature = 'database:backup';
    protected $description = 'Backup main DB and school DB and email the dumps';

    public function handle()
    {
        $date = Carbon::now()->format('Y-m-d');

        // MAIN DATABASE (from .env)
        $mainDb = env('DB_DATABASE');

        // SCHOOL DATABASE (fixed name or env variable)
        $schoolDb = 'eschool_saas_5_asif'; // you may put env('SCHOOL_DB') if needed

        // Backup Paths
        $backupDir = storage_path("app/backup");
        if (!File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0777, true);
        }

        $mainFile = $backupDir . "/backup-main-$date.gz";
        $schoolFile = $backupDir . "/backup-school-$date.gz";

        // Run backups
        $this->backupDatabase($mainDb, $mainFile);
        $this->backupDatabase($schoolDb, $schoolFile);

        // Email backups
        $this->sendBackupEmail([$mainFile, $schoolFile]);

        return 0;
    }


    private function backupDatabase($dbName, $filePath)
    {
        $command = "mysqldump --user=" . env('DB_USERNAME') .
            " --password=" . env('DB_PASSWORD') .
            " --host=" . env('DB_HOST') .
            " $dbName | gzip > $filePath";

        $returnVar = null;
        exec($command, $output, $returnVar);

        if ($returnVar === 0 && file_exists($filePath)) {
            Log::info("Backup created: $filePath");
        } else {
            Log::error("Backup FAILED for DB: $dbName");
        }
    }


    private function sendBackupEmail($files)
    {
        try {
            Mail::raw('Attached are today’s DB backups.', function ($message) use ($files) {
                $message->to('hrskschool@gmail.com')
                    ->subject('HRSK: Daily DB Backups');

                foreach ($files as $file) {
                    if (file_exists($file)) {
                        $message->attach($file);
                    }
                }
            });

            Log::info("Backup email sent successfully.");
        } catch (\Exception $e) {
            Log::error("Failed to send email: " . $e->getMessage());
        }
    }
}
