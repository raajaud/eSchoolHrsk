@extends('layouts.master')

@section('title') {{ __('Due Management') }} @endsection

@section('content')
<div class="content-wrapper">
    <div class="page-header">
        <h3 class="page-title">Fees Due Management</h3>
        <div class="mr-1">
            <a href="{{ route('fees_monthly') }}" class="btn btn-theme">Monthly Fees</a>
            <a href="{{ route('charges_monthly') }}" class="btn btn-theme">Fees Details</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4">
                    <label>Class Section</label>
                    <select id="filter_class_section_id" class="form-control">
                        <option value="">All</option>
                        @foreach($class_sections as $class_section)
                            <option value="{{ $class_section->id }}">{{ $class_section->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label>Minimum Dues (₹)</label>
                    <input type="number" id="min_dues" class="form-control" placeholder="Enter minimum dues">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button id="filterBtnFees" class="btn btn-primary w-100">Filter</button>
                </div>
            </div>
            <div id="stats" class="alert alert-" style="display:none; border: 1px solid black;"></div>

            <table class="table table-bordered table-striped table-responsive" id="dueTable">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAll"></th>
                        <th>SL No</th>
                        <th>Id</th>
                        <th>Guardian</th>
                        <th>Mobile</th>
                        <th>Student(s)</th>
                        <th>Class</th>
                        <th class="sortable" data-field="dues" data-order="desc">Total Dues (₹) ⬍</th>
                        <th class="sortable" data-field="amount" data-order="desc">Last Payment ⬍</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>

            <div class="mt-3 text-end">
                <button id="sendWhatsapp" class="btn btn-success">Send WhatsApp</button>
                <button id="printSlips" class="btn btn-secondary">Print Due Slips</button>
            </div>
        </div>
    </div>
</div>

@endsection
