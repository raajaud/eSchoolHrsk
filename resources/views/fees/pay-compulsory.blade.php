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
                    <div class="card-body d-flex justify-content-center">
                        <form class="pt-3 create-form form-validation col-sm-12 col-md-7" method="post" action="{{ route('fees.compulsory.store') }}" data-success-function="successFunction" novalidate="novalidate">
                            <input type="hidden" name="fees_id" id="compulsory-fees-id" value="{{$fees->id}}"/>
                            <input type="hidden" name="student_id" id="student-id" value="{{$student->id}}"/>
                            <input type="hidden" name="parent_id" id="parent-id" value="{{$student->student->guardian_id}}"/>
                            <input type="hidden" name="installment_mode" id="installment-mode" value="0"/>
                            <input type="hidden" name="total_amount" id="total-amount" value="{{$fees->adjusted_compulsory_fees}}"/>
                            <input type="hidden" id="adjusted_compulsory_fees" name="adjusted_compulsory_fees" value="{{$fees->adjusted_compulsory_fees}}">
                            <input type="hidden" id="remaining_amount" value="{{$fees->remaining_amount}}">
                            <input type="hidden" id="total_installment_amount" value="0">
                            <h4>
                                {{ $student->full_name }} :-
                                {{ optional(optional($student->student)->class_section)->full_name ?? '' }}
                            </h4><br>
                            <div class="form-group">
                                <label for="payment-date">{{ __('date') }} <span class="text-danger">*</span></label>
                                <input id="payment-date" type="text" name="date" class="datepicker-popup paid-date form-control" placeholder="{{ __('date') }}" autocomplete="off" required>
                            </div>

                            <hr>
                            <div class="form-group col-sm-12 col-md-12">
                                <div class="compulsory-fees-content">
                                    <table class="table">
                                        <tbody>
                                            @foreach($fees->fees_details as $category => $details)
                                                <tr>
                                                    <td class="text-left" style="width: 30%;">{{ ucwords($category) }}</td>
                                                    <td class="text-left" style="width: 50% !important;">
                                                        {{ implode(', ', $details['month_names']) }}
                                                        <br>
                                                        <small>@if($details['months'] != 0) {{ $details['months'] }} Month(s) × @endif ₹{{ $details['fee_per_month'] }}</small>
                                                    </td>
                                                    <td class="text-right" style="width: 20%;" colspan="1">
                                                        ₹{{ $details['total'] }}
                                                    </td>
                                                </tr>
                                            @endforeach

                                            <tr>
                                                <td class="text-left" style="width: 30%;"></td>
                                                <td class="text-left" style="width: 50%;"><label>{{ __("Total Amount") }}</label></td>
                                                <td class="text-right" style="width: 20%;">₹{{ $fees->adjusted_compulsory_fees }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-left" style="width: 30%;"></td>
                                                <td class="text-left" style="width: 50%;"><label>{{ __("Total Paid") }}</label></td>
                                                <td class="text-right" style="width: 20%;">₹{{ $fees->paid }}</td>
                                            </tr>

                                            <tr>
                                                <td class="text-left" style="width: 30%;"></td>
                                                <td class="text-left" style="width: 40%;"><label>{{ __("Total Dues") }}</label></td>
                                                <td class="text-right" style="width: 30%;">
                                                    ₹{{ $fees->adjusted_compulsory_fees-$fees->paid }}
                                                </td>
                                            </tr>

                                            <tr>
                                                <td class="text-left" style="width: 30%;"></td>
                                                <td class="text-left" style="width: 40%;"><label>{{ __("Total Payable") }}</label></td>
                                                <td class="text-right" style="width: 30%;">
                                                    <input type="text" name="payment" id="payment" class="form-control"
                                                           style="font-size: 16px; width: 100px; text-align: right;"
                                                           value="{{ $fees->adjusted_compulsory_fees - $fees->paid }}">
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
                            <input class="btn btn-theme" type="submit" value={{ __('pay') }} />
                        </form>
                        <div class="mt-4 col-sm-12 col-md-5">
                            <h4>Payment History</h4>
                            <table class="table table-bordered mt-5">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>Amount</th>
                                        <th>Mode</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($logs as $index => $log)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ number_format($log->amount, 2) }}</td>
                                            <td>{{ ucfirst($log->payment_gateway) }}</td>
                                            <td>{{ \Carbon\Carbon::parse($log->date)->format('d-m-Y') }}</td>
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

        @if($student->fees_paid && $student->fees_paid->is_used_installment)
        $('.pay-in-installment').trigger('click').attr("disabled", true);
        @endif

        function successFunction() {
            window.location.href = "{{route('fees.paid.index')}}";
        }
    </script>
@endsection
