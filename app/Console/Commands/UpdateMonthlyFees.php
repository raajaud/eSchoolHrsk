<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\UserCharge;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Config;

class UpdateMonthlyFees extends Command
{
    protected $signature = 'fees:update-monthly';
    protected $description = 'Add monthly fee entries for all guardians on the 1st of every month';

    public function handle()
    {
        DB::setDefaultConnection('school');
        Config::set('database.connections.school.database', 'eschool_saas_5_asif');
        DB::purge('school');
        DB::connection('school')->reconnect();
        DB::setDefaultConnection('school');

        $today = Carbon::now();
        $monthYear = $today->format('F-Y');
        $todayDate = $today->format('Y-m-d');

        $guardians = User::whereHas('roles', fn($q) => $q->where('name', 'Guardian'))
            ->whereHas('child')
            ->where('status', 1)
            ->whereNotNull('monthly_fees')
            ->get();

        if ($guardians->isEmpty()) {
            $this->info('No guardians found.');
            return;
        }

        DB::beginTransaction();
        try {
            foreach ($guardians as $guardian) {
                $dues = $guardian->monthly_fees ?? 0;

                // prevent duplicate entry for the same month
                $exists = UserCharge::where('user_id', $guardian->id)
                    ->where('charge_type', 'monthly_fees')
                    ->where('description', 'like', "%{$monthYear}%")
                    ->exists();

                if (!$exists) {
                    UserCharge::create([
                        'user_id'     => $guardian->id,
                        'charge_type' => 'monthly_fees',
                        'amount'      => $dues,
                        'description' => $monthYear,
                        'is_paid'     => 0,
                        'charge_date' => $todayDate,
                    ]);
                }
            }

            DB::commit();
            $this->info('Monthly fees successfully updated for all guardians.');
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('UpdateMonthlyFees error: ' . $e->getMessage());
            $this->error('An error occurred while updating monthly fees.');
        }
    }
}
