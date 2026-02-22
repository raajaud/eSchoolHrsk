<?php

namespace App\Imports;

use App\Models\ClassSchool;
use App\Models\ClassSubject;
use App\Models\Exam;
use App\Models\ExamMarks;
use App\Models\ExamTimetable;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Services\ResponseService;
use App\Repositories\ExamMarks\ExamMarksInterface;
use App\Repositories\ExamTimetable\ExamTimetableInterface;

use Throwable;
use JsonException;

class MarksDataImport implements WithMultipleSheets
{
    private mixed $classSectionID;
    private mixed $examID;
    private mixed $classSubjectID;


    public function __construct($classSectionID, $examID, $classSubjectID)
    {
        $this->classSectionID = $classSectionID;
        $this->examID = $examID;
        $this->classSubjectID = $classSubjectID;
    }

    /**
     * @throws Throwable
     */
    public function sheets(): array
    {
        return [
            new FirstSheetImport($this->classSectionID, $this->examID, $this->classSubjectID)
        ];
    }
}

class FirstSheetImport implements ToCollection, WithHeadingRow
{
    private mixed $classSectionID;
    private mixed $examID;
    private mixed $classSubjectID;

    /**
     * @param $classSectionID
     * @param $examID
     */

    // Import the Class Section and Repositories
    public function __construct($classSectionID, $examID, $classSubjectID)
    {
        $this->classSectionID = $classSectionID;
        $this->examID = $examID;
        $this->classSubjectID = $classSubjectID;
    }

