@extends('layouts.master')

@section('title')
    {{ __('Guardian') }}
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                {{ __('manage') . ' ' . __('Guardian') }}
            </h3>
        </div>

        <div class="row">
            <div class="col-lg-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            {{ __('list') . ' ' . __('Guardian') }}
                        </h4>
                        <div class="row mt-3" id="toolbar">
                            <div class="form-group col-sm-12 col-md-4">
                                <label class="filter-menu">{{ __('Class') }} <span class="text-danger">*</span></label>
                                <select name="filter_class_id" id="filter_class_id" class="form-control">
                                    <option value="">{{ __('all_class') }}</option>
                                    @foreach ($classes as $class)
                                        <option value={{ $class->id }}>{{$class->full_name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-sm-12 col-md-4">
                                <label class="filter-menu">{{ __('class_section') }} <span class="text-danger">*</span></label>
                                <select required name="filter_class_section_id" class="form-control" id="filter_class_section_id">
                                    <option value="">{{ __('all_section') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <table aria-describedby="mydesc" class='table' id='table_list' data-toggle="table"
                                       data-url="{{ route('guardian.show',1) }}" data-click-to-select="true"
                                       data-side-pagination="server" data-pagination="true" data-toolbar="#toolbar"
                                       data-show-columns="true" data-show-refresh="true" data-search="true" data-trim-on-search="false" data-mobile-responsive="true" data-sort-name="id"
                                       data-sort-order="desc" data-maintain-selected="true" data-export-data-type='all' data-show-export="true"
                                       data-export-options='{ "fileName": "guardian-list-<?= date('d-m-y') ?>" ,"ignoreColumn": ["operate"]}'
                                       data-query-params="guardianQueryParams" data-escape="true">
                                    <thead>
                                    <tr>
                                        <th scope="col" data-sortable="true" data-visible="false" data-align="center" data-field="id"> {{ __('id') }}</th>
                                        <th scope="col" data-align="center" data-field="no"> {{ __('no.') }} </th>
                                        <th scope="col" data-align="center" data-field="first_name"> {{ __('first_name') }} </th>
                                        <th scope="col" data-align="center" data-field="last_name"> {{ __('last_name') }} </th>
                                        <th scope="col" data-align="center" data-field="gender"> {{ __('gender') }} </th>
                                        <th scope="col" data-align="center" data-field="email"> {{ __('email') }} </th>
                                        <th scope="col" data-align="center" data-field="mobile"> {{ __('mobile') }} </th>
                                        <th scope="col" data-align="center" data-field="more" data-escape="false"> {{ __('More') }} </th>
                                        <th scope="col" data-align="center" data-field="image" data-formatter="imageFormatter"> {{ __('image') }} </th>
                                        <th data-events="guardianEvents" scope="col" data-field="operate" data-escape="false"> {{ __('action') }} </th>
                                    </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editModal" data-backdrop="static" tabindex="-1" role="dialog"
         aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">{{ __('edit') . ' ' . __('Guardian') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true"><i class="fa fa-close"></i></span>
                    </button>
                </div>
                <form id="edit-form" novalidate="novalidate" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="edit_id" id="edit_id">
                        <div class="row">
                            <div class="form-group col-sm-12 col-md-6">
                                <label>{{ __('first_name') }} <span class="text-danger">*</span></label>
                                {!! Form::text('first_name', null, ['required', 'placeholder' => __('first_name'), 'class' => 'form-control', 'id' => 'first_name']) !!}
                            </div>

                            <div class="form-group col-sm-12 col-md-6">
                                <label>{{ __('last_name') }} <span class="text-danger">*</span></label>
                                {!! Form::text('last_name', null, ['required', 'placeholder' => __('last_name'), 'class' => 'form-control', 'id' => 'last_name']) !!}
                            </div>

                            <div class="form-group col-sm-12 col-md-6">
                                <label>{{ __('email') }} <span class="text-danger">*</span></label>
                                {!! Form::text('email', null, ['required', 'placeholder' => __('email'), 'class' => 'form-control', 'id' => 'email']) !!}
                            </div>

                            <div class="form-group col-sm-12 col-md-6">
                                <label>{{ __('mobile') }} <span class="text-danger">*</span></label>
                                {!! Form::number('mobile', null, ['required', 'placeholder' => __('mobile'), 'min' => 0 ,'class' => 'form-control', 'id' => 'mobile']) !!}
                            </div>

                            <div class="form-group col-sm-12 col-md-12">
                                <label>{{ __('gender') }} <span class="text-danger">*</span></label>
                                <div class="d-flex">
                                    <div class="form-check form-check-inline">
                                        <label class="form-check-label">
                                            {!! Form::radio('gender', 'male', null, ['class' => 'form-check-input edit', 'id' => 'male']) !!}
                                            {{ __('male') }}
                                        </label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <label class="form-check-label">
                                            {!! Form::radio('gender', 'female', null, ['class' => 'form-check-input edit', 'id' => 'female']) !!}
                                            {{ __('female') }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-sm-12 col-md-12 col-lg-6 col-xl-4">
                                <label for="image">{{ __('image') }} </label>
                                <input type="file" name="image" class="file-upload-default"/>
                                <div class="input-group col-xs-12">
                                    <input type="text" id="image" class="form-control file-upload-info" disabled="" placeholder="{{ __('image') }}"/>
                                    <span class="input-group-append">
                                            <button class="file-upload-browse btn btn-theme" type="button">{{ __('upload') }}</button>
                                        </span>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-sm-12 col-md-4">
                                <div class="d-flex">
                                    <div class="form-check w-fit-content">
                                        <label class="form-check-label ml-4">
                                            <input type="checkbox" class="form-check-input" name="reset_password" value="1">{{ __('reset_password') }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Cancel') }}</button>
                        <input class="btn btn-theme" type="submit" value={{ __('submit') }}>
                    </div>
                </form>
            </div>
        </div>
    </div>

<div class="modal fade" id="addMoreGuardianModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form id="addMoreGuardianForm">
            @csrf
            <input type="hidden" name="user_id" id="more_guardian_user_id">

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Guardian</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body">
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Mobile</label>
                        <input type="text" name="mobile" class="form-control" required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button class="btn btn-theme" type="submit">Save</button>
                </div>
            </div>
        </form>
    </div>
</div>
<div class="modal fade" id="editGuardianModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="editGuardianForm">
            @csrf
            <input type="hidden" name="guardian_id" id="edit_guardian_id">

            <div class="modal-content">
                <div class="modal-header">
                    <h5>Edit Guardian</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body">
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="name" id="edit_guardian_name" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Mobile</label>
                        <input type="text" name="mobile" id="edit_guardian_mobile" class="form-control" required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button class="btn btn-theme" type="submit">Update</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
@section('script')
<script>
    $(document).on('click', '.add-more-guardian', function () {
        let userId = $(this).data('user-id');
        $('#more_guardian_user_id').val(userId);
    });

    $('#addMoreGuardianForm').on('submit', function (e) {
        e.preventDefault();

        $.ajax({
            url: "{{ route('guardian.addNew') }}",
            type: "POST",
            data: $(this).serialize(),
            success: function () {
                $('#addMoreGuardianModal').modal('hide');
                $('#table_list').bootstrapTable('refresh');
            },
            error: function () {
                alert('Failed to add guardian');
            }
        });
    });
</script>
<script>
$(document).on('click', '.edit-guardian', function () {
    let parent = $(this).closest('.guardian-item');

    $('#edit_guardian_id').val(parent.data('id'));
    $('#edit_guardian_name').val(parent.data('name'));
    $('#edit_guardian_mobile').val(parent.data('mobile'));

    $('#editGuardianModal').modal('show');
});
</script>
<script>
$('#editGuardianForm').on('submit', function (e) {
    e.preventDefault();

    $.ajax({
        url: "{{ route('guardian.updateMore') }}",
        type: "POST",
        data: $(this).serialize(),
        success: function () {
            $('#editGuardianModal').modal('hide');
            $('#table_list').bootstrapTable('refresh');
        },
        error: function () {
            alert('Update failed');
        }
    });
});
</script>
@endsection
