@extends('layouts.master')

@section('title')
    {{__('online')}} {{__('fees')}} {{ __('transactions') }} {{__('logs')}}
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                {{__('online')}} {{__('fees')}} {{ __('transactions') }} {{__('logs')}}
            </h3>
        </div>
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card search-container">
                <div class="card">
                    <div class="card-body">

                        <div id="toolbar" class="row">
                            {{-- <div class="form-group col-md-4">
                                <label class="filter-menu" for="filter_payment_status" style="font-size: 0.86rem;width: 110px">
                                    {{ __('Payment Status') }}
                                </label>
                                <select name="filter_payment_status" id="filter_payment_status" class="form-control">
                                    <option value="">{{__('all')}}</option>
                                    <option value="failed">{{__('failed')}}</option>
                                    <option value="succeed">{{__('succeed')}}</option>
                                    <option value="pending">{{__('pending')}}</option>
                                </select>
                            </div> --}}

                            <div class="form-group col-md-3">
                                <label class="filter-menu" for="filter_paid_status"> {{ __('month') }} </label>
                                {!! Form::select('month', $months, date('n'), ['class' => 'form-control paid-month','placeholder' => __('all')]) !!}
                            </div>

                            <div class="form-group col-md-3">
                                <label class="filter-menu" for="session_year_id"> {{ __('Session Years') }} </label>
                                <select name="session_year_id" id="filter_session_year_id" class="form-control">
                                    @foreach ($session_year_all as $session_year)
                                        <option value="{{ $session_year->id }}"
                                            {{ $session_year->default ? 'selected' : '' }}> {{ $session_year->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group col-md-4">
                                <label class="filter-menu" for="search"> {{ __('Search') }} </label>
                                <input type="text" name="search" id="search" class="form-control" placeholder="Search name or amount">
                            </div>
                        </div>


                        <div class="row">
                            {{-- Statistics Cards --}}
                            <div class="col-md-3 stretch-card grid-margin">
                                <div class="card bg-success card-img-holder text-white d-flex align-items-stretch">
                                    <div class="card-body">
                                        <h4 class="font-weight-normal mb-3">Total Collection</h4>
                                        <h2 class="mb-5" id="stat-total-amount">...</h2>
                                        <p class="card-text">Overall Amount Collected</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 stretch-card grid-margin">
                                <div class="card bg-info card-img-holder text-white d-flex align-items-stretch">
                                    <div class="card-body">
                                        <h4 class="font-weight-normal mb-3">Successful Transactions</h4>
                                        <h2 class="mb-5" id="stat-succeed-count">...</h2>
                                        <p class="card-text">Total Successful Payments</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 stretch-card grid-margin">
                                <div class="card bg-primary card-img-holder text-white d-flex align-items-stretch">
                                    <div class="card-body">
                                        <h4 class="font-weight-normal mb-3">Pending Transactions</h4>
                                        <h2 class="mb-5" id="stat-pending-count">...</h2>
                                        <p class="card-text">Total Pending Payments</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 stretch-card grid-margin">
                                <div class="card bg-danger card-img-holder text-white d-flex align-items-stretch">
                                    <div class="card-body">
                                        <h4 class="font-weight-normal mb-3">Failed Transactions</h4>
                                        <h2 class="mb-5" id="stat-failed-count">...</h2>
                                        <p class="card-text">Total Failed Payments</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- <div class="row">
                            <div class="col-md-6 grid-margin stretch-card">
                                <div class="card">
                                    <div class="card-body">
                                        <h4 class="card-title">Transaction Status Distribution</h4>
                                        <canvas id="paymentStatusChart"></canvas>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 grid-margin stretch-card">
                                <div class="card">
                                    <div class="card-body">
                                        <h4 class="card-title">Monthly Collection Trend (In Amount)</h4>
                                        <canvas id="monthlyTrendChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div> --}}

                        <table aria-describedby="mydesc" class='table' id='table_list'
                               data-toggle="table" data-url="{{ route('fees.transactions.log.list', 1) }}"
                               data-click-to-select="true" data-side-pagination="server"
                               data-pagination="true" data-page-list="[5, 10, 20, 50, 100, 200]"
                               data-search="false" data-toolbar="#toolbar" data-show-columns="true"
                               data-show-refresh="true" data-fixed-columns="false" data-fixed-number="3"
                               data-fixed-right-number="1" data-trim-on-search="true"
                               data-mobile-responsive="true" data-sort-name="id"
                               data-sort-order="desc" data-maintain-selected="true" data-export-data-type='all'
                               data-export-options='{ "fileName": "{{__('fees')}}-{{__('transactions')}}-<?= date(' d-m-y') ?>" ,"ignoreColumn":["operate"]}'
                               data-show-export="true" data-query-params="feesPaymentTransactionQueryParams" data-escape="true">
                            <thead>
                            <tr>
                                <th scope="col" data-field="id" data-sortable="false" data-visible="false">{{__('id')}}</th>
                                <th scope="col" data-field="no">{{ __('no.') }}</th>
                                <th scope="col" data-field="full_name" data-align="center" data-searchable="true">{{ __('Guardian')}}</th>
                                <th scope="col" data-field="children" data-align="center" data-searchable="true">{{ __('Children')}}</th>
                                <th scope="col" data-field="amount" data-align="center" data-sortable="true">{{ __('Amount')}}</th>
                                <th scope="col" data-field="payment_gateway" data-align="center" data-formatter="feesTransactionParentGatewayNew">{{ __('Payment Gateway') }}</th>
                                <th scope="col" data-field="payment_status" data-align="center" >{{ __('Transaction Id') }}</th>
                                <th scope="col" data-field="order_id" data-align="center" data-visible="false">{{ __('order_id') }}</th>
                                <th scope="col" data-field="payment_id" data-align="center" data-visible="false">{{ __('payment_id') }}</th>
                                <th scope="col" data-field="date" data-sortable="true" data-visible="true">{{ __('date') }}</th>
                                <th scope="col" data-field="updated_at" data-formatter="dateTimeFormatter" data-sortable="false" data-visible="false">{{ __('updated_at') }}</th>
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> {{-- Make sure Chart.js is included --}}
    <script>
        var monthlyTrendChart; // Global variable for Chart instance
        var paymentStatusChart; // Global variable for Chart instance

        // 1. Function to get query parameters for both table and stats
        function feesPaymentTransactionQueryParams(params) {
            params.payment_status = $('#filter_payment_status').val();
            params.month = $('.paid-month').val();
            params.session_year_id = $('#filter_session_year_id').val();
            params.search = $('#search').val();

            return params;
        }

        $('#search').on('keyup', function () {
            $('#table_list').bootstrapTable('refresh', {silent: true});
            loadFeesStatsAndCharts();
        });

        // 2. Function to fetch and update statistics/charts
        function loadFeesStatsAndCharts() {
            const params = feesPaymentTransactionQueryParams({});

            // Show loading indicators (if you have them)
            // $('#stat-total-amount').text('...');
            // ...

            $.ajax({
                url: "{{ route('fees.transactions.log.stats') }}",
                type: "GET",
                data: params,
                success: function (response) {
                    // Update Statistics Cards
                    $('#stat-total-amount').text('₹' + response.total_amount.toLocaleString()); // Format as currency
                    $('#stat-succeed-count').text(response.total_transactions.toLocaleString());
                    $('#stat-pending-count').text(response.pending_count.toLocaleString());
                    $('#stat-failed-count').text(response.failed_count.toLocaleString());

                    // Update Charts
                    renderPaymentStatusChart(response);
                    renderMonthlyTrendChart(response);
                },
                error: function (e) {
                    console.error("Error fetching fees statistics:", e);
                }
            });
        }

        // 3. Chart Rendering Functions
        function renderPaymentStatusChart(data) {
            const ctx = document.getElementById('paymentStatusChart').getContext('2d');

            // Destroy existing chart if it exists
            if (paymentStatusChart) {
                paymentStatusChart.destroy();
            }

            paymentStatusChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Succeed', 'Pending', 'Failed'],
                    datasets: [{
                        label: 'Transaction Count',
                        data: [data.succeed_count, data.pending_count, data.failed_count],
                        backgroundColor: ['#28a745', '#ffc107', '#dc3545'], // success, warning, danger
                        hoverOffset: 4
                    },
                    {
                        label: 'Amount Collected',
                        data: [data.succeed_amount, data.pending_amount, data.failed_amount],
                        backgroundColor: ['#28a745', '#ffc107', '#dc3545'],
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'Transaction Status Breakdown (Count/Amount)'
                        }
                    }
                }
            });
        }

        function renderMonthlyTrendChart(data) {
            const ctx = document.getElementById('monthlyTrendChart').getContext('2d');

            if (monthlyTrendChart) {
                monthlyTrendChart.destroy();
            }

            const months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
            const trendData = new Array(12).fill(0);

            // Map the fetched data to the full 12-month array
            Object.keys(data.monthly_trend).forEach(monthKey => {
                // monthKey is 1-indexed, array is 0-indexed
                const index = parseInt(monthKey) - 1;
                trendData[index] = data.monthly_trend[monthKey].total_collected_amount;
            });

            monthlyTrendChart = new Chart(ctx, {
                type: 'bar', // Using Bar chart for clarity on collection
                data: {
                    labels: months,
                    datasets: [{
                        label: 'Amount Collected (₹)',
                        data: trendData,
                        backgroundColor: 'rgba(54, 162, 235, 0.5)', // Blue color
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    },
                    plugins: {
                        title: {
                            display: true,
                            text: data.monthly_trend.length > 0 ? 'Monthly Collection Trend' : 'Monthly Trend Disabled (Filter by Month is Active)'
                        }
                    }
                }
            });
        }


        $(document).ready(function () {
            loadFeesStatsAndCharts();

            $('#filter_payment_status, .paid-month, #filter_session_year_id').on('change', function () {
                $('#table_list').bootstrapTable('refresh');

                loadFeesStatsAndCharts();
            });
        });


        function feesTransactionParentGatewayNew(value, row) {
            if (row.payment_gateway == "Stripe") {
                return "<span class='badge badge-primary'>"+window.trans['Stripe']+"</span>";
            } else if (row.payment_gateway == 'Cash') {
                return "<span class='badge badge-success'>"+window.trans['cash']+"</span>";
            } else if (row.payment_gateway == 'Cheque') {
                return "<span class='badge badge-info'>"+window.trans['cheque']+"</span>";
            } else if (row.payment_gateway == 'Razorpay') {
                return "<span class='badge badge-dark'>"+window.trans['Razorpay']+"</span>";
            } else if (row.payment_gateway == 'Flutterwave') {
                return "<span class='badge badge-dark'>"+window.trans['Flutterwave']+"</span>";
            } else {
                return "<span class='badge badge-primary'>"+row.payment_gateway+"</span>";
            }
        }
    </script>
@endsection
