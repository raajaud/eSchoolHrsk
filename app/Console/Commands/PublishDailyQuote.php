<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Quote;
use App\Models\Holiday;
use App\Models\Announcement;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class PublishDailyQuote extends Command
{
    protected $signature = 'quote:publish';
    protected $description = 'Publishes a random, unrepeated quote every weekday at 9 AM, excluding holidays.';

    public function handle()
    {
        $today = Carbon::today();

        // 1. Check if today is a Sunday
        if ($today->isSunday()) {
            $this->info('Today is Sunday. No quote will be published.');
            return;
        }

        // 2. Check if today is a holiday
        DB::setDefaultConnection('school');
        Config::set('database.connections.school.database', 'eschool_saas_5_asif');
        DB::purge('school');
        DB::connection('school')->reconnect();
        DB::setDefaultConnection('school');
        $isHoliday = Holiday::where('date', $today->toDateString())->exists();
        if ($isHoliday) {
            $this->info('Today is a holiday. No quote will be published.');
            return;
        }

        // 3. Get an unpublished random quote
        $quote = Quote::where('published', false)->inRandomOrder()->first();

        if ($quote) {
            // Your existing logic to publish the announcement
            DB::transaction(function () use ($quote) {
                $announcement = Announcement::create([
                    'title' => 'Thought of the Day',
                    'description' => $quote->quote . ' - ' . $quote->author,
                    'session_year_id' => 4, // Replace with dynamic value if needed
                    'school_id' => 5, // Replace with dynamic value if needed
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Assuming you want to publish to specific class_sections
                // Replace with your actual logic for class_section_ids
                $classSectionIds = [8, 9, 10, 11, 12, 13, 14, 15, 16];
                foreach ($classSectionIds as $classSectionId) {
                    DB::table('announcement_classes')->insert([
                        'announcement_id' => $announcement->id,
                        'class_section_id' => $classSectionId,
                        'class_subject_id' => null, // Or your dynamic value
                        'school_id' => 5, // Replace with dynamic value if needed
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // Mark the quote as published
                $quote->update(['published' => true]);
            });

            $this->info("Quote published successfully: \"{$quote->quote}\"");
        } else {
            // If all quotes are published, you might want to reset them
            Quote::query()->update(['published' => false]);
            $this->warn('All quotes have been published. Resetting published status for all quotes.');
            // Then try to get a new quote again for today
            $this->handle(); // Re-run the command to pick a new quote
        }
    }
}
