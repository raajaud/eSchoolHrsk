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
                    <h4 class="card-title">All Points</h4>
                    <div class="row">
                        <div class="form-group col-md-12 d-flex align-items-end">

                            <div style="flex:1; margin-right:10px;">
                                    <label>Search Student</label>
                                    <input type="text" id="studentSearch" class="form-control" placeholder="Type student name..." autocomplete="off">
                                    <div id="searchSuggestions" class="list-group mt-1" style="position:absolute; z-index:999; width:100%; display:none;"></div>
                            </div>

                            <div class="mr-3 py-3">
                                <span class="">or</span>
                            </div>
                            <div class="mr-1">
                                <a href="{{ route('points.index') }}" class="btn btn-theme">Add Class Wise Points</a>
                            </div>

                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-12 d-flex align-items-end">

                            <div class="" style="flex:1; margin-right:10px;">
                                <label>Class Section</label>
                                <select name="class_section_id" id="class_section_id" required class="form-control">
                                    <option value="">-- Select Class Section --</option>
                                    @foreach ($classes as $class)
                                        <option value="{{ $class->id }}">{{ $class->full_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="ml-1">
                                <button type="button" id="search" class="btn btn-theme">Search</button>
                            </div>

                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <canvas id="chartStudents"></canvas>
                        </div>

                        <div class="col-md-4">
                            <canvas id="chartClasses"></canvas>
                        </div>

                        <div class="col-md-4">
                            <canvas id="chartActivities"></canvas>
                        </div>

                    </div>



                        <div class="show_student_list" style="display:none;">
                            <table class="table student_table" id="table_list"
                                   data-toggle="table"
                                   data-url="{{ route('points.all_points_list') }}"
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
                                        <th data-field="class">Class</th>
                                        <th data-field="points" data-sortable="true">All Points</th>
                                    </tr>
                                </thead>
                            </table>

                        </div>

                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="studentPointsModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Student Points</h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>

      <div class="modal-body">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Occasion</th>
                    <th>Points</th>
                    <th>Remarks</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody id="studentPointsTable"></tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="addPointsModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Add Points</h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>

      <div class="modal-body">
          <input type="hidden" id="sp_student_id">

          <p><strong>Name:</strong> <span id="sp_name"></span></p>
          <p><strong>Class:</strong> <span id="sp_class"></span></p>

          <div class="form-group">
              <label>Occasion</label>
              <input type="text" id="sp_occasion" class="form-control">
          </div>

          <div class="form-group">
              <label>Points</label>
              <input type="number" id="sp_points" class="form-control" required>
          </div>

          <div class="form-group">
              <label>Remarks</label>
              <input type="text" id="sp_remarks" class="form-control">
          </div>
      </div>

      <div class="modal-footer">
          <button class="btn btn-theme" id="saveStudentPoint">Save</button>
      </div>

    </div>
  </div>
</div>

@endsection

@section('script')
<script>
    $(document).ready(function () {
        $('.show_student_list').show();
        $('.student_table').bootstrapTable('refresh');
    });
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

    const chartColors = [
        '#FF6384', '#36A2EB', '#FFCE56',
        '#4BC0C0', '#9966FF', '#FF9F40',
        '#00C49F', '#845EC2', '#D65DB1',
    ];
    // Load Stats (Charts)
    $.get("{{ route('points.stats') }}", function(res) {

        new Chart(document.getElementById("chartStudents"), {
            type: 'bar',
            data: {
                labels: res.top_students.map(s => s.name),
                datasets: [{
                    label: 'Top 10 Students',
                    data: res.top_students.map(s => s.points),
                    backgroundColor: chartColors,
                    borderColor: chartColors,
                    borderWidth: 1
                }]
            }
        });

        new Chart(document.getElementById("chartClasses"), {
            type: 'bar',
            data: {
                labels: res.top_classes.map(c => c.class),
                datasets: [{
                    label: 'Top 10 Classes',
                    data: res.top_classes.map(c => c.points),
                    backgroundColor: chartColors,
                    borderColor: chartColors,
                    borderWidth: 1
                }]
            }
        });

        new Chart(document.getElementById("chartActivities"), {
            type: 'pie',
            data: {
                labels: res.top_activities.map(a => a.remarks),
                datasets: [{
                    data: res.top_activities.map(a => a.total),
                    backgroundColor: chartColors,
                    borderColor: '#fff',
                    borderWidth: 1
                }]
            }
        });
    });


    // When clicking row → load modal
    $('#table_list').on('click-row.bs.table', function (e, row) {
        $.get("{{ url('students/student-points') }}/" + row.id, function(res){
            let html = '';
            res.forEach(p => {
                html += `
                    <tr>
                        <td>${p.occasion}</td>
                        <td>${p.points}</td>
                        <td>${p.remarks}</td>
                        <td>${p.date}</td>
                    </tr>
                `;
            });
            $('#studentPointsTable').html(html);
            $('#studentPointsModal').modal('show');
        });
    });

    $('#studentSearch').on('keyup', function () {
        let q = $(this).val();

        if (q.length < 2) {
            $('#searchSuggestions').hide();
            return;
        }

        $.get("{{ route('students.search') }}", { q }, function(res) {
            let html = '';

            res.forEach(s => {
                html += `
                    <a href="#" class="list-group-item list-group-item-action student-suggestion"
                        data-id="${s.id}" data-name="${s.name}" data-class="${s.class}">
                        ${s.name} <small class="text-muted">(${s.class})</small>
                    </a>
                `;
            });

            $('#searchSuggestions').html(html).show();
        });
    });

    // ==============================
    // CLICK SUGGESTION → OPEN MODAL
    // ==============================
    $(document).on('click', '.student-suggestion', function (e) {
        e.preventDefault();

        $('#sp_student_id').val($(this).data('id'));
        $('#sp_name').text($(this).data('name'));
        $('#sp_class').text($(this).data('class'));

        $('#searchSuggestions').hide();
        $('#addPointsModal').modal('show');
    });

    // ==============================
    // SAVE POINT
    // ==============================
    $('#saveStudentPoint').click(function () {
        $.post("{{ route('students.save_point') }}", {
            child_id: $('#sp_student_id').val(),
            occasion: $('#sp_occasion').val(),
            points: $('#sp_points').val(),
            remarks: $('#sp_remarks').val(),
            _token: '{{ csrf_token() }}'
        }, function(res) {

            showSuccessToast(res.msg);

            $('#addPointsModal').modal('hide');
            $('#table_list').bootstrapTable('refresh');
        });
    });
</script>
@endsection
