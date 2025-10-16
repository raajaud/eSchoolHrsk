<?php

namespace App\Imports;

use App\Models\ClassSchool;
use App\Models\PaymentTransaction;
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

class StudentsImport implements WithMultipleSheets
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
        $student = app(StudentInterface::class);
        $formFields = app(FormFieldsInterface::class);
        $sessionYear = app(SessionYearInterface::class);

        $subscription = app(SubscriptionInterface::class);
        $user = app(UserInterface::class);
        $cache = app(CachingService::class);

        $validator = Validator::make($collection->toArray(), [
            '*.first_name'     => 'required',
            // '*.last_name'      => 'required',
            '*.mobile'         => 'nullable|regex:/^([0-9\s\-\+\(\)]*)$/',
            '*.gender'         => ['required', new TrimmedEnum(['male', 'female', 'Male', 'Female'])],
            // '*.dob'            => 'required|date',
            '*.admission_date' => 'required|date',
            '*.current_address'      => 'required',
            // '*.permanent_address'      => 'required',
            // '*.guardian_email'      => 'required|email',
            // '*.guardian_first_name' => 'required',
            // '*.guardian_last_name'  => 'required',
            '*.guardian_mobile'     => 'required|regex:/^([0-9\s\-\+\(\)]*)$/',
        ],[
            '*.first_name.required' => 'Please enter the first name.',
            '*.last_name.required' => 'Please enter the last name.',
            '*.mobile.required' => 'Please enter the mobile number.',
            '*.gender.required' => 'Please select the gender.',
            // '*.dob.date' => 'Please ensure that the dob date format you use is either DD-MM-YYYY or MM/DD/YYYY.',
            '*.admission_date.date' => 'Please ensure that the admission date format you use is either DD-MM-YYYY or MM/DD/YYYY.',
            // '*.guardian_email.required' => 'Please enter the guardian email.',
            // '*.guardian_email.email' => 'Please enter a valid email address.',
            // '*.guardian_first_name.required' => 'Please enter the guardian first name.',
            // '*.guardian_last_name.required' => 'Please enter the guardian last name.',
            '*.guardian_mobile.required' => 'Please enter the guardian mobile number.',

        ]);

        //             If Validation fails then this will throw the ValidationFail Exception
        $validator->validate();

        // Check free trial package
        $today_date = Carbon::now()->format('Y-m-d');
        $get_subscription = $subscription->builder()->doesntHave('subscription_bill')->whereDate('start_date','<=',$today_date)->where('end_date','>=',$today_date)->whereHas('package',function($q){
            $q->where('is_trial',1);
        })->first();

        $userService = app(UserService::class);
        $sessionYear = $sessionYear->findById($this->sessionYearID);
        DB::beginTransaction();
        foreach ($collection as $row) {
            // dd($row);
            // Check free trial package

            // if ($get_subscription) {
            //     $systemSettings = $cache->getSystemSettings();
            //     $count_student = $user->builder()->role('Student')->withTrashed()->count();
            //     if ($count_student >= $systemSettings['student_limit']) {
            //         $message = "The free trial allows only ".$systemSettings['student_limit']." students.";
            //         ResponseService::errorResponse($message);
            //         break;
            //     }
            // }
            $row = $row->toArray();
            // Find the index of the key after which to split the array
            $splitIndex = array_search('guardian_mobile', array_keys($row)) + 1;

            // Get The Extra Details of it
            $extraDetailsFields = array_slice($row, $splitIndex);
            // Get the Session year ID
            // $sessionYear = $sessionYear->findById($this->sessionYearID);

            $fullName = $row['guardian_first_name'];
            $nameParts = explode(' ', $fullName);
            $first_names = array_pop($nameParts);
            $last_names = implode(' ', $nameParts);

            $guardian = $userService->createOrUpdateParent($last_names, $first_names, $row['guardian_email'], $row['guardian_mobile'], $row['guardian_gender']);
            // dd($guardian);
            $get_student = $student->builder()->where('session_year_id', $sessionYear->id)->select('id')->latest('id')->pluck('id')->first();
            $admission_no = $sessionYear->name .'0'.  Auth::user()->school_id .'0'. ($get_student + 1);
            $extraDetails = array();
            // Check that Extra Details Exists
            if (!empty($extraDetailsFields)) {
                $extraFieldName = array_map(static function ($d) {
                    return str_replace("_", " ", $d);
                }, array_keys($extraDetailsFields));
                $formFieldsCollection = $formFields->builder()->whereIn('name', $extraFieldName)->get();
                $extraFieldValidationRules = [];
                foreach ($formFieldsCollection as $field) {
                    if ($field->is_required) {
                        $name = strtolower(str_replace(' ', '_', $field->name));
                        $extraFieldValidationRules[$name] = 'required';
                    }
                }
                $extraFieldValidator = Validator::make($row, $extraFieldValidationRules);
                $extraFieldValidator->validate();


                // Create Extra Details Array for Student's Extra Form Details
                foreach ($extraDetailsFields as $key => $value) {
                    $formField = $formFieldsCollection->first(function ($data) use ($key) {
                        return strtolower($data->name) === str_replace("_", " ", $key);
                    });

                    if (!empty($formField)) {

                        // if Form Field is checkbox then make data in json format
                        $data = $formField->type == 'checkbox' ? explode(',', $value) : $value;
                        $extraDetails[] = array(
                            'input_type'    => $formField->type,
                            'form_field_id' => $formField->id,
                            'data'          => (is_array($data)) ? json_encode($data, JSON_THROW_ON_ERROR) : $data
                        );
                    }
                }
                //                     Make File Input Array to Store the Null Values
                $getFileExtraField = $formFields->builder()->where('type', 'file')->get();
                foreach ($getFileExtraField as $value) {
                    $extraDetails[] = array(
                        'input_type'    => 'file',
                        'form_field_id' => $value->id,
                        'data'          => NULL,
                    );
                }
            }
            //                $userService->createOrUpdateStudentUser($row['first_name'], $row['last_name'], $admission_no, $row['mobile'], $row['dob'], $row['gender'], null, $this->class_section_id, now(), $extraDetails, null, $guardian->id);
            $class = ClassSchool::where('name', $row['class'])->first();
            if($class){
                $classSectionID = $class->class_sections->first()->id;
            }else{
                $classSectionID = 0;
            }
            try {
                $fullName = $row['first_name'];
                $nameParts = explode(' ', $fullName);
                $last_name = array_pop($nameParts);
                $first_name = implode(' ', $nameParts);

                $user = $userService->createStudentUser($first_name, $last_name, $admission_no, $row['mobile'], $row['dob']??'', $row['gender'], null, $classSectionID, $row['admission_date'],$row['current_address'],$row['permanent_address'], $sessionYear->id, $guardian->id, $extraDetails, 1, $this->is_send_notification, $row['monthly_fees']);

                $payments = explode(',', $row['payments']);
                if (!$guardian) {
                    continue;
                }

                $totalPayments = 0;

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

                // Insert total payments as a UserCharge before July
                // UserCharge::create([
                //     'user_id'     => $guardian->id,
                //     'charge_type' => 'payments_made',
                //     'amount'      => $totalPayments,
                //     'description' => 'Payments Made Before July-2025',
                //     'is_paid'     => 1, // since it's already paid
                //     'charge_date' => Carbon::parse('2025-06-30')->format('Y-m-d'),
                // ]);

                // Then insert the July monthly_fees row
                UserCharge::create([
                    'user_id'     => $guardian->id,
                    'charge_type' => 'monthly_fees',
                    'amount'      => $row['dues'] ?? 0,
                    'description' => 'July-2025',
                    'is_paid'     => 0,
                    'charge_date' => $date, // from loop last iteration
                ]);


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
