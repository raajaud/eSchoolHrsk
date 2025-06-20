@extends('layouts.master')

@section('title')
    {{ __('Shift') }}
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                {{ __('manage').' '.__('Shift') }}
            </h3>
        </div>
        <div class="row">
            <div class="col-12 col-sm-12 col-md-6 grid-margin">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            {{ __('create').' '.__('new').' '.__('Shift') }}
                        </h4>
                        <form class="pt-3 section-create-form" id="create-form" action="{{route('shift.store')}}" method="POST" novalidate="novalidate">
                            <div class="row">
                                <div class="form-group col-sm-12 col-md-12">
                                    <label for="name">{{ __('name') }} <span class="text-danger">*</span></label>
                                    <input name="name" id="name" type="text" placeholder="{{ __('name') }}" class="form-control" required/>
                                </div>
                                <div class="form-group col-sm-12 col-md-12">
                                    <label for="start_time">{{ __('start_time') }} <span class="text-danger">*</span></label>
                                    <input name="start_time" id="start_time" type="time" class="form-control" required/>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-sm-12 col-md-12">
                                    <label for="end_time">{{ __('end_time') }} <span class="text-danger">*</span></label>
                                    <input name="end_time" id="end_time" type="time" class="form-control" required/>
                                </div>
                            </div>
                            {{-- <input class="btn btn-theme" id="create-btn" type="submit" value={{ __('submit') }}> --}}
                            <input class="btn btn-theme float-right ml-3" id="create-btn" type="submit" value={{ __('submit') }}>
                            <input class="btn btn-secondary float-right" type="reset" value={{ __('reset') }}>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-12 col-md-6 grid-margin">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            {{ __('list').' '.__('Shift') }}
                        </h4>
                        <div class="col-12 text-right">
                            <b><a href="#" class="table-list-type active mr-2" data-id="0">{{__('all')}}</a></b> | <a href="#" class="ml-2 table-list-type" data-id="1">{{__("Trashed")}}</a>
                        </div>
                        <table aria-describedby="mydesc" class='table' id='table_list'
                               data-toggle="table" data-url="{{ http_url('shift/show') }}" data-click-to-select="true" data-side-pagination="server"
                               data-pagination="true" data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true"
                               data-toolbar="#toolbar" data-show-columns="true" data-show-refresh="true"
                               data-fixed-columns="false" data-fixed-number="2" data-fixed-right-number="1"
                               data-trim-on-search="false" data-mobile-responsive="true" data-sort-name="id"
                               data-sort-order="desc" data-maintain-selected="true" data-query-params="queryParams"
                               data-show-export="true"
                               data-export-options='{"fileName": "shift-list-<?= date('d-m-y') ?>","ignoreColumn": ["operate"]}'
                               data-escape="true">
                            <thead>
                            <tr>
                                <th scope="col" data-field="id" data-sortable="true" data-visible="false">{{__('id')}}</th>
                                <th scope="col" data-field="no" data-sortable="false">{{__('no.')}}</th>
                                <th scope="col" data-field="name" data-sortable="false">{{__('name')}}</th>
                                <th scope="col" data-field="start_time" data-sortable="false">{{__('start_time')}}</th>
                                <th scope="col" data-field="end_time" data-sortable="false">{{__('end_time')}}</th>
                                <th scope="col" data-field="status" data-sortable="false" data-formatter="shiftStatusFormatter">{{__('status')}}</th>
                                <th scope="col" data-field="created_at" data-formatter="dateTimeFormatter" data-sortable="true" data-visible="false">{{__('created_at')}}</th>
                                <th scope="col" data-field="updated_at" data-formatter="dateTimeFormatter" data-sortable="true" data-visible="false">{{__('updated_at')}}</th>
                                <th scope="col" data-field="operate" data-sortable="false" data-events="shiftEvents" data-escape="false">{{__('action')}}</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
            <!-- Modal -->
            <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">{{__('edit').' '.__('Shift')}}</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form class="pt-3 section-edit-form" id="edit-form" action="{{ http_url('shifts') }}" novalidate="novalidate">
                            <input type="hidden" name="edit_id" id="edit_id" value=""/>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="form-group col-sm-12 col-md-12">
                                        <label for="edit_name">{{ __('name') }} <span class="text-danger">*</span></label>
                                        <input name="name" id="edit_name" type="text" placeholder="{{ __('name') }}" class="form-control" required/>
                                    </div>
                                    <div class="form-group col-sm-12 col-md-12">
                                        <label for="edit_start_time">{{ __('start_time') }} <span class="text-danger">*</span></label>
                                        <input name="start_time" id="edit_start_time" type="time" class="form-control" required/>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-sm-12 col-md-12">
                                        <label for="edit_end_time">{{ __('end_time') }} <span class="text-danger">*</span></label>
                                        <input name="end_time" id="edit_end_time" type="time" class="form-control" required/>
                                    </div>
                                    <div class="form-group col-sm-12 col-md-12">
                                        <label>{{ __('status') }} <span class="text-danger">*</span></label><br>
                                        <div class="d-flex">
                                            <div class="form-check form-check-inline">
                                                <label class="form-check-label">
                                                    {!! Form::radio('status', '1',false,['id' => 'edit_status_active']) !!}
                                                    {{ __('Active') }}
                                                </label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <label class="form-check-label">
                                                    {!! Form::radio('status', '0',false,['id' => 'edit_status_inactive']) !!}
                                                    {{ __('Inactive') }}
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">{{__('close')}}</button>
                                <input class="btn btn-theme" type="submit" value={{ __('submit') }} />
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
