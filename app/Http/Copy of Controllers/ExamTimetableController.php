<?php

namespace App\Http\Controllers;

use App\Http\Services\ResponseService;
use App\Repositories\Exam\ExamInterface;
use App\Repositories\ExamTimetable\ExamTimetableInterface;
use App\Repositories\SessionYear\SessionYearInterface;
use App\Repositories\Semester\SemesterInterface;
use App\Repositories\Settings\SettingsInterface;
use App\Repositories\Subject\SubjectInterface;
use App\Repositories\ClassSubject\ClassSubjectInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Throwable;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ExamTimetableImport;
use App\Models\ExamTimetable; // Keep this here if you use it directly
use App\Models\ClassSchool; // To fetch the ClassSchool model if needed
use App\Models\Subject; // If you need to access Subject model directly
use App\Repositories\SchoolSetting\SchoolSettingInterface;
use App\Services\CachingService;
use App\Services\ResponseService as ServicesResponseService;

class ExamTimetableController extends Controller
{
    protected ExamInterface $exam;
    protected ExamTimetableInterface $examTimetable;
    protected SessionYearInterface $sessionYear;
    protected SemesterInterface $semester;
    private CachingService $cache;

    protected SubjectInterface $subject;
    protected ClassSubjectInterface $classSubject;


    public function __construct(
        ExamInterface $exam,
        ExamTimetableInterface $examTimetable,
        SessionYearInterface $sessionYear,
        SemesterInterface $semester,
        CachingService $cachingService,

        SubjectInterface $subject,
        ClassSubjectInterface $classSubject
    )
    {
        $this->exam = $exam;
        $this->examTimetable = $examTimetable;
        $this->sessionYear = $sessionYear;
        $this->semester = $semester;
        $this->cache = $cachingService;

        $this->subject = $subject;
        $this->classSubject = $classSubject;
    }

    // ... your existing edit and update methods ...

    public function import(Request $request, $examID)
    {
        ServicesResponseService::noFeatureThenRedirect('Exam Management');
        ServicesResponseService::noPermissionThenSendJson('exam-timetable-create');

        $validator = Validator::make($request->all(), [
            'file'                        => 'required|file|mimes:csv,txt,xls,xlsx',
            'last_result_submission_date' => 'required|date_format:d-m-Y',
        ], [
            'file.required'                       => trans('file_is_required'),
            'file.mimes'                          => trans('file_must_be_a_csv_or_excel'),
            'last_result_submission_date.required' => trans('last_result_submission_date_is_required'),
            'last_result_submission_date.date_format' => trans('last_result_submission_date_format_is_invalid'),
        ]);

        if ($validator->fails()) {
            ServicesResponseService::errorResponse($validator->errors()->first());
        }

        try {
            DB::beginTransaction();

            $exam = $this->exam->builder()->where(['id' => $examID])->firstOrFail();
            $currentSessionYear = $this->cache->getDefaultSessionYear();

            // CRITICAL: Get the class_id from the Exam model for the import context
            // Your Exam model should have a 'class_id' or a relation to determine this.
            // Example: $exam->class_id if Exam directly has it.
            // If an exam is for multiple classes, you'd need a different approach (e.g., separate import per class or class_id in CSV).
            // Based on your ExamTimetable model having class_id, I assume an exam is linked to a specific class.
            $examClassId = $exam->class_id; // <--- **ASSUMPTION: Exam model has a 'class_id' attribute**

            if (!$examClassId) {
                DB::rollBack();
                ServicesResponseService::errorResponse(trans('cannot_determine_class_for_this_exam'));
            }

            // Pass the school_id from the exam to the import class
            $schoolId = $exam->school_id;

            $import = new ExamTimetableImport($examID, $schoolId, $currentSessionYear->id, $examClassId);
            Excel::import($import, $request->file('file'));

            // Check for failures during import
            if ($import->failures()->isNotEmpty()) {
                $errors = collect($import->failures())->map(function ($failure) {
                    $rowNumber = $failure->row();
                    $errorMessages = implode(", ", $failure->errors());
                    return "Row " . $rowNumber . ": " . $errorMessages;
                })->implode("<br>");

                DB::rollBack();
                ServicesResponseService::errorResponse(trans('import_failed_with_errors') . "<br>" . $errors);
            }

            // After successful import (and potential upsert), update exam's dates
            $examTimetableQuery = $this->examTimetable->builder()->where('exam_id', $examID)->where('class_id', $examClassId); // Filter by class_id too
            $startDate = $examTimetableQuery->min('date');
            $endDate = $examTimetableQuery->max('date');
            $last_result_submission_date = Carbon::createFromFormat('d-m-Y', $request->last_result_submission_date)->format('Y-m-d');

            // Additional validation for last_result_submission_date against imported dates
            if ($endDate && $last_result_submission_date <= $endDate) {
                DB::rollBack();
                ServicesResponseService::errorResponse(trans('the_exam_result_marks_submission_date_should_be_greater_than_last_exam_timetable_date'));
            }

            $this->exam->update($examID, [
                'start_date'                => $startDate,
                'end_date'                  => $endDate,
                'last_result_submission_date' => $last_result_submission_date
            ]);

            DB::commit();
            ServicesResponseService::successResponse('Exam Timetable Imported Successfully');

        } catch (Throwable $e) {
            DB::rollBack();
            ServicesResponseService::logErrorResponse($e, "Exam Timetable Controller -> Import method");
            ServicesResponseService::errorResponse(trans('something_went_wrong_while_importing_exam_timetable'));
        }
    }
}
