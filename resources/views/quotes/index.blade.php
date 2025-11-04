@extends('layouts.master')

@section('title')
    {{ __('students') }}
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">{{ __('Quotes List') }}</h3>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="card-title">{{ __('All Quotes') }}</h4>
                    <button class="btn btn-primary" data-toggle="modal" data-target="#addQuoteModal">
                        + Add New Quote
                    </button>
                </div>

                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Quote</th>
                            <th>Author</th>
                            <th>Published</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($quotes as $index => $quote)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $quote->quote }}</td>
                                <td>{{ $quote->author }}</td>
                                <td>{{ $quote->published ? 'Yes' : 'No' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="addQuoteModal" tabindex="-1" aria-labelledby="addQuoteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('thought.store') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addQuoteModalLabel">Add New Quote</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Quote</label>
                            <textarea name="quote" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="form-group">
                            <label>Author</label>
                            <input type="text" name="author" class="form-control" required>
                        </div>
                        <div class="form-group form-check">
                            <input type="checkbox" class="form-check-input" name="published" value="1" id="published">
                            <label class="form-check-label" for="published">Publish Now</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Save Quote</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

@endsection
