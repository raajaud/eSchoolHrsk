<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Result</title>

    <style>
        body {
            border: 1px solid black;
            border-radius: 4px;
            padding: 10px;
        }

        table {
            border-collapse: collapse;
            border: none;
            font-size: 12px;
            z-index: 1;
        }

        .header tr td {
            padding: 5px;
        }

        .school-name {
            font-size: 22px;
            font-weight: bold;
        }

        .full-width {
            width: 100%;
        }

        .cell-pedding tr th,
        tr td {
            padding: 5px;
            font-size: 12px;

        }

        .table-gap {
            margin-top: 20px;
        }
        .text-left {
            text-align: left;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .student-info table, .student-info table tr {
            border: 1px solid lightgray;
        }
        .table-result table, .table-result table tr, .table-result table tr th, .table-result table tr td {
            border: 1px solid lightgray;
        }
        .footer {
            position: absolute;
            bottom: 20px;
            right: 20px;
            left: 20px;
        }
    </style>
</head>

<body>
    <div class="body">
        <table class="full-width header">
            <tr>
                <td style="width: 75%">
                    <span class="school-name">{{ $settings['school_name'] }}</span>
                    <br>
                    {{ $settings['school_address'] }}
                    <br>
                    {{ $settings['school_email'] }}
                    <br>
                    {{ $settings['school_phone'] }}
                </td>

                <td style="text-align: center">
                    <img height="100" src="{{ public_path('assets/school/images/hrskblue.png') }}" alt="">
                    {{-- @if ($settings['horizontal_logo'] ?? '')
                        <img height="50" src="{{ public_path('storage/') . $settings['horizontal_logo'] }}" alt="">
                    @else
                        <img height="50" src="{{ public_path('assets/horizontal-logo2.svg') }}" alt="">
                    @endif --}}
                </td>
            </tr>
        </table>
        <hr>

        <div class="student-info table-gap">
            <table class="full-width student-info-table cell-pedding">
                <tr>
                    <td colspan="6" style="text-align: center; font-size: 16px;">
                        {{ $result->exam->name }} <br>
                        Session Year: {{ $result->session_year->name }}
                    </td>
                </tr>
                <tr>
                    <th colspan="6" style="font-size: 16px;letter-spacing: 2px">Student Information</th>
                </tr>
                <tr>
                    <th class="text-left">Student Name :</th>
                    <td>{{ $result->user->full_name }}</td>
                    <th class="text-left">DOB :</th>
                    <td>{{ date($settings['date_format'], strtotime($result->user->dob)) }}</td>
                    <th class="text-left">Admission No. :</th>
                    <td>{{ $result->user->student->admission_no }}</td>
                </tr>
                <tr>
                    <th class="text-left">Guardian Name :</th>
                    <td>{{ $result->user->student->guardian->full_name }}</td>
                    <th class="text-left">Class :</th>
                    <td>{{ $result->user->student->class_section->name }}</td>
                    <th class="text-left">Roll No. :</th>
                    <td>{{ $result->user->student->roll_number }}</td>
                </tr>
            </table>
        </div>

        {{-- <div class="exam-info table-gap">
            <table class="full-width cell-pedding" border="0">
                <tr>
                    <th class="text-left">Exam Name :</th>
                    <td>{{ $result->exam->name }}</td>
                    <th class="text-right">Session Year :</th>
                    <td class="text-right">{{ $result->session_year->name }}</td>
                </tr>
            </table>
            <hr>
        </div> --}}
{{--
        <div class="table-result table-gap">
            <table class="full-width cell-pedding">
                <tr>
                    <td colspan="5" class="text-center" style="font-size: 15px;letter-spacing: 1px">Scholastic Areas</td>
                </tr>
                <tr>
                    <th>SR No.</th>
                    <th>Subject</th>
                    <th>Total Marks</th>
                    <th>Obtain Marks</th>
                    <th>Grade</th>
                </tr>

                @foreach ($result->user->exam_marks as $mark)
                    <tr>
                        <td class="text-center">{{ ($loop->index + 1) }}</td>
                        <td>{{ $mark->class_subject->subject->name }}</td>
                        <td class="text-center">{{ $mark->timetable->total_marks }}</td>
                        <td class="text-center">{{ $mark->obtained_marks }}</td>
                        <td class="text-center">{{ $mark->grade }}</td>
                    </tr>
                @endforeach
                <tr>
                    <th colspan="2">Total</th>
                    <th>{{ $result->total_marks }}</th>
                    <th>{{ $result->obtained_marks }}</th>
                    <th>{{ $result->grade }}</th>
                </tr>
            </table>
        </div> --}}
        @php
        // Prepare subjects
        $subjects = [];

        // Helper to push exam marks
        $pushMarks = function($resultObj, $examLabel) use (&$subjects) {
            if (!$resultObj) return;

            foreach ($resultObj->user->exam_marks as $m) {
                $sub = $m->class_subject->subject->name;

                if (!isset($subjects[$sub])) {
                    $subjects[$sub] = [
                        'Unit Test I' => ['total' => '-', 'obtain' => '-'],
                        'Unit Test II' => ['total' => '-', 'obtain' => '-'],
                        'Term I Exam' => ['total' => '-', 'obtain' => '-'],
                    ];
                }

                $subjects[$sub][$examLabel] = [
                    'total' => $m->timetable->total_marks,
                    'obtain' => $m->obtained_marks
                ];
            }
        };

        // Merge marks into one array
        $pushMarks($result2, 'Unit Test I');
        $pushMarks($result3, 'Unit Test II');
        $pushMarks($result,  'Term I Exam');
        @endphp
        <div class="table-result table-gap">
            <table class="full-width cell-pedding">

                <tr>
                    <th colspan="2" class="text-center" style="font-size: 15px;">Scholastic Areas</th>
                    <th colspan="3">Full Marks</th>
                    <th colspan="5">Obtain Marks</th>

                </tr>

                <tr>
                    <th>SR</th>
                    <th>Subject</th>
                    <th>Unit I</th>
                    <th>Unit II</th>
                    <th>Term I</th>

                    <th>Unit I</th>
                    <th>Unit II</th>
                    <th>Term I</th>

                    <th>Total</th>
                    <th>Grade</th>
                </tr>

                @php $sr = 1; @endphp
                @foreach($subjects as $subject => $exams)
                    @php
                        $termTotal = $exams['Term I Exam']['total'] != '-' ? $exams['Term I Exam']['total'] : 0;
                        $termOb    = $exams['Term I Exam']['obtain'] != '-' ? $exams['Term I Exam']['obtain'] : 0;

                        $p = ($termTotal > 0) ? ($termOb / $termTotal) * 100 : 0;

                        if ($p >= 90) $grade = 'A1';
                        elseif ($p >= 80) $grade = 'A2';
                        elseif ($p >= 70) $grade = 'B1';
                        elseif ($p >= 60) $grade = 'B2';
                        elseif ($p >= 50) $grade = 'C1';
                        elseif ($p >= 40) $grade = 'C2';
                        else $grade = 'D';
                    @endphp

                    <tr>
                        <td class="text-center">{{ $sr++ }}</td>
                        <td>{{ $subject }}</td>

                        <td class="text-center">{{ $exams['Unit Test I']['total'] }}</td>
                        <td class="text-center">{{ $exams['Unit Test II']['total'] }}</td>
                        <td class="text-center">{{ $exams['Term I Exam']['total'] }}</td>

                        <td class="text-center">{{ $exams['Unit Test I']['obtain'] }}</td>
                        <td class="text-center">{{ $exams['Unit Test II']['obtain'] }}</td>
                        <td class="text-center">{{ $exams['Term I Exam']['obtain'] }}</td>
                        @php
                            $total = $exams['Unit Test I']['obtain'] + $exams['Unit Test II']['obtain'] + $exams['Term I Exam']['obtain'];
                        @endphp
                        <td class="text-center">{{ $total }}</td>
                        <td class="text-center">{{ $grade }}</td>
                    </tr>
                @endforeach

            </table>
        </div>

        <div class="result-status table-result">
            <table class="full-width cell-pedding table-gap">
                <tr>
                    <th>Status :</th>
                    <td>
                        @if ($result->status == 1)
                            Pass
                        @else
                            Fail
                        @endif
                    </td>
                    <th>Percentage :</th>
                    <td>
                        @if ($result->status == 1)
                            {{ number_format($result->percentage,2) }} %
                        @else
                            -
                        @endif
                    </td>
                    <th>
                        Grade :
                    </th>
                    <td>
                        @if ($result->status == 1)
                            {{ $result->grade }}
                        @else
                            -
                        @endif
                    </td>
                    <th>Rank :</th>
                    <td>
                        @if ($result->status == 1)
                            {{ $result->rank }}
                        @else
                            -
                        @endif
                    </td>
                </tr>
            </table>
        </div>


        <div class="footer table-result">
            <table class="full-width cell-pedding">
                <tr>
                    <th>Range</th>
                    @foreach ($grades as $grade)
                        <th>
                            {{ $grade->starting_range }} - {{ $grade->ending_range }}
                        </th>
                    @endforeach
                </tr>
                <tr>
                    <th>Grade</th>
                    @foreach ($grades as $grade)
                        <th>
                            {{ $grade->grade }}
                        </th>
                    @endforeach
                </tr>
            </table>
        </div>

    </div>
</body>

</html>
