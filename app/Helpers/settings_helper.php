<?php

use App\Models\Grade;
use App\Models\PaymentTransaction;
use App\Repositories\Grades\GradesInterface;
use App\Repositories\SchoolSetting\SchoolSettingInterface;
use App\Services\CachingService;
use Intervention\Image\Facades\Image;
use App\Models\ClassSection;
use App\Models\Subject;
use App\Models\ClassSubject;
use App\Models\SubjectTeacher;
use App\Models\User;
use Illuminate\Support\Facades\DB;

//function getSystemSettings($name = '') {
//    $systemSettingsRepository = app(SystemSettingInterface::class);
//
//    $settingList = array();
//    if ($name == '') {
//        $settings = $systemSettingsRepository->all();
//        foreach ($settings as $row) {
//            $settingList[$row->name] = $row->data;
//        }
//        return $settingList;
//    }
//
//    $settings = $systemSettingsRepository->getSpecificData($name);
//    return $settings ?? null;
//}

function getSchoolSettings($name = '') {
    $schoolSettingsRepository = app(SchoolSettingInterface::class);

    $settingList = array();
    if ($name == '') {
        $settings = $schoolSettingsRepository->all();
        foreach ($settings as $row) {
            $settingList[$row->name] = $row->data;
        }
        return $settingList;
    }

    $settings = $schoolSettingsRepository->getSpecificData($name);
    return $settings ?? null;
}


function getTimeFormat() {
    $timeFormat = array();
    $timeFormat['h:i a'] = 'h:i a - ' . date('h:i a');
    $timeFormat['h:i A'] = 'h:i A - ' . date('h:i A');
    $timeFormat['H:i'] = 'H:i - ' . date('H:i');
    return $timeFormat;
}

function getDateFormat() {
    $dateFormat = array();
    $dateFormat['d/m/Y'] = 'd/m/Y - ' . date('d/m/Y');
    $dateFormat['m/d/Y'] = 'm/d/Y - ' . date('m/d/Y');
    $dateFormat['Y/m/d'] = 'Y/m/d - ' . date('Y/m/d');
    $dateFormat['Y/d/m'] = 'Y/d/m - ' . date('Y/d/m');
    $dateFormat['m-d-Y'] = 'm-d-Y - ' . date('m-d-Y');
    $dateFormat['d-m-Y'] = 'd-m-Y - ' . date('d-m-Y');
    $dateFormat['Y-m-d'] = 'Y-m-d - ' . date('Y-m-d');
    $dateFormat['Y-d-m'] = 'Y-d-m - ' . date('Y-d-m');
    // $dateFormat['F j, Y'] = 'F j, Y - ' . date('F j, Y');
    // $dateFormat['jS F Y'] = 'jS F Y - ' . date('jS F Y');
    // $dateFormat['l jS F'] = 'l jS F - ' . date('l jS F');
    // $dateFormat['d M, y'] = 'd M, y - ' . date('d M, y');
    return $dateFormat;
}

function getTimezoneList() {
    static $timezones = null;

    if ($timezones === null) {
        $list = DateTimeZone::listAbbreviations();
        $idents = DateTimeZone::listIdentifiers();

        $data = $offset = $added = array();
        foreach ($list as $info) {
            foreach ($info as $zone) {
                if (!empty($zone['timezone_id']) && !in_array($zone['timezone_id'], $added) && in_array($zone['timezone_id'], $idents)) {
                    $z = new DateTimeZone($zone['timezone_id']);
                    $c = new DateTime('', $z);
                    $zone['time'] = $c->format('H:i a');
                    $offset[] = $zone['offset'] = $z->getOffset($c);
                    $data[] = $zone;
                    $added[] = $zone['timezone_id'];
                }
            }
        }

        array_multisort($offset, SORT_ASC, $data);
        $i = 0;
        $temp = array();
        foreach ($data as $row) {
            $temp[0] = $row['time'];
            $temp[1] = formatOffset($row['offset']);
            $temp[2] = $row['timezone_id'];
            $timezones[$i++] = $temp;
        }
    }
    return $timezones;
}

function formatOffset($offset) {
    $hours = $offset / 3600;
    $remainder = $offset % 3600;
    $sign = $hours > 0 ? '+' : '-';
    $hour = (int)abs($hours);
    $minutes = (int)abs($remainder / 60);

    if ($hour == 0 && $minutes == 0) {
        $sign = ' ';
    }
    return $sign . str_pad($hour, 2, '0', STR_PAD_LEFT) . ':' . str_pad($minutes, 2, '0');
}

function flattenMyModel($model) {
    $modelArr = $model->toArray();
    $data = [];
    array_walk_recursive($modelArr, static function ($item, $key) use (&$data) {
        $data[$key] = $item;
    });
    return $data;
}

