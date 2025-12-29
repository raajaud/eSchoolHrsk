<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>School Fee Slips (2x2 Layout)</title>
    <style>
        @page { size: A4 portrait; margin: 0.8cm; }
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; }

        .page {
            display: grid;
            grid-template-columns: 1fr 1fr;
            grid-template-rows: 1fr 1fr;
            gap: 0.5cm;
            width: 19.4cm;
            height: 27.9cm;
            margin: auto;
            page-break-after: always;
        }

        .slip {
            border: 1px solid #000;
            background: #fff;
            padding: 0.4cm;
            font-size: 9px;
            box-sizing: border-box;

        }

        .fee-demand {
            position: relative;
            width: 100px;
            top: -10px;
            left: 42%;
            border: 0.01rem solid #000;
            padding: 1px 3px;
            font-size: 7px;
            text-align: center;
        }

        .header { display: flex; align-items: center; margin-bottom: 1px; }
        .logo img { width: 50px; height: 50px; border: 0.01rem solid #000; margin-right: 5px; margin-top: -15px; }
        .school-info { text-align: center; flex-grow: 1; margin-top: -70px; }
        .school-info .name { font-size: 14px; font-weight: bold; margin: 0; }
        .school-info .tagline, .school-info .address { font-size: 8px; margin: 0; }

        table { width: 100%; border-collapse: collapse; font-size: 10px; margin-top: 5px; }
        td, th { border: 0.3px solid #000; padding: 2px 3px; }
        th { background: #f5f5f5; text-align: center; }

        .signature { text-align: right; margin-top: 1px; font-size: 8px; }
    </style>
</head>
<body>
@foreach($guardians->chunk(4) as $pageGuardians)
    <div class="page">
        @foreach($pageGuardians as $index => $guardian)
            @php
                // $child = $guardian->child->first();
                $student = '';
                $class = '';
                $count = $guardian->child->count();

                foreach ($guardian->child as $key => $child) {
                    $student .= $child->user->full_name;
                    $class .= $child->class_section->class->name ?? '-';

                    if ($count > 1 && $key < $count - 2) {
                        $student .= ', ';
                        $class .= ', ';
                    } elseif ($count > 1 && $key == $count - 2) {
                        $student .= ' & ';
                        $class .= ' & ';
                    }
                }

                if (!$student) continue;

                $father = $guardian->full_name ?? '-';
                $month = $guardian->due_month;
                $previousMonth = now()->subMonth()->format('F-Y');
                $tuition = (int) $guardian->monthly_due;
                $back_dues = (int) ($guardian->total_fees - ($guardian->total_paid + $guardian->monthly_due));
                if($back_dues < 0) {
                    $back_dues = 0;
                }
                $total_dues = (int) ($guardian->total_fees - $guardian->total_paid);
            @endphp

            <div class="slip" @if($index%4!=0) style="margin-top:55px" @else style="margin-top:5px" @endif>
                <div class="fee-demand">FEE DEMAND</div>

                <div class="header" style="margin-top: -8px;">
                    <div class="logo">
                        <img src="https://kiranawala.in/storage/5/school-settings/68a06bdc249646.011787991755343836.png">
                    </div>
                    <div class="school-info">
                        <p class="name">HRSK INTERNATIONAL SCHOOL</p>
                        <p class="tagline"><small>Education Beyond Boundaries</small></p>
                        <p class="address">Goharrh Hat, Near Baldiabasa High School, Goalpokher, WB - 733208</p>
                        <p class="address">Mob: 9239192393, 7488699325</p>
                    </div>
                </div>
                {{-- <br> --}}
                <table>
                    <tr><td><b>Name:</b> {{ $student }}</td><td><b>Father's Name:</b> {{ $father }}</td><td><b>Class:</b> {{ $class }}</td></tr>
                    <tr><td><b>Month:</b> {{ $month }}</td><td><b>A/c No:</b> {{ $guardian->id }}</td><td><b>Date:</b> {{ now()->format('d-m-Y') }}</td></tr>
                </table>

                <table>
                    <thead>
                        <tr><th>Sl.</th><th>Particulars</th><th>Month</th><th>Rs.</th><th>P.</th></tr>
                    </thead>
                    <tbody>
                        <tr><td style="text-align: center;">1</td><td>School Fees (tuition/van/hostel/day boarding) </td><td>{{ $month }}</td><td>{{ number_format($tuition, 0, '.', ',') }}/-</td><td>00</td></tr>
                        <tr><td style="text-align: center;">2</td><td>Back Dues</td><td>{{ $previousMonth?'upto '.$previousMonth:'' }}</td><td>{{ number_format($back_dues, 0, '.', ',') }}/-</td><td>00</td></tr>
                        <tr style="font-size: 12px;"><td colspan="3" align="right"><b>Total Dues</b></td><td><b>{{ number_format($total_dues, 0, '.', ',') }}/-</b></td><td></td></tr>
                        <tr style="font-size: 12px; padding: 10px;"><td colspan="5" align="left">
                            Last Payment: {{ $guardian->lastPayment?->amount }}
                            on {{ \Carbon\Carbon::parse($guardian->lastPayment?->date)->format('d F Y') }}
                        </td></tr>
                    </tbody>
                </table>

                {{-- <p>
                    Last Payment: {{ $guardian->lastPayment?->amount }}
                    on {{ \Carbon\Carbon::parse($guardian->lastPayment?->date)->format('d F Y') }}
                </p> --}}
                {{-- <div class="signature">Signature</div> --}}
            </div>
            @if( ($index % 4) != 3 && ($index + 1) != $guardians->count() )
                <div style="">
                    <hr style="border: none; border-top: 0.3px dotted #000; position: relative; top: 25px;">
                </div>
            @endif
        @endforeach
    </div>
@endforeach
</body>
</html>
{{-- delete from user_charges;
delete from payment_transactions; --}}
