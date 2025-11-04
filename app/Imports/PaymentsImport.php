<?php

namespace App\Imports;

use App\Models\ClassSchool;
use App\Models\PaymentTransaction;
use App\Models\User;
use App\Models\UserCharge;
use App\Repositories\FormField\FormFieldsInterface;
use App\Repositories\SessionYear\SessionYearInterface;
use App\Repositories\Student\StudentInterface;
use App\Repositories\Subscription\SubscriptionInterface;
use App\Repositories\User\UserInterface;
use App\Rules\TrimmedEnum;
use App\Services\CachingService;
use App\Services\ResponseService;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use JsonException;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Str;
use Throwable;
use TypeError;

class PaymentsImport implements WithMultipleSheets
{
    private mixed $classSectionID;
    private mixed $sessionYearID;
    private mixed $is_send_notification;

    public function __construct($classSectionID, $sessionYearID, $is_send_notification)
    {
        $this->classSectionID = $classSectionID;
        $this->sessionYearID = $sessionYearID;
        $this->is_send_notification = $is_send_notification;
    }

    /**
     * @throws Throwable
     */
    public function sheets(): array
    {
        return [
            new FirstSheetImport($this->classSectionID, $this->sessionYearID, $this->is_send_notification)
        ];
    }
}

class FirstSheetImport implements ToCollection, WithHeadingRow
{
    private mixed $classSectionID;
    private mixed $sessionYearID;
    private mixed $is_send_notification;

    /**
     * @param $classSectionID
     * @param $sessionYearID
     * @param $is_send_notification
     */

    // Import the Class Section and Repositories
    public function __construct($classSectionID, $sessionYearID, $is_send_notification)
    {
        $this->classSectionID = $classSectionID;
        $this->sessionYearID = $sessionYearID;
        $this->is_send_notification = $is_send_notification;
    }

    /**
     * @throws JsonException
     * @throws Throwable
     */
    public function collection(Collection $collection)
    {

        $sessionYear = app(SessionYearInterface::class);
        $today_date = Carbon::now()->format('Y-m-d');
        $sessionYear = $sessionYear->findById($this->sessionYearID);
        DB::beginTransaction();
        foreach ($collection as $row) {

            try {
                $mobile = $row['mobile'];
                $fullName = $row['first_name'];
                $nameParts = explode(' ', $fullName);
                $last_name = array_pop($nameParts);
                $first_name = implode(' ', $nameParts);



                if($row['monthly_fees']){
                    $guardian = User::where('first_name', trim($first_name))
                        ->where('last_name', trim($last_name))
                        ->where('mobile', trim($mobile))
                        ->first();

                    if (!$guardian) {
                        continue;
                    }
                    $guardian->monthly_fees = $row['monthly_fees'] ?? 0;
                    $guardian->save();

                    $totalPayments = 0;

                    if($row['payments']){
                        $payments = explode(',', $row['payments']);
                    } else {
                        $payments = [];
                    }
                    foreach ($payments as $entry) {
                        if (strpos($entry, '|') === false) {
                            continue;
                        }

                        [$amount, $date] = explode('|', $entry);

                        if (!trim($date) || !preg_match('/\d{2}-\d{2}-\d{4}/', $date)) {
                            continue; // skip if date is empty or malformed
                        }

                        $amount = (float) $amount;
                        $totalPayments += $amount; // accumulate payment total

                        $date = Carbon::createFromFormat('d-m-Y', $date)->format('Y-m-d');
                        // dd($date);
                        PaymentTransaction::create([
                            'user_id'           => $guardian->id,
                            'amount'            => $amount,
                            'payment_gateway'   => 'cash',
                            'order_id'          => null,
                            'payment_id'        => null,
                            'payment_signature' => null,
                            'payment_status'    => 'success',
                            'school_id'         => 5,
                            'date'              => $date,
                        ]);

                        UserCharge::create([
                            'user_id'     => $guardian->id,
                            'charge_type' => 'old_payments',
                            'amount'      => $amount,
                            'description' => Carbon::parse($date)->format('F-Y'),
                            'is_paid'     => 1,
                            'charge_date' => Carbon::parse($date)->format('Y-m-d'),
                        ]);
                    }

                    // Then insert the July monthly_fees row
                    UserCharge::create([
                        'user_id'     => $guardian->id,
                        'charge_type' => 'monthly_fees',
                        'amount'      => $row['dues'] ?? 0,
                        'description' => 'October-2025',
                        'is_paid'     => 0,
                        'charge_date' => $today_date, // from loop last iteration
                    ]);
                }


            } catch (Throwable $e) {
                // IF Exception is TypeError and message contains Mail keywords then email is not sent successfully
                if ($e instanceof TypeError && Str::contains($e->getMessage(), [
                        'Mail',
                        'Mailer',
                        'MailManager'
                    ])) {
                    continue;
                }
                DB::rollBack();
                throw $e;
            }
        }
        DB::commit();
        return true;
    }
}