function changeEnv($data = array()) {
    if (count($data) > 0) {

        // Read .env-file
        $env = file_get_contents(base_path() . '/.env');
        // Split string on every " " and write into array
        $env = explode(PHP_EOL, $env);
        // $env = preg_split('/\s+/', $env);
        $temp_env_keys= [];
        foreach ($env as $env_value) {
            $entry = explode("=", $env_value);
            $temp_env_keys[] = $entry[0];

        }
        // Loop through given data
        foreach ((array)$data as $key => $value) {
            $key_value = $key . "=" . $value;

            if (in_array($key, $temp_env_keys)) {
                // Loop through .env-data
                foreach ($env as $env_key => $env_value) {
                    // Turn the value into an array and stop after the first split
                    // So it's not possible to split e.g. the App-Key by accident
                    $entry = explode("=", $env_value);
                    // // Check, if new key fits the actual .env-key
                    if ($entry[0] == $key) {

                        // If yes, overwrite it with the new one

//                        if ($key != 'APP_NAME') {
//                            $env[$env_key] = $key . "=" . str_replace('"', '', $value);
//                        } else {
                            $env[$env_key] = $key . "=\"" . $value."\"";
//                        }

                    } else {
                        // If not, keep the old one
                        $env[$env_key] = $env_value;
                    }
                }
            } else {
                $env[] = $key_value;
            }
        }
        // Turn the array back to a String
        $env = implode("\n", $env);

        // And overwrite the .env with the new data
        file_put_contents(base_path() . '/.env', $env);

        return true;
    }

    return false;
}

function findExamGrade($percentage) {
    // $grades = Grade::Owner()->get();
    $grades = app(GradesInterface::class)->builder()->get();
    if (count($grades)) {
        foreach ($grades as $row) {
            if (round($percentage,2) >= floor($row['starting_range']) && round($percentage,2) <= floor($row['ending_range'])) {
                return $row->grade;
            }
        }
    }
    return '';
}

function resizeImage($image) {
    Image::make($image)->save(null, 50);
}

function sessionYearWiseMonth()
{
    $monthArray = array( '1' => __('January'), '2' => __('February'), '3' => __('March'), '4' => __('April'), '5' => __('May'), '6' => __('June'), '7' => __('July'), '8' => __('August'), '9' => __('September'), '10' => __('October'), '11' => __('November'), '12' => __('December') );
    $currentSessionYear = app(CachingService::class)->getDefaultSessionYear();
    $startingMonth = date('m',strtotime($currentSessionYear->start_date));
    $months = array();
    for ($i = $startingMonth - 1; $i < $startingMonth + count($monthArray); $i++) {
        $index = $i % count($monthArray) + 1;
        $months[$index] = $monthArray[$index];
    }
    return $months;
}

function format_date($date)
{
    if (Auth::user()) {
        if (Auth::user()->school_id) {
            $setting = app(CachingService::class)->getSchoolSettings();
            return date($setting['date_format'] ?? 'Y-m-d',strtotime($date));
        } else {
            $setting = app(CachingService::class)->getSystemSettings();
            return date($setting['date_format'] ?? 'Y-m-d',strtotime($date));
        }
    }
    return $date;
}
function http_url($path) {
    return 'http://' . request()->getHost() . '/' . ltrim($path, '/');
}

function getCategoryStartOverrides($categoryUsagePeriod)
{
    $categoryStarts = [];

    if ($categoryUsagePeriod) {
        $decoded = json_decode($categoryUsagePeriod, true);
        // dd($decoded);
        if (isset($decoded['category_usage']) && is_array($decoded['category_usage'])) {
            foreach ($decoded['category_usage'] as $usage) {
                $cat = strtolower(trim($usage['category']));
                $start = isset($usage['start'])?\Carbon\Carbon::parse($usage['start']):$usage['discount'];

            //    dd($usage);
                $endRaw = $usage['end'] ?? null;
                $end = (!empty($endRaw)) ? \Carbon\Carbon::parse($endRaw) : \Carbon\Carbon::now();

                $categoryStarts[$cat][] = [
                    'start' => $start,
                    'end' => $end,
                ];
                // dd($categoryStarts);
            }
        }
    }
    // dd($categoryStarts);
    return $categoryStarts;
}

function getCategoryAdjustedFee($row)
{
    // dd($row->student->class_section_id);
    $guardian = User::where('id', $row->student->guardian_id)->first();
    // dd($guardian);
    $breakup = [];
    $total = (int) $guardian->total_fees;
    // dd($row);
    $month = $guardian->due_month;
    return [
        'paid' => $guardian->total_paid ?? 0,
        'total' => $total,
        'breakup' => $breakup,
        'month' => $month,
    ];

}


function totalPaid($row)
{
    return PaymentTransaction::where('user_id', $row->id)->sum('amount');
}



if (!function_exists('assignTeacherToAllSubjects')) {
    function assignTeacherToAllSubjects($teacher_id, $school_id)
    {
        $classes = ClassSection::where('school_id', $school_id)->pluck('id');
        $subjects = Subject::where('school_id', $school_id)->pluck('id');

        foreach ($classes as $class_id) {
            foreach ($subjects as $subject_id) {
                // Check if already assigned
                $exists = SubjectTeacher::where([
                    'class_section_id' => $class_id,
                    'subject_id' => $subject_id,
                    'teacher_id' => $teacher_id,
                    'school_id' => $school_id,
                ])->exists();

                if (!$exists) {
                    $clSubject = ClassSubject::where('class_id', $class_id)
                        ->where('subject_id', $subject_id)
                        ->first();
                        // dd($clSubject);
                        if($clSubject){
                            SubjectTeacher::create([
                                'class_section_id' => $class_id,
                                'subject_id' => $subject_id,
                                'teacher_id' => $teacher_id,
                                'user_id' => auth()->id() ?? 1,
                                'class_subject_id' => $clSubject ? $clSubject->id : null,
                                'school_id' => $school_id,
                            ]);
                        }
                }
            }
        }

        return true;
    }
}
