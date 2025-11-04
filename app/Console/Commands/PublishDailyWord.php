<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DailyWord;
use App\Models\Holiday;
use App\Models\Announcement;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class PublishDailyWord extends Command
{
    protected $signature = 'word:publish';
    protected $description = 'Publishes a random, unrepeated Word of the Day every weekday at 9 AM, excluding holidays.';

    public function handle()
    {
        $today = Carbon::today();

        // 1. Skip Sunday
        if ($today->isSunday()) {
            $this->info('Today is Sunday. No word will be published.');
            return;
        }

        // 2. Connect to school DB and check for holiday
        DB::setDefaultConnection('school');
        Config::set('database.connections.school.database', 'eschool_saas_5_asif');
        DB::purge('school');
        DB::connection('school')->reconnect();
        DB::setDefaultConnection('school');

        $isHoliday = Holiday::where('date', $today->toDateString())->exists();
        if ($isHoliday) {
            $this->info('Today is a holiday. No word will be published.');
            return;
        }

        // 3. Pick a random unpublished word (no publish_date yet)
        $word = DailyWord::whereNull('publish_date')->inRandomOrder()->first();

        if ($word) {
            DB::transaction(function () use ($word, $today) {
                // Create announcement
                $announcement = Announcement::create([
                    'title' => 'Word of the Day',
                    'description' =>
                        '<b>' . $word->english_word . '</b> (' . $word->pronunciation . ')<br>' .
                        'Hindi: ' . $word->hindi_word . '<br>' .
                        'Meaning: ' . $word->hindi_meaning,
                    'session_year_id' => 4, // dynamic if needed
                    'school_id' => 5,       // dynamic if needed
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Assign to class sections
                $classSectionIds = [8, 9, 10, 11, 12, 13, 14, 15, 16];
                foreach ($classSectionIds as $classSectionId) {
                    DB::table('announcement_classes')->insert([
                        'announcement_id' => $announcement->id,
                        'class_section_id' => $classSectionId,
                        'class_subject_id' => null,
                        'school_id' => 5,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // Mark word as published (set date)
                $word->update(['publish_date' => $today->toDateString()]);
            });

            $this->info("Word published successfully: \"{$word->english_word}\"");
        } else {
            // All words published — reset
            DailyWord::query()->update(['publish_date' => null]);
            $this->warn('All words have been published. Resetting publish_date for all.');
            $this->handle(); // Re-run
        }
    }
}
