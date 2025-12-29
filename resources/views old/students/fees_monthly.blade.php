@extends('layouts.master')

@section('title') Monthly Fee Management @endsection

@section('content')
<div class="content-wrapper">

    <!-- PAGE HEADER -->
    <div class="page-header">
        <h3 class="page-title">Monthly Fees Management</h3>
        <div class="mr-1">
            <a href="{{ route('students.dueManagement') }}" class="btn btn-theme">Due Management</a>
        </div>
    </div>

    <!-- MAIN CARD -->
    <div class="card">
        <div class="card-body">

            <!-- FILTERS -->
            <form method="GET" action="{{ url('fees-monthly') }}" class="row mb-3">
                <div class="col-md-4">
                    <label>Class Section</label>
                    <select name="class_id" class="form-control">
                        <option value="">All</option>
                        @foreach($class_sections as $class_section)
                            <option value="{{ $class_section->id }}"
                                {{ isset($classId) && $classId == $class_section->id ? 'selected' : '' }}>
                                {{ $class_section->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-primary w-100">Filter</button>
                </div>
            </form>

            <!-- TABLE -->
            <form method="POST" action="{{ route('fees_monthly_save') }}">
                @csrf

                <table class="table table-bordered table-striped table-responsive">
                    <thead>
                        <tr>
                            <th>Guardian</th>
                            <th>Student(s)</th>
                            <th>Class</th>
                            <th>Monthly Fee (₹)</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($guardians as $guardian)

                            @php
                                $count = $guardian->child->count();
                                $student = '';
                                $class = '';
                                foreach ($guardian->child as $key => $child) {
                                    $student .= $child->user->full_name;
                                    $class .= $child->class_section->class->name ?? '-';

                                    if ($count > 1 && $key < $count - 2) {
                                        $student .= ', ';
                                        $class .= ', ';
                                    } elseif ($count > 1 && $key == $count - 2) {
                                        $student .= ' & ';
                                        $class .= ' & ';
                                    }
                                }

                                if (!$student) continue;

                                $father = $guardian->full_name ?? '-';
                            @endphp

                            <tr>
                                <td>{{ $father }}</td>
                                <td>{{ $student }}</td>
                                <td>{{ $class }}</td>
                                <td>
                                    <input type="number"
                                           name="fees[{{ $guardian->id }}]"
                                           class="form-control"
                                           value="{{ $guardian->monthly_fees ?? 0 }}"
                                           style="width: 85px;">
                                </td>
                            </tr>

                        @empty
                            <tr>
                                <td colspan="4" class="text-center">No data found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                @if(count($guardians) > 0)
                    <div class="text-end">
                        <button class="btn btn-success">Save Monthly Fees</button>
                    </div>
                @endif

            </form>
        </div>
    </div>
</div>
@endsection
