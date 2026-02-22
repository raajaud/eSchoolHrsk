@extends('layouts.master')

@section('title')
    {{ __('Pay Compulsory Fees') }}
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                {{ __('Pay Compulsory Fees') }}
            </h3>
        </div>

        <div class="row">
            <div class="col-md-12 grid-margin stretch-card search-container">
                <div class="card">
                    <div class="card-body d-flex justify-content-center row">
                        <div class="col-sm-12 col-md-5">
                            <form class="pt-3 create-form form-validation " method="post" action="{{ route('fees.compulsory.store') }}" data-success-function="successFunction" novalidate="novalidate">
                                <input type="hidden" name="parent_id" id="parent-id" value="{{$guardian->id}}"/>

                                <h4>Child(ren):
                                    @foreach ($guardian->child as $child)
                                        {{ $child->user->first_name }}
                                        @if(optional($child->class_section)->class)
                                            ({{ $child->class_section->class->name }})
                                        @endif

                                        @if (!$loop->last)
                                            @if ($loop->remaining == 1)
                                                &nbsp;&
                                            @else
                                                ,
                                            @endif
                                        @endif
                                    @endforeach
                                </h4>
                                <h4>Guardian:
                                    {{ $guardian->full_name }}
                                </h4><br>
                                <div class="form-group">
                                    <label for="payment-date">{{ __('date') }} <span class="text-danger">*</span></label>
                                    <input id="payment-date" type="text" name="date" class="datepicker-popup paid-date form-control" placeholder="{{ __('date') }}" autocomplete="off" required>
                                </div>

                                <hr>
                                <div class="form-group col-sm-12 col-md-12">
                                    <div class="compulsory-fees-content">
                                        <table class="table table-responsive table-bordered">
                                            <tbody>
                                                @foreach($charges as $charge)
                                                <tr>
                                                    <td class="text-left" style="width: 30%;">
                                                        {{ ucwords(str_replace('_', ' ', $charge->charge_type)) }}
                                                    </td>
                                                    <td class="text-left" style="width: 50%;">
                                                        {{ $charge->description ?? '-' }}
                                                        <br>
                                                        <small>{{ \Carbon\Carbon::parse($charge->charge_date)->format('d M Y') }}</small>
                                                    </td>
                                                    <td class="text-right" style="width: 20%;">
                                                        ₹{{ number_format($charge->amount, 2) }}
                                                    </td>
                                                </tr>
                                                @endforeach

                                                <tr>
                                                    <td class="text-left" style="width: 30%;"></td>
                                                    <td class="text-left" style="width: 50%;"><label>{{ __("Total Amount") }}</label></td>
                                                    <td class="text-right" style="width: 20%;">₹{{ $totalCharges }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-left" style="width: 30%;"></td>
                                                    <td class="text-left" style="width: 50%;"><label>{{ __("Total Paid") }}</label></td>
                                                    <td class="text-right" style="width: 20%;">₹{{ $totalPaid }}</td>
                                                </tr>

                                                <tr>
                                                    <td class="text-left" style="width: 30%;"></td>
                                                    <td class="text-left" style="width: 40%;"><label>{{ __("Total Dues") }}</label></td>
                                                    <td class="text-right" style="width: 30%;">
                                                        ₹{{ $totalCharges-$totalPaid }}
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td class="text-left" style="width: 30%;"></td>
                                                    <td class="text-left" style="width: 40%;"><label>{{ __("Total Payable") }}</label></td>
                                                    <td class="text-right" style="width: 30%;">
                                                        <input type="text" name="payment" id="payment" class="form-control"
                                                            style="font-size: 16px; width: 100px; text-align: right;"
                                                            value="{{ $totalCharges-$totalPaid }}">
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <hr>
                                <div class="row mode-container">
                                    <div class="form-group col-sm-12 col-md-12">
                                        <label>{{ __('mode') }} <span class="text-danger">*</span></label><br>
                                        <div class="d-flex">
                                            <div class="form-check form-check-inline">
                                                <label class="form-check-label">
                                                    <input type="radio" name="mode" class="cash-compulsory-mode  mode" value="1" checked>
                                                    {{ __('cash') }}
                                                </label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <label class="form-check-label">
                                                    <input type="radio" name="mode" class="cheque-compulsory-mode mode" value="2">
                                                    {{ __('online') }}
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group cheque-no-container" style="display: none">
                                    <label for="cheque_no">{{ __('Id') }} <span class="text-danger">*</span></label>
                                    <input type="number" id="cheque_no" name="cheque_no" placeholder="{{ __('Id') }}" class="form-control cheque-no" required/>
                                </div>
                                <input class="btn btn-theme btn-lg w-100" type="submit" value={{ __('pay') }} />
                            </form>
                        </div>
                        <div class="mt-4 col-sm-12 col-md-7">
                            <h4>Payment History</h4>
                            <table class="table table-bordered mt-5 table-responsive">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>Amount</th>
                                        <th>Mode</th>
                                        <th>Date</th>
                                        <th>Created At</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($logs as $index => $log)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ number_format($log->amount, 2) }}</td>
                                            <td>{{ ucfirst($log->payment_gateway) }}</td>
                                            <td>{{ \Carbon\Carbon::parse($log->date)->format('d-m-Y') }}</td>
                                            <td>{{ \Carbon\Carbon::parse($log->created_at)->format('d-m-Y h:i:s A') }}</td>
                                            <td>
                                                <button
                                                    class="btn btn-sm btn-outline-primary resend-receipt"
                                                    data-id="{{ $log->payment_id }}">
                                                    Resend Receipt
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
@section('js')
    <script>
        $('#payment-date').datepicker({
            format: "dd-mm-yyyy",
        }).datepicker("setDate", 'now');


        function successFunction() {
            window.location.href = "{{route('fees.compulsory.index', [1, $guardian->id])}}";
        }


    </script>
    <script>
    $(document).on('click', '.resend-receipt', function () {
        let paymentId = $(this).data('id');

        if (!confirm('Resend receipt to parent WhatsApp?')) return;

        $.get("{{ url('fees/resend-receipt') }}/" + paymentId, function (res) {
            console.log(res);
            alert(res.message || 'Receipt sent');
        }).fail(function () {
            alert('Failed to resend receipt');
        });
    });
    </script>
@endsection
