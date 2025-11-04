<?php

namespace App\Imports;

use App\Models\ExamTimetable;
use App\Models\Subject;
use App\Models\ClassSubject;
use App\Models\SessionYear; // Keep this if you need to fetch it in model function
use App\Models\ClassSchool; // Add this if you need to fetch class details
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection; // Use ToCollection to handle multiple rows at once
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

// Implementing ToCollection instead of ToModel for more flexible batch processing
class ExamTimetableImport implements ToCollection, WithHeadingRow, WithValidation, SkipsOnFailure, WithBatchInserts, WithChunkReading
{
    use SkipsFailures;

    protected $examId;
    protected $schoolId;
    protected $sessionYearId;
    protected $classIdForExam; // The specific class this exam is for, derived from the Exam model
    protected $processedRows = []; // To store data for the final update/insert outside the loop

    // Cache subject IDs and ClassSubject IDs to reduce DB queries
    protected $subjectCache = [];
    protected $classSubjectCache = [];

    public function __construct($examId, $schoolId, $sessionYearId, $classIdForExam)
    {
        $this->examId = $examId;
        $this->schoolId = $schoolId;
        $this->sessionYearId = $sessionYearId;
        $this->classIdForExam = $classIdForExam; // This is the class associated with the Exam itself
    }

    /**
     * Process each row in the collection.
     * @param Collection $collection
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $validator = Validator::make($row->toArray(), $this->rules(), $this->customValidationMessages());

            if ($validator->fails()) {
                foreach ($validator->errors()->messages() as $attribute => $errors) {
                    $this->onFailure(new \Maatwebsite\Excel\Validators\Failure(
                        $index + 1,          // Row number
                        $attribute,          // Field name causing the error
                        $errors,             // Array of messages for that field
                        $row->toArray()      // Row data
                    ));
                }
                continue;
            }

            $subjectName = $row['subject_name'];
            if (!isset($this->subjectCache[$subjectName])) {
                $subject = Subject::where('name', $subjectName)->first();
                if (!$subject) {
                    $this->onFailure(new \Maatwebsite\Excel\Validators\Failure(
                        $index + 1,
                        'subject_name',
                        ["Subject '$subjectName' is not associated with the exam's class (ID: {$this->classIdForExam})."],
                        $row->toArray()
                    ));
                    continue;
                }
                $this->subjectCache[$subjectName] = $subject->id;
            }
            $subjectId = $this->subjectCache[$subjectName];

            $classSubjectKey = $this->classIdForExam . '-' . $subjectId;
            if (!isset($this->classSubjectCache[$classSubjectKey])) {
                $classSubject = ClassSubject::where('class_id', $this->classIdForExam)
                                            ->where('subject_id', $subjectId)
                                            ->first();
                if (!$classSubject) {
                    $this->onFailure(new \Maatwebsite\Excel\Validators\Failure(
                        $index + 1,
                        $row->toArray(),
                        ["Subject '$subjectName' is not associated with the exam's class (ID: {$this->classIdForExam})."]
                    ));
                    continue;
                }
                $this->classSubjectCache[$classSubjectKey] = $classSubject->id;
            }
            $classSubjectId = $this->classSubjectCache[$classSubjectKey];

            $date = Carbon::createFromFormat('d-m-Y', $row['date'])->format('Y-m-d');
            $startTime = Carbon::createFromFormat('h:i A', $row['start_time'])->format('H:i:s');
            $endTime = Carbon::createFromFormat('h:i A', $row['end_time'])->format('H:i:s');

            $this->processedRows[] = [
                'exam_id'          => $this->examId,
                // 'class_id'         => $this->classIdForExam,
                'class_subject_id' => $classSubjectId,
                'total_marks'      => $row['full_mark'],
                'passing_marks'    => $row['pass_marks'],
                'start_time'       => $startTime,
                'end_time'         => $endTime,
                'date'             => $date,
                'session_year_id'  => $this->sessionYearId,
                'school_id'        => $this->schoolId,
                'created_at'       => now(),
                'updated_at'       => now(),
            ];
        }

        if (!empty($this->processedRows)) {
            $uniqueBy = ['exam_id', 'class_id', 'class_subject_id', 'date'];
            $updateColumns = ['total_marks', 'passing_marks', 'start_time', 'end_time', 'session_year_id', 'school_id', 'updated_at'];
            ExamTimetable::upsert($this->processedRows, $uniqueBy, $updateColumns);
        }

        $this->processedRows = [];
    }

    public function rules(): array
    {
        return [
            'date'          => ['required', 'date_format:d-m-Y'],
            'start_time'    => ['required', 'date_format:h:i A'],
            'end_time'      => ['required', 'date_format:h:i A', 'after:start_time'],
            'subject_name'  => ['required', 'string'], // Custom validation for subject existence handled in collection method
            // 'full_mark'     => ['required', 'numeric', 'min:1'],
            // 'pass_marks'    => ['required', 'numeric', 'min:0', 'lte:full_mark'],
        ];
    }

    public function customValidationMessages()
    {
        return [
            'date.required'           => trans('date_is_required'),
            'date.date_format'        => trans('date_format_is_invalid'),
            'start_time.required'     => trans('start_time_is_required'),
            'start_time.date_format'  => trans('start_time_format_is_invalid'),
            'end_time.required'       => trans('end_time_is_required'),
            'end_time.date_format'    => trans('end_time_format_is_invalid'),
            'end_time.after'          => trans('end_time_should_be_greater_than_start_time'),
            'subject_name.required'   => trans('subject_name_is_required'),
            'subject_name.string'     => trans('subject_name_must_be_a_string'),
            'full_mark.required'      => trans('full_mark_is_required'),
            'full_mark.numeric'       => trans('full_mark_must_be_a_number'),
            'full_mark.min'           => trans('full_mark_must_be_at_least_1'),
            'pass_marks.required'     => trans('passing_marks_is_required'),
            'pass_marks.numeric'      => trans('passing_marks_must_be_a_number'),
            'pass_marks.min'          => trans('passing_marks_cannot_be_negative'),
            'pass_marks.lte'          => trans('passing_marks_should_less_than_or_equal_to_total_marks'),
        ];
    }

    public function batchSize(): int
    {
        return 500; // Increased batch size for upsert
    }

    public function chunkSize(): int
    {
        return 500; // Read 500 rows into memory at a time
    }
}
