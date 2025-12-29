@extends('layouts.master')

@section('title') Monthly User Charges @endsection

@section('content')
<div class="content-wrapper">

    <div class="page-header">
        <h3 class="page-title">Monthly User Charges</h3>
        <button type="button"
        class="btn btn-success mb-3"
        data-toggle="modal"
        data-target="#addFeesModal">
    + Add Fees
</button>
    </div>

    <div class="card">
        <div class="card-body">

            {{-- Month Filter --}}
            <form method="GET" action="{{ route('charges_monthly') }}" class="row mb-4">

                <div class="col-md-3">
                    <label><strong>Select Month</strong></label>
                    <select name="month" class="form-control" onchange="this.form.submit()">
                        @foreach($months as $m)
                            <option value="{{ $m['value'] }}" {{ $selectedMonth == $m['value'] ? 'selected' : '' }}>
                                {{ $m['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

            </form>

            {{-- Table --}}
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Guardian</th>
                        <th>Student(s)</th>
                        <th>Class</th>
                        <th>Charge Type</th>
                        <th>Amount (₹)</th>
                        <th>Description</th>
                        <th>Charge Date</th>
                    </tr>
                </thead>

                <tbody>
                    <form action="{{ route('feesUpdateAmount') }}" method="POST">
                        @csrf

                        @forelse($charges as $charge)

                            @php
                                $guardian = $charge->user;
                                $children = $guardian->child;

                                $studentNames = $children->pluck('user.full_name')->join(', ');
                                $classNames   = $children->map(fn($c) => $c->class_section->class->name ?? '-')->join(', ');
                            @endphp

                            <tr>
                                <td>{{ $guardian->full_name }}</td>
                                <td>{{ $studentNames }}</td>
                                <td>{{ $classNames }}</td>
                                <td>{{ ucfirst($charge->charge_type) }}</td>

                                <td>
                                    ₹
                                    <input type="text"
                                        name="amount[{{ $charge->id }}]"
                                        value="{{ number_format($charge->amount, 2, '.', '') }}"
                                        class="form-control form-control-sm"
                                        style="width:120px;">
                                </td>

                                <td>{{ $charge->description }}</td>
                                <td>{{ \Carbon\Carbon::parse($charge->charge_date)->format('d M Y') }}</td>

                            </tr>

                        @empty
                            <tr>
                                <td colspan="8" class="text-center">No charges found for this month.</td>
                            </tr>
                        @endforelse

                        <tr>
                            <td colspan="8" class="text-end">
                                <button type="submit" class="btn btn-primary btn-sm">Save All</button>
                            </td>
                        </tr>
                    </form>
                </tbody>
            </table>

        </div>
    </div>
</div>

<div class="modal fade" id="addFeesModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('charges.store') }}">
            @csrf

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Monthly Fees</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">

                    {{-- Guardian --}}
                    <div class="mb-3">
                        <label class="form-label">Guardian</label>
                        <select name="user_id[]"
                                id="guardianSelect"
                                class="form-control select2-dropdown"
                                multiple
                                required>
                            <option value="all">All Guardians</option>
                            @foreach($guardians as $guardian)
                                <option value="{{ $guardian->id }}"
                                        data-fee="{{ $guardian->monthly_fees }}"
                                        data-other="{{ $guardian->other_payments ?? 0 }}">
                                    {{ $guardian->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>


                    {{-- Charge Type --}}
                    <div class="mb-3">
                        <label class="form-label">Charge Type</label>
                        <select name="charge_type" class="form-control" required>
                            <option value="monthly_fees">Monthly Fees</option>
                            <option value="uniforms">Uniforms</option>
                            <option value="books">Books</option>
                            <option value="other_payments">Other</option>
                        </select>
                    </div>

                    {{-- Amount --}}
                    <div class="mb-3">
                        <label class="form-label">Amount (₹)</label>
                        <input type="number" step="100" name="amount" id="amountInput"
                               class="form-control" required>
                    </div>

                    {{-- Description --}}
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <input type="text" name="description"
                               value="{{ $selectedMonth }}"
                               class="form-control" required>
                    </div>

                    {{-- Charge Date --}}
                    <div class="mb-3">
                        <label class="form-label">Charge Date</label>
                        <input type="date" name="charge_date"
                               value="{{ now()->toDateString() }}"
                               class="form-control" required>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save Fees</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // Open modal
    // document.getElementById('openAddFeesModal').addEventListener('click', function () {
    //     let modal = new bootstrap.Modal(document.getElementById('addFeesModal'));
    //     modal.show();
    // });

    // Auto-fill amount
    document.getElementById('guardianSelect').addEventListener('change', function () {
        const fee = this.options[this.selectedIndex].dataset.fee || 0;
        document.getElementById('amountInput').value = fee;
    });

});
</script>
<script>
$('#addFeesModal').on('shown.bs.modal', function () {

    $('#guardianSelect').select2({
        dropdownParent: $('#addFeesModal'),
        placeholder: 'Select Guardian(s)',
        width: '100%'
    });
});

// Guardian selection → amount autofill
$(document).on('change', '#guardianSelect', function () {

    let selected = $(this).find(':selected');

    // If ALL selected → clear amount
    if (selected.filter('[value="all"]').length) {
        $('#amountInput').val('');
        return;
    }

    // Sum monthly fees
    let total = 0;
    selected.each(function () {
        total += parseFloat($(this).data('fee') || 0);
    });

    $('#amountInput').val(total);
});

</script>
@endsection
