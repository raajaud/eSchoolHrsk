<?php

namespace App\Http\Controllers\Exam;

use Throwable;
use Illuminate\Http\Request;
use App\Services\CachingService;
use App\Services\ResponseService;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Repositories\Exam\ExamInterface;
use Illuminate\Support\Facades\Validator;
use App\Repositories\ExamTimetable\ExamTimetableInterface;
use Carbon\Carbon;
use App\Imports\ExamTimetableImport;
use Maatwebsite\Excel\Facades\Excel;


class ExamTimetableController extends Controller {
    private ExamInterface $exam;
    private ExamTimetableInterface $examTimetable;
    private CachingService $cache;

    public function __construct(ExamInterface $exam, ExamTimetableInterface $examTimetable, CachingService $cache) {
        $this->exam = $exam;
        $this->examTimetable = $examTimetable;
        $this->cache = $cache;
    }

    public function edit($examId) {
        // dd('here');
        ResponseService::noFeatureThenRedirect('Exam Management');
        ResponseService::noPermissionThenRedirect('exam-timetable-list');
        $currentSessionYear = $this->cache->getDefaultSessionYear();
        $currentSemester = $this->cache->getDefaultSemesterData();
        $exam = $this->exam->builder()->where(['id' => $examId])->with(['class.medium', 'class.all_subjects' => function($query) use($currentSemester){
            (isset($currentSemester) && !empty($currentSemester)) ? $query->where('semester_id',$currentSemester->id)->orWhereNull('semester_id') : $query->orWhereNull('semester_id');
        }, 'timetable'])->firstOrFail();
        $last_result_submission_date = isset($exam->last_result_submission_date) ? date('d-m-Y', strtotime($exam->last_result_submission_date)) : '';
        $disabled = $exam->publish ? 'disabled' : '';
        return response(view('exams.timetable', compact('exam','currentSessionYear','disabled','last_result_submission_date')));
    }

    public function update(Request $request, $examID) {
        ResponseService::noFeatureThenRedirect('Exam Management');
        ResponseService::noPermissionThenSendJson('exam-timetable-create');
        $validator = Validator::make($request->all(), [
            'timetable'                 => 'required|array',
            'timetable.*.passing_marks' => 'required|lte:timetable.*.total_marks',
            'timetable.*.end_time'      => 'required|after:timetable.*.start_time',
            'timetable.*.date'          => 'required|date',
            'last_result_submission_date' => 'required|date',
        ], [
            'timetable.*.passing_marks.lte' => trans('passing_marks_should_less_than_or_equal_to_total_marks'),
            'timetable.*.end_time.after'    => trans('end_time_should_be_greater_than_start_time'),
            'last_result_submission_date.after'   => trans('the_exam_result_marks_submission_date_should_be_greater_than_last_exam_timetable_date'),
        ]);

        $validator->after(function ($validator) use ($request) {
            $timetable = $request->timetable;
            $lastResultDate = $request->last_result_submission_date;

            if (!empty($timetable) && $lastResultDate) {
                // Extract the latest date from the timetable
                $latestExamDate = collect($timetable)
                ->pluck('date')
                ->map(fn($date) => Carbon::createFromFormat('d-m-Y', $date)) // Convert to Carbon
                ->max() // Get the max date
                ->format('Y-m-d');

                $latestExamDate = Carbon::parse($latestExamDate)->format('Y-m-d');
                $lastResultDate = Carbon::parse($lastResultDate)->format('Y-m-d');

                if ($latestExamDate && $lastResultDate <= $latestExamDate) {
                    $validator->errors()->add(
                        'last_result_submission_date',
                        trans('the_exam_result_marks_submission_date_should_be_greater_than_last_exam_timetable_date')
                    );
                }
            }
        });

        if ($validator->fails()) {
            ResponseService::errorResponse($validator->errors()->first());
        }
        try {
            DB::beginTransaction();

            foreach ($request->timetable as $timetable) {
                $examTimetable = array(
                    'exam_id'           => $examID,
                    'class_subject_id'  => $timetable['class_subject_id'],
                    'total_marks'       => $timetable['total_marks'],
                    'passing_marks'     => $timetable['passing_marks'],
                    'start_time'        => $timetable['start_time'],
                    'end_time'          => $timetable['end_time'],
                    'date'              => date('Y-m-d', strtotime($timetable['date'])),
                    'session_year_id'   => $request->session_year_id,
                );
                $this->examTimetable->updateOrCreate(['id' => $timetable['id'] ?? null], $examTimetable);
            }

            // Get Start Date & End Date From Exam Timetable
            $examTimetable = $this->examTimetable->builder()->where('exam_id',$examID);
            $startDate = $examTimetable->min('date');
            $endDate = $examTimetable->max('date');
            $last_result_submission_date = date('Y-m-d', strtotime($request->last_result_submission_date));

            // Update Start Date and End Date to the particular Exam
            $exam = $this->exam->update($examID,['start_date' => $startDate,'end_date' => $endDate, 'last_result_submission_date' => $last_result_submission_date]);

            DB::commit();
            ResponseService::successResponse('Data Stored Successfully');
        } catch (Throwable $e) {
            DB::rollBack();
            ResponseService::logErrorResponse($e, "Exam Timetable Controller -> Store method");
            ResponseService::errorResponse();
        }
    }

