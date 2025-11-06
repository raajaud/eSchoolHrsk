<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Receipt</title>

    <style>
        body {
            width: 400px;
            /* font-family: 'Arial', sans-serif; */
            font-family: 'Arial', 'Helvetica', 'DejaVu Sans', sans-serif;
            font-size: 13px;
            padding: 20px;
            border: 2px solid #000;
            border-radius: 10px;
            background: pink;
        }
        .header {
            text-align: center;
        }
        .header img {
            height: 60px;
        }
        .school-name {
            font-size: 18px;
            font-weight: bold;
            margin-top: 5px;
        }
        .tagline {
            font-style: italic;
            color: #555;
            margin-bottom: 10px;
        }
        hr {
            border: none;
            border-top: 1px dashed #666;
            margin: 10px 0;
        }
        table.info {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }
        table.info td {
            border: 1px solid #000;
            padding: 4px 6px;
            vertical-align: top;
            width: 50%;
        }
        .amount, .amount_img {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin: 5px 0;
        }
        .footer {
            text-align: center;
            color: gray;
            font-style: italic;
            margin-top: 10px;
        }
        @page { margin: 0; }
        body { margin: 20px; padding: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <img src="https://kiranawala.in/storage/5/school-settings/68a06bdc249646.011787991755343836.png" alt="Logo">
        <div class="school-name">HRSK INTERNATIONAL SCHOOL</div>
        <div class="tagline">Nurturing Excellence, One Student at a Time</div>
    </div>
    <hr>
    <div class="amount_img">
        <img src="https://kiranawala.in/uploads/tick.png" alt="Success" style="height:20px; vertical-align: middle; margin-top:5px;">
        Payment Received
    </div>
    <hr>
    <table class="info">
        <tr>
            <td><b>Receipt No:</b> {{ substr($payment_id, 0, 5) . '...' . substr($payment_id, -5) }}</td>
            <td><b>Date:</b> {{ $date }}</td>
        </tr>
        <tr>
            <td><b>Student:</b> {{ $student }}</td>
            <td><b>Class:</b> {{ $class }}</td>
        </tr>
        <tr>
            <td><b>Father:</b> {{ $father }}</td>
            <td><b>Mode:</b> Cash</td>
        </tr>
        {{-- <tr>
            <td><b>Amount Paid:</b> Rs. {{ number_format($amount, 0) }}</td>
            <td><b>Dues upto {{ $month }}:</b> Rs. {{ number_format($amount, 0) }}</td>
        </tr> --}}
    </table>
    <div class="amount">
        <table style="width:100%; border-collapse:collapse; margin-top:10px;">
            <tr>
                <td style="border:1px solid #000; width:50%; text-align:center; background:#e8f5e9; padding:6px;">
                    <div style="font-size:12px; font-weight:bold;">Amount Paid</div>
                </td>
                <td style="border:1px solid #000; width:50%; text-align:center; background:#fff3e0; padding:6px;">
                    <div style="font-size:12px; font-weight:bold;">Amount Dues ({{ $month }})</div>
                </td>
            </tr>
            <tr>
                <td style="border:1px solid #000; width:50%; text-align:center; background:#e8f5e9; padding:6px;">
                    <div style="font-size:18px; margin-top:7px;"><img src="https://kiranawala.in/uploads/inr.png" style="height:12px; margin-top: 5px; vertical-align:middle;"> {{ number_format($amount, 0) }}/-</div>
                </td>

                <td style="border:1px solid #000; width:50%; text-align:center; background:#fff3e0; padding:6px;">
                    <div style="font-size:18px; margin-top:7px;"><img src="https://kiranawala.in/uploads/inr.png" style="height:12px; margin-top: 5px; vertical-align:middle;"> {{ number_format($dues, 0) }}/-</div>
                </td>
            </tr>
        </table>
    </div>

    <hr>
    <div class="footer">
        This is System-generated receipt. Please verify the details.
    </div>
</body>
</html>
{{-- <!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Receipt</title>
    <style>
        body {
            width: 400px;
            font-family: 'Arial', sans-serif;
            font-size: 13px;
            padding: 20px;
            border: 2px solid #000;
            border-radius: 10px;
            background: #fff;
        }
        .header {
            text-align: center;
        }
        .header img {
            height: 60px;
        }
        .school-name {
            font-size: 18px;
            font-weight: bold;
            margin-top: 5px;
        }
        .tagline {
            font-style: italic;
            color: #555;
            margin-bottom: 10px;
        }
        hr {
            border: none;
            border-top: 1px dashed #666;
            margin: 10px 0;
        }
        .info p {
            margin: 3px 0;
        }
        .amount {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin: 15px 0;
        }
        .amount_img {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin: 15px 0;
        }
        .footer {
            text-align: center;
            color: gray;
            font-style: italic;
            margin-top: 10px;
        }
        @page { margin: 0; }
        body { margin: 20px; padding: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <img src="https://kiranawala.in/storage/5/school-settings/68a06bdc249646.011787991755343836.png" alt="Logo">
        <div class="school-name">HRSK INTERNATIONAL SCHOOL</div>
        <div class="tagline">Nurturing Excellence, One Student at a Time</div>
    </div>
    <hr>
    <div class="info">
        <p><b>Receipt No:</b> {{ $payment_id }}</p>
        <p><b>Date:</b> {{ $date }}</p>
        <p><b>Student:</b> {{ $student }}</p>
        <p><b>Class:</b> {{ $class }}</p>
        <p><b>Father:</b> {{ $father }}</p>
        <p><b>Amount:</b> Rs. {{ number_format($amount, 2) }}</p>
        <p><b>Mode:</b> Cash</p>
    </div>
    <div class="amount">
        <h4>Rs. {{ number_format($amount, 0) }}/-</h4>
    </div>
    <div class="amount_img">
        <img src="https://kiranawala.in/uploads/tick.png" alt="Success" style="height:20px; vertical-align: middle; margin-top:5px;">
        Payment Received</div>
    <hr>
    <div class="footer">
        * System-generated receipt • HRSK International School
    </div>
</body>
</html> --}}
