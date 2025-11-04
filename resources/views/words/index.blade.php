@extends('layouts.master')

@section('title', __('Daily Words'))

@section('content')
<div class="content-wrapper">
    <div class="page-header">
        <h3 class="page-title">{{ __('Daily Words List') }}</h3>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="card-title">{{ __('All Daily Words') }}</h4>
                <button class="btn btn-primary" data-toggle="modal" data-target="#addWordModal">
                    + Add New Word
                </button>
            </div>

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>English Word</th>
                        <th>Pronunciation</th>
                        <th>Hindi Word</th>
                        <th>Hindi Meaning</th>
                        <th>Publish Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($words as $index => $word)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $word->english_word }}</td>
                            <td>{{ $word->pronunciation ?? '-' }}</td>
                            <td>{{ $word->hindi_word }}</td>
                            <td>{{ $word->hindi_meaning ?? '-' }}</td>
                            <td>{{ $word->publish_date ? \Carbon\Carbon::parse($word->publish_date)->format('d-m-Y') : '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">No words added yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="addWordModal" tabindex="-1" aria-labelledby="addWordModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('word.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addWordModalLabel">Add New Word</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="form-group">
                        <label>English Word</label>
                        <input type="text" name="english_word" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Pronunciation</label>
                        <input type="text" name="pronunciation" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Hindi Word</label>
                        <input type="text" name="hindi_word" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Hindi Meaning</label>
                        <input type="text" name="hindi_meaning" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Publish Date</label>
                        <input type="date" name="publish_date" class="form-control">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Save Word</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
