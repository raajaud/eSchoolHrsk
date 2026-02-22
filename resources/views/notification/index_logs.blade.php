@extends('layouts.master')

@section('title')
    {{ __('notification') }}
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                {{ __('manage_notification') }}
            </h3>
        </div>

        <div class="row">

            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            Notification Logs
                        </h4>

                        <table aria-describedby="mydesc" class='table' id='table_list' data-toggle="table"
                               data-url="{{ route('whatsapp.logs') }}" data-click-to-select="true"
                               data-side-pagination="server" data-pagination="true" data-page-list="[5, 10, 20, 50, 100, 200]"
                               data-search="true" data-toolbar="#toolbar" data-show-columns="true" data-show-refresh="true"
                               data-fixed-columns="false" data-fixed-number="2" data-fixed-right-number="1"
                               data-trim-on-search="false" data-mobile-responsive="true" data-sort-name="id"
                               data-sort-order="desc" data-maintain-selected="true" data-export-data-type='all' data-show-export="true"
                               data-export-options='{ "fileName": "notification-list-<?= date('d-m-y') ?>","ignoreColumn":["operate"]}'
                               data-escape="true" data-query-params="queryParams">
                            <thead>
                            <tr>
                                <th data-field="id" data-visible="false">ID</th>
                                <th data-field="no">No.</th>
                                <th data-field="number">Mobile</th>
                                <th data-field="message" data-formatter="descriptionFormatter">Message</th>
                                <th data-field="file_url" data-formatter="fileFormatter">Media</th>
                                <th data-field="status" data-formatter="statusFormatter">Status</th>
                                <th data-field="response">Response</th>
                                <th data-field="created_at">Sent At</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        $(document).ready(function () {
            $('.role-list').hide(500);
            $('.user-list').hide(500);
            $('.type').trigger('change');
        });
        function formSuccessFunction(response) {
            setTimeout(() => {
                // Reset selections
                selections = [];
                user_list = [];
                $('.roles').show();
                $('.over_due_fees_roles').hide();
                $('.type').trigger('change');
                $('#table_user_list').bootstrapTable('refresh');

                // reset form fields
                $('.form-control').val('');
            }, 500);
        }

        $('#reset').click(function (e) {
            // e.preventDefault();
            $('.default-all').prop('checked', true);
            $('.type').trigger('change');
        });


        $('.type').change(function (e) {
            var selectedType = $('input[name="type"]:checked').val();
            e.preventDefault();
            $('.user_id').val('').trigger('change');

            $('.roles').hide();
            $('.over_due_fees_roles').hide();
            $('.user-list').hide();
            $('.role-list').hide();

            $('#table_user_list').bootstrapTable('uncheckAll');

            if (selectedType == 'Roles') {
                $('.roles').show();
                $('.role-list').show();

                $("#roles").prop("disabled", false);
                $("#over_due_fees_roles").prop("disabled", true);

                // reset roles
                $("#roles").val('').trigger('change');

            } else if (selectedType == 'OverDueFees') {
                $('.over_due_fees_roles').show();
                $('.user-list').show();

                $("#roles").prop("disabled", true);
                $("#over_due_fees_roles").prop("disabled", false);

                // reset roles
                $("#over_due_fees_roles").val('').trigger('change');
            }

        });

        $('#roles').change(function (e) {
            e.preventDefault();
            $('#table_user_list').bootstrapTable('refresh');
        });

        $('#over_due_fees_roles').change(function (e) {
            e.preventDefault();
            $('#table_user_list').bootstrapTable('refresh');
        });

        $('.type').change(function (e) {
            e.preventDefault();
            $('#table_user_list').bootstrapTable('refresh');

        });

        var $tableList = $('#table_user_list')
        var selections = []
        var user_list = [];

        function responseHandler(res) {
            $.each(res.rows, function (i, row) {
                row.state = $.inArray(row.id, selections) !== -1
            })
            return res
        }

        $(function () {
            $tableList.on('check.bs.table check-all.bs.table uncheck.bs.table uncheck-all.bs.table',
                function (e, rowsAfter, rowsBefore) {
                    user_list = [];
                    var rows = rowsAfter
                    if (e.type === 'uncheck-all') {
                        rows = rowsBefore
                    }
                    var ids = $.map(!$.isArray(rows) ? [rows] : rows, function (row) {
                        return row.id
                    })

                    var func = $.inArray(e.type, ['check', 'check-all']) > -1 ? 'union' : 'difference'
                    selections = window._[func](selections, ids)
                    selections.forEach(element => {
                        user_list.push(element);
                    });

                    $('textarea#user_id').val(user_list);
                })
        })

    </script>

<script>
function statusFormatter(value) {
    if (value === 'success') {
        return '<span class="badge badge-success">Success</span>';
    }
    return '<span class="badge badge-danger">Failed</span>';
}

function fileFormatter(value) {
    if (!value) return '-';
    return `<a href="${value}" target="_blank">View</a>`;
}
</script>
@endsection