    public function destroy($id) {
        ResponseService::noFeatureThenRedirect('Exam Management');
        ResponseService::noPermissionThenSendJson('exam-timetable-delete');
        try {
            $this->examTimetable->deleteById($id);
            ResponseService::successResponse('Data Deleted Successfully');
        } catch (Throwable $e) {
            ResponseService::logErrorResponse($e, "Exam Controller -> DeleteTimetable method");
            ResponseService::errorResponse();
        }
    }

    public function import(Request $request, $exam)
    {
        ResponseService::noFeatureThenRedirect('Exam Management');
        ResponseService::noPermissionThenSendJson('exam-timetable-create');

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
            ResponseService::errorResponse($validator->errors()->first());
        }

        try {
            DB::beginTransaction();

            // $exam = $this->exam->builder()->where(['id' => $examID])->firstOrFail();
            $currentSessionYear = $this->cache->getDefaultSessionYear();

            $examClassId = $exam->class_id;
            $examID = $exam->examID;
            // dd($examID);
            if (!$examClassId) {
                DB::rollBack();
                ResponseService::errorResponse(trans('cannot_determine_class_for_this_exam'));
            }

            // Pass the school_id from the exam to the import class
            $schoolId = $exam->school_id;

            $import = new ExamTimetableImport($examID, $schoolId, $currentSessionYear->id, $examClassId);
            // dd($examClassId);
            Excel::import($import, $request->file('file'));
            // dd($import);
            // Check for failures during import
            if ($import->failures()->isNotEmpty()) {
                $errors = collect($import->failures())->map(function ($failure) {
                    $rowNumber = $failure->row();
                    $errorMessages = implode(", ", $failure->errors());
                    return "Row " . $rowNumber . ": " . $errorMessages;
                })->implode("<br>");

                DB::rollBack();
                ResponseService::errorResponse(trans('import_failed_with_errors') . "<br>" . $errors);
            }

            // After successful import (and potential upsert), update exam's dates
            $examTimetableQuery = $this->examTimetable->builder()->where('exam_id', $examID); // Filter by class_id too
            $startDate = $examTimetableQuery->min('date');
            $endDate = $examTimetableQuery->max('date');
            $last_result_submission_date = Carbon::createFromFormat('d-m-Y', $request->last_result_submission_date)->format('Y-m-d');

            // Additional validation for last_result_submission_date against imported dates
            if ($endDate && $last_result_submission_date <= $endDate) {
                DB::rollBack();
                ResponseService::errorResponse(trans('the_exam_result_marks_submission_date_should_be_greater_than_last_exam_timetable_date'));
            }

            $this->exam->update($examID, [
                'start_date'                => $startDate,
                'end_date'                  => $endDate,
                'last_result_submission_date' => $last_result_submission_date
            ]);

            DB::commit();
            ResponseService::successResponse('Exam Timetable Imported Successfully');

        } catch (Throwable $e) {
            DB::rollBack();
            ResponseService::logErrorResponse($e, "Exam Timetable Controller -> Import method");
            ResponseService::errorResponse(trans('something_went_wrong_while_importing_exam_timetable'));
        }
    }
}