    /**
     * @throws JsonException
     * @throws Throwable
     */
    public function collection(Collection $collection)
    {


        // DB::beginTransaction();
        try {

            $examTimetable = app(ExamTimetableInterface::class);
            $examMarks = app(ExamMarksInterface::class);

            // Build subject + class map (e.g. ['I_Mathematics' => class_subjects.id])
            $classSubjects = DB::table('class_subjects')
                ->join('classes', 'classes.id', '=', 'class_subjects.class_id')
                ->join('subjects', 'subjects.id', '=', 'class_subjects.subject_id')
                ->select(
                    'class_subjects.id as class_subject_id',
                    'classes.name as class_name',
                    'subjects.name as subject_name'
                )
                ->get();

            $subjectMap = [];
            foreach ($classSubjects as $cs) {
                $key = trim($cs->class_name . '_' . $cs->subject_name);
                $subjectMap[$key] = $cs->class_subject_id;
            }

            $subjectMap = array_combine(
                array_map(fn($k) => str_replace(['Class ', 'Class'], '', $k), array_keys($subjectMap)),
                $subjectMap
            );

            $classMap = [
                'I' => 'Class I',
                'II' => 'Class II',
                'III' => 'Class III',
                'IV' => 'Class IV',
                'V' => 'Class V',
                'VI' => 'Class VI',
                'VII' => 'Class VII',
                'VIII' => 'Class VIII',
                'Nursery' => 'Nursery',
                'LKG' => 'LKG',
                'UKG' => 'UKG',
            ];

            // Now loop through Excel rows
            foreach ($collection as $row) {
                if (empty($row['name'])) continue;

                // Find student_id using name (case-insensitive)
                $student = User::whereRaw("LOWER(REPLACE(CONCAT(TRIM(first_name), ' ', TRIM(last_name)), ' ', '')) = ?",
                [strtolower(str_replace(' ', '', trim($row['name'])))])
                ->whereHas('student.class_section.class', function ($q) use ($row) {
                    $q->where('name', trim($row['class']));
                })
                ->with('student.class_section.class')
                ->first();
                // dd($student->student);
                if (!$student) {
                    \Log::warning("Student not found: " . $row['name']);
                    continue;
                }

                $classKeys = explode('|', $row['class']);
                $classNames = array_map(fn($key) => $classMap[trim($key)] ?? trim($key), $classKeys);

                // Get all matching class IDs
                $classIds = ClassSchool::whereIn('name', $classNames)->first();
                // dd($classIds);
                // Get exams for these classes (same exam name)
                $examByClass = Exam::where('name', $row['exam'])
                    ->where('class_id', $classIds->id)
                    ->first();
                // dd($examByClass);

                // Get all class_subjects for this subject in given classes
                $subjectNamess = [];
                $classSubjects = ClassSubject::whereIn('class_id', $classIds)->get();
                // foreach ($classSubjects as $key => $classSubject) {
                //     $subjectNamess[] = strtolower(trim($classSubject->subject->name));
                // }
                // dd($subjectNamess, $row);
                foreach ($classSubjects as $key => $classSubject) {
                    $subjectName = strtolower(str_replace(' ', '_', trim($classSubject->subject->name)));
                    // dd($classSubject);
                    // if ($subjectName == 'Social Science') {
                    //     dd('here');
                    // }
                    $marksRaw = trim($row[$subjectName] ?? '');
                    // dd($marksRaw);

                    $exam_timetable = ExamTimetable::where(['exam_id' => $examByClass->id, 'class_subject_id' => $classSubject->id])->first();
                    // dd($exam_timetable);
                    if (!$exam_timetable) {
                        \Log::warning("Timetable not found for subject: $subjectName (Class: $classNames[0])");
                        continue;
                    }
                    $admissionDate = \Carbon\Carbon::parse($student->student->admission_date);
                    $examDate = \Carbon\Carbon::parse($exam_timetable->date);

                    // dd($row['exam']);
                    $unitTestMarksTotal = 0;
                    // if($row['exam'] == 'Term I Exam'){
                    //     $marks1 = ExamMarks::with(['timetable.exam', 'subject']) // load exam name + subject
                    //         ->where('student_id', $student->id)
                    //         ->where('class_subject_id', $classSubject->id)
                    //         ->whereHas('timetable.exam', function ($q) {
                    //             $q->where('name', 'Unit Test I');
                    //         })
                    //         ->first();

                    //     $marks2 = ExamMarks::with(['timetable.exam', 'subject']) // load exam name + subject
                    //         ->where('student_id', $student->id)
                    //         ->where('class_subject_id', $classSubject->id)
                    //         ->whereHas('timetable.exam', function ($q) {
                    //             $q->where('name', 'Unit Test II');
                    //         })
                    //         ->first();
                    //     $unitTest1Marks = $marks1 ? $marks1->obtained_marks : 0;
                    //     $unitTest2Marks = $marks2 ? $marks2->obtained_marks : 0;
                    //     $unitTestMarksTotal = $unitTest1Marks + $unitTest2Marks;
                    //     // dd($unitTestMarksTotal);
                    // }
                    if ($admissionDate->gt($examDate)) {
                        $obtainedMarks = 0;
                        $status = 2;
                        $marksPercentage = 0;
                        $examGrade = null;

                        ExamMarks::updateOrCreate(
                            [
                                'exam_timetable_id' => $exam_timetable->id,
                                'student_id' => $student->id,
                                'class_subject_id' => $classSubject->id,
                                'school_id' => 5,
                            ],
                            [
                                'obtained_marks' => $obtainedMarks,
                                'passing_status' => $status,
                                'session_year_id' => $exam_timetable->session_year_id,
                                'grade' => $examGrade,
                            ]
                        );

                        continue;
                    }
                    // dd($exam_timetable);

                    $totalMarks = $exam_timetable->total_marks ?? 10;
                    $passingMarks = $exam_timetable->passing_marks ?? 7;


                    if ($marksRaw === '' || strtoupper(trim($marksRaw)) === 'AB') {
                        $obtainedMarks = 0;
                        $status = 2;

                        $marksPercentage = ($obtainedMarks >= 0 && $totalMarks > 0)
                            ? ($obtainedMarks / $totalMarks) * 100
                            : 0;

                        $examGrade = ($obtainedMarks >= 0) ? findExamGrade($marksPercentage) : null;
                    } elseif (is_numeric($marksRaw)) {
                        $obtainedMarks = floatval($marksRaw);
                        $obtainedMarks = $obtainedMarks + $unitTestMarksTotal;

                        $status = ($obtainedMarks >= $passingMarks) ? 1 : 0;
                        if ($obtainedMarks < 0) $status = 0;

                        $marksPercentage = ($obtainedMarks >= 0 && $totalMarks > 0)
                            ? ($obtainedMarks / $totalMarks) * 100
                            : 0;

                        $examGrade = ($obtainedMarks >= 0) ? findExamGrade($marksPercentage) : null;
                    } else {
                        $percentage = findPercentageFromGrade($marksRaw);
                        $obtainedMarks = round(($percentage / 100) * ($exam_timetable->total_marks ?? 10));

                        $status = ($obtainedMarks >= $passingMarks) ? 1 : 0;
                        if ($obtainedMarks < 0) $status = 0;

                        $marksPercentage = ($obtainedMarks >= 0 && $totalMarks > 0)
                            ? ($obtainedMarks / $totalMarks) * 100
                            : 0;

                        $examGrade = $marksRaw;
                        // dd($obtainedMarks);
                    }



                    // if ($marksRaw === '' || strtoupper($marksRaw) === 'AB') {
                    //     $obtainedMarks = 0;
                    //     $status = 0;
                    // } else {
                    //     $obtainedMarks = floatval($marksRaw);
                    // }
                    // // dd($marksRaw);
                    // //  dd($exam_timetable);
                    // $totalMarks = $exam_timetable->total_marks ?? 10;
                    // $passingMarks = $exam_timetable->passing_marks ?? 7;

                    // $status = ($obtainedMarks >= $passingMarks) ? 1 : 0;
                    // if ($obtainedMarks < 0) $status = 0;

                    // $marksPercentage = ($obtainedMarks >= 0 && $totalMarks > 0)
                    //     ? ($obtainedMarks / $totalMarks) * 100
                    //     : 0;

                    // $examGrade = ($obtainedMarks >= 0) ? findExamGrade($marksPercentage) : null;
                    // dd([
                    //         'exam_timetable_id' => $exam_timetable->id,
                    //         'student_id' => $student->id,
                    //         'class_subject_id' => $classSubject->id,
                    //     ]);
                    //     dd([
                    //         'obtained_marks' => $obtainedMarks,
                    //         'passing_status' => $status,
                    //         'session_year_id' => $exam_timetable->session_year_id,
                    //         'grade' => $examGrade,
                    //     ]);
                    $res = ExamMarks::updateOrCreate(
                        [
                            'exam_timetable_id' => $exam_timetable->id,
                            'student_id' => $student->id,
                            'class_subject_id' => $classSubject->id,
                            'school_id' => 5,
                        ],
                        [
                            'obtained_marks' => $obtainedMarks,
                            'passing_status' => $status,
                            'session_year_id' => $exam_timetable->session_year_id,
                            'grade' => $examGrade,
                        ]
                    );
                    // dd($res);
                }
            }

            // DB::commit();
            return true;
        } catch (Throwable $e) {
            // DB::rollBack();
            throw $e;
        }
    }
    // public function collection(Collection $collection)
    // {
    //     // Validate incoming CSV data
    //     $validator = Validator::make($collection->toArray(), [
    //         '*.student_id' => 'required|numeric',
    //         '*.obtained_marks' => 'required|numeric',
    //         '*.total_marks' => 'required|numeric',
    //     ], [
    //         'student_id.required' => 'Student ID field is required.',
    //         'obtained_marks.required' => 'Obtained Marks field is required.',
    //         'total_marks.required' => 'Total Marks field is required.',
    //     ]);

