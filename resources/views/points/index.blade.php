@extends('layouts.master')

@section('title')
    Manage Points
@endsection

@section('content')
<div class="content-wrapper">
    <div class="page-header">
        <h3 class="page-title">Manage Student Points</h3>
    </div>

    <div class="row">
        <div class="col-md-12 grid-margin stretch-card search-container">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Add / Edit Points</h4>

                    <form action="{{ route('points.store') }}" class="create-form" id="formdata" data-success-function="pointsSuccess">
                        @csrf

                        <div class="row">
                            <div class="form-group col-md-4">
                                <label>Class Section</label>
                                <select name="class_section_id" id="class_section_id" required class="form-control">
                                    <option value="">-- Select Class Section --</option>
                                    @foreach ($classes as $class)
                                        <option value="{{ $class->id }}">{{ $class->full_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group col-md-12">
                                <button type="button" id="search" class="btn btn-theme">Search</button>
                            </div>
                        </div>

                        <div class="show_student_list" style="display:none;">
                            <table class="table student_table" id="table_list"
                                   data-toggle="table"
                                   data-url="{{ route('points.list') }}"
                                   data-side-pagination="client"
                                   data-pagination="false"
                                   data-search="true"
                                   data-show-refresh="true"
                                   data-query-params="pointsQuery">
                                <thead>
                                    <tr>
                                        <th data-field="id" data-visible="false">ID</th>
                                        <th data-field="no">No.</th>
                                        <th data-field="student">Student</th>
                                        <th data-field="occasion" data-formatter="occasionInput">Occasion</th>
                                        <th data-field="points" data-formatter="pointsInput">Points</th>
                                        <th data-field="remarks" data-formatter="remarksInput">Remarks</th>
                                        <th data-field="date" data-formatter="dateInput">Date</th>
                                    </tr>
                                </thead>
                            </table>

                            <div class="form-group mt-3">
                                <button class="btn btn-theme float-right ml-3" type="submit">Submit</button>
                                <button class="btn btn-secondary float-right" type="reset">Reset</button>
                            </div>

                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $('#search').on('click', function () {
        $('.show_student_list').show();
        $('.student_table').bootstrapTable('refresh');
    });

    function pointsQuery(p) {
        p.class_section_id = $('#class_section_id').val();
        return p;
    }

    function pointsSuccess(res) {
        $('.student_table').bootstrapTable('refresh');
    }

    // ==== FORMATTERS ==== //

    function occasionInput(value, row) {
        return `<input type="hidden" name="child_id[]" value="${row.id}">
                <input type="text" class="form-control" name="occasion[]" value="${value ?? ''}">`;
    }

    function pointsInput(value) {
        return `<input type="number" class="form-control" name="points[]" value="${value ?? ''}">`;
    }

    function remarksInput(value) {
        return `<input type="text" class="form-control" name="remarks[]" value="${value ?? ''}">`;
    }

    function dateInput(value) {
        let today = new Date().toISOString().split('T')[0]; // YYYY-MM-DD
        let finalDate = value && value !== "" ? value : today;

        return `<input type="date" class="form-control" name="date[]" value="${finalDate}">`;
    }
</script>
@endsection
