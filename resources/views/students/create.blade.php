@extends('layouts.master')

@section('title')
    {{ __('students') }}
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                {{ __('manage') . ' ' . __('students') }}
            </h3>
        </div>

        <div class="row">
            <div class="col-lg-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            {{ __('create') . ' ' . __('students') }}
                        </h4>
                        <div id="form-errors" class="alert alert-danger d-none"></div>
                        <form action="{{ route('students.store') }}" method="POST">
                            @csrf

                            <div class="row">

                                <div class="col-md-4">
                                    <label>Student Name <span class="text-danger">*</span></label>
                                    <input type="text" name="first_name" class="form-control" required>
                                </div>

                                <div class="col-md-4">
                                    <label>Gender <span class="text-danger">*</span></label><br>
                                    <label><input type="radio" name="gender" value="male" checked> Male</label>
                                    <label class="ml-3"><input type="radio" name="gender" value="female"> Female</label>
                                </div>

                                <div class="col-md-4">
                                    <label>Admission Date <span class="text-danger">*</span></label>
                                    <input type="text" name="admission_date"
                                        class="form-control datepicker-popup-no-future"
                                        required autocomplete="off">
                                </div>

                                <div class="col-md-4 mt-3">
                                    <label>DOB <span class="text-danger">*</span></label>
                                    <input type="text" name="dob"
                                        class="form-control datepicker-popup-no-future"
                                        required autocomplete="off">
                                </div>


                                <div class="col-md-4 mt-3">
                                    <label for="class_section">{{ __('class_section') }} <span class="text-danger">*</span></label>
                                    <select name="class_section_id" id="class_section" class="form-control select2">
                                        <option value="">{{ __('select') . ' ' . __('Class') . ' ' . __('section') }}</option>
                                        @if(count($class_sections))
                                            @foreach ($class_sections as $class_section)
                                                <option value="{{ $class_section->id }}">{{$class_section->full_name}}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>

                                <div class="col-md-4 mt-3">
                                    <label for="session_year_id">{{ __('session_year') }} <span class="text-danger">*</span></label>
                                    <select name="session_year_id" id="session_year_id" class="form-control select2">
                                        @if(count($sessionYears))
                                            @foreach ($sessionYears as $year)
                                                <option value="{{ $year->id }}" {{$year->default==1 ? "selected" : ""}}>{{$year->name}}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>

                                <div class="col-md-4 mt-3">
                                    <label>Monthly Fees</label>
                                    <input type="number" name="monthly_fees" class="form-control">
                                </div>

                                <div class="col-md-12 mt-3">
                                    <label>Current Address <span class="text-danger">*</span></label>
                                    <textarea name="current_address" class="form-control" rows="2" required></textarea>
                                </div>



                            </div>

                            <hr>

                            <div class="row">

                                <div class="col-md-4">
                                    <label>Guardian Name <span class="text-danger">*</span></label>
                                    <input type="text" name="guardian_first_name" class="form-control" required>
                                </div>

                                <div class="col-md-4">
                                    <label>Guardian Mobile <span class="text-danger">*</span></label>
                                    <input type="number" name="guardian_mobile" class="form-control" required>
                                </div>

                                <div class="col-md-4 mt-2">
                                    <label>Guardian Gender <span class="text-danger">*</span></label><br>
                                    <label><input type="radio" name="guardian_gender" value="male" checked> Male</label>
                                    <label class="ml-3"><input type="radio" name="guardian_gender" value="female"> Female</label>
                                </div>

                            </div>

                            <button type="submit" class="btn btn-theme mt-4 float-right">Submit</button>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
<script>

    $('#admission_date').datepicker({
        format: "dd-mm-yyyy",
        rtl: isRTL()
    }).datepicker("setDate", 'now');

    $('form').on('submit', function (e) {
        e.preventDefault();

        let form = $(this);
        let url = form.attr('action');

        $('#form-errors').addClass('d-none').html('');

        $.ajax({
            url: url,
            type: 'POST',
            data: form.serialize(),
            success: function (res) {
                if (res.status === true) {
                    // alert(res.message);
                    showSuccessToast(res.message)
                    // window.location.reload(); // enable if needed
                } else {
                    showErrorToast(res.message || 'An error occurred. Please try again.');
                }
                // window.location.reload(); // reload ONLY on success
            },
            error: function (xhr) {

                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    let html = '<ul class="mb-0">';

                    $.each(errors, function (key, value) {
                        html += `<li>${value[0]}</li>`;
                    });

                    html += '</ul>';

                    $('#form-errors').removeClass('d-none').html(html);
                } else {
                    alert('Something went wrong. Please try again.');
                }
            }
        });
    });
</script>
@endsection