    //     $validator->validate();

    //     DB::beginTransaction();
    //     try {

    //         $examTimetable = app(ExamTimetableInterface::class);
    //         $examMarks = app(ExamMarksInterface::class);
    //         // dd($examMarks);
    //         $exam_timetable = $examTimetable->builder()->where(['exam_id' =>  $this->examID, 'class_subject_id' => $this->classSubjectID])->firstOrFail();
    //         foreach ($collection as $row) {

    //             $passing_marks = $exam_timetable->passing_marks;
    //             if ($row['obtained_marks'] >= $passing_marks) {
    //                 $status = 1;
    //             } else {
    //                 $status = 0;
    //             }
    //             $marks_percentage = ($row['obtained_marks'] / $row['total_marks']) * 100;
    //             $exam_grade = findExamGrade($marks_percentage);

    //             if ($exam_grade == null) {
    //                 ResponseService::errorResponse('Grades data does not exists');
    //             }

    //             $examMarks->updateOrCreate([
    //                 'id' => $row['exam_marks_id'] ?? null],
    //                 ['exam_timetable_id' => $exam_timetable->id,
    //                 'student_id' => $row['student_id'],
    //                 'class_subject_id' => $this->classSubjectID,
    //                 'obtained_marks' => $row['obtained_marks'],
    //                 'passing_status' => $status,
    //                 'session_year_id' => $exam_timetable->session_year_id,
    //                 'grade' => $exam_grade,]);
    //         }

    //         DB::commit();
    //         return true;
    //     } catch (Throwable $e) {
    //         DB::rollBack();
    //         throw $e;
    //     }
    // }
}
