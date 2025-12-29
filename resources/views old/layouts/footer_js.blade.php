<script src="{{ asset('/assets/js/Chart.min.js') }}"></script>
<script src="{{ asset('/assets/js/jquery.validate.min.js') }}"></script>
<script src="{{ asset('/assets/jquery-toast-plugin/jquery.toast.min.js') }}"></script>
<script src="{{ asset('/assets/select2/select2.min.js') }}"></script>

<script src="{{ asset('/assets/js/off-canvas.js') }}"></script>
<script src="{{ asset('/assets/js/hoverable-collapse.js') }}"></script>
<script src="{{ asset('/assets/js/misc.js') }}"></script>
<script src="{{ asset('/assets/js/settings.js') }}"></script>
<script src="{{ asset('/assets/js/todolist.js') }}"></script>
<script src="{{ asset('/assets/js/ekko-lightbox.min.js') }}"></script>
<script src="{{ asset('/assets/js/jquery.tagsinput.min.js') }}"></script>

<script src="{{ asset('/assets/js/apexcharts.js') }}"></script>




{{--<script src="{{ asset('/assets/bootstrap-table/bootstrap-table.min.js') }}"></script>--}}

<script src="https://unpkg.com/bootstrap-table@1.22.1/dist/bootstrap-table.min.js"></script>
<script src="{{ asset('/assets/bootstrap-table/bootstrap-table-mobile.js') }}"></script>
<script src="{{ asset('/assets/bootstrap-table/bootstrap-table-export.min.js') }}"></script>
<script src="{{ asset('/assets/bootstrap-table/fixed-columns.min.js') }}"></script>
<script src="{{ asset('/assets/bootstrap-table/tableExport.min.js') }}"></script>
<script src="{{ asset('/assets/bootstrap-table/jspdf.min.js') }}"></script>
<script src="{{ asset('/assets/bootstrap-table/jspdf.plugin.autotable.js') }}"></script>
<script src="{{ asset('/assets/bootstrap-table/reorder-rows.min.js') }}"></script>
<script src="{{ asset('/assets/bootstrap-table/jquery.tablednd.min.js') }}"></script>
<script src="{{ asset('/assets/bootstrap-table/loadash.min.js') }}"></script>

<script src="{{ asset('/assets/js/jquery.cookie.js') }}"></script>
<script src="{{ asset('/assets/js/sweetalert2.all.min.js') }}"></script>
<script src="{{ asset('/assets/js/momentjs.js') }}"></script>
<script src="{{ asset('/assets/js/datepicker.min.js') }}"></script>
<script src="{{ asset('/assets/js/daterangepicker.js') }}"></script>
<script src="{{ asset('/assets/js/jquery.repeater.js') }}"></script>
<script src="{{ asset('/assets/tinymce/tinymce.min.js') }}"></script>

<script src="{{ asset('/assets/color-picker/jquery-asColor.min.js') }}"></script>
<script src="{{ asset('/assets/color-picker/color.min.js') }}"></script>

<script src="{{ asset('/assets/js/custom/validate.js') }}"></script>
<script src="{{ asset('/assets/js/jquery-additional-methods.min.js')}}"></script>
<script src="{{ asset('/assets/js/custom/function.js') }}"></script>
<script src="{{ asset('/assets/js/custom/common.js') }}"></script>
<script src="{{ asset('/assets/js/custom/custom.js') }}"></script>
<script src="{{ asset('/assets/js/custom/bootstrap-table/actionEvents.js') }}"></script>
<script src="{{ asset('/assets/js/custom/bootstrap-table/formatter.js') }}"></script>
<script src="{{ asset('/assets/js/custom/bootstrap-table/queryParams.js') }}"></script>

<script src="{{ asset('/assets/ckeditor-4/ckeditor.js') }}"></script>
<script src="{{ asset('/assets/ckeditor-4/adapters/jquery.js') }}" async></script>


<script type='text/javascript'>
    @if ($errors->any())
    @foreach ($errors->all() as $error)
    $.toast({
        text: '{{ $error }}',
        showHideTransition: 'slide',
        icon: 'error',
        loaderBg: '#f2a654',
        position: 'top-right'
    });
    @endforeach
    @endif

    @if (Session::has('success'))
    $.toast({
        text: '{{ Session::get('success') }}',
        showHideTransition: 'slide',
        icon: 'success',
        loaderBg: '#f96868',
        position: 'top-right'
    });
    @endif

    @if (Session::has('error'))
    $.toast({
        text: '{{ Session::get('error') }}',
        showHideTransition: 'slide',
        icon: 'error',
        loaderBg: '#f2a654',
        position: 'top-right'
    });
    @endif
</script>
<script>
    const please_wait = "{{__('Please wait')}}"
    const processing_your_request = "{{__('Processing your request')}}"
    let date_format = '{{ $schoolSettings['date_format'] ?? $systemSettings['date_format'] ?? "d-m-Y" }}'.replace('Y', 'YYYY').replace('m', 'MM').replace('d', 'DD');

    let date_time_format = '{{ $schoolSettings['date_format'] ?? $systemSettings['date_format'] ?? "d-m-Y" }} {{ $schoolSettings['time_format'] ?? $systemSettings['time_format'] ?? "h:i A" }}'.replace('Y', 'YYYY').replace('m', 'MM').replace('d', 'DD').replace('h', 'hh').replace('H', 'HH').replace('i', 'mm').replace('a', 'a').replace('A', 'A');

    let time_format = '{{ $schoolSettings['time_format'] ?? $systemSettings['time_format'] ?? "h:i A" }}'.replace('h', 'hh').replace('H', 'HH').replace('i', 'mm').replace('a', 'a').replace('A', 'A');

    setTimeout(() => {

        $(document).ready(function() {
            var targetNode = document.querySelector('thead');

            // Apply initial styles
            $('th[data-field="operate"]').addClass('action-column');

            // Create an observer instance linked to the callback function
            var observer = new MutationObserver(function(mutationsList, observer) {
                for (var mutation of mutationsList) {
                    if (mutation.type === 'childList') {
                        // Reapply the class when changes are detected
                        $('th[data-field="operate"]').addClass('action-column');
                    }
                }
            });

            // Start observing the target node for configured mutations
            observer.observe(targetNode, { childList: true, subtree: true });
        });

    }, 500);


    // razorpay-payment-button
    setTimeout(() => {
        $('.razorpay-payment-button').addClass('btn btn-info');
    }, 100);



    // document.addEventListener("DOMContentLoaded", function () {
    //     var isMobile = window.matchMedia("only screen and (max-width: 768px)").matches;
    //     var table = document.getElementsByClassName('reorder-table-row');

    //     if (table) {
    //         if (isMobile) {
    //             table.removeAttribute('data-reorderable-rows');
    //         } else {
    //             table.setAttribute('data-reorderable-rows', 'true');
    //         }
    //     }
    //     // Initialize the table
    //     $('.reorder-table-row').bootstrapTable();
    // });



    document.addEventListener("DOMContentLoaded", function() {
        // Add the event listener for the button to initiate the payment
        setTimeout(() => {

            $('#razorpay-button').click(function (e) {
                e.preventDefault();
                let baseUrl = window.location.origin;
                var order_id = '';
                var paymentTransactionId = '';

                $.ajax({
                    type: "post",
                    url: baseUrl + '/subscriptions/create/razorpay/order-id',
                    data: {
                        amount: $('.bill_amount').val() * 100, // Amount is in currency subunits. Default currency is INR. Hence, 100 refers to 1 INR
                        currency : "{{ $system_settings['currency_code'] ?? 'INR' }}",

                        type : $('.type').val(),
                        package_type : $('.package_type').val(),
                        package_id : $('.package_id').val(),
                        upcoming_plan_type : $('.upcoming_plan_type').val(),
                        subscription_id : $('.subscription_id').val(),
                        feature_id : $('.feature_id').val(),
                        end_date : $('.end_date').val(),

                    },
                    success: function (response) {
                        console.log(response.data);
                        if (response.data) {
                            order_id = response.data.order.id;
                            paymentTransactionId = response.data.paymentTransaction.id;

                            var options = {
                                "key": "{{ $paymentConfiguration->api_key ?? '' }}", // Enter the Key ID generated from the Dashboard
                                "amount": $('.bill_amount').val() * 100, // Amount is in currency subunits. Default currency is INR. Hence, 100 refers to 1 INR
                                "currency": "{{ $system_settings['currency_code'] ?? 'INR' }}",
                                "name": "{{ $system_settings['system_name'] ?? 'eSchool-Saas' }}",
                                "description": "Razorpay",
                                "order_id": order_id,
                                "handler": function(response) {
                                    // Set the response data in the form
                                    $('.razorpay_payment_id').val(response.razorpay_payment_id);
                                    $('.razorpay_signature').val(response.razorpay_signature);
                                    $('.razorpay_order_id').val(response.razorpay_order_id);
                                    $('.paymentTransactionId').val(paymentTransactionId);

                                    // Submit the form
                                    document.querySelector('.razorpay-form').submit();
                                }
                            };

                            var rzp1 = new Razorpay(options);
                            rzp1.open();
                        } else {
                            Swal.fire({icon: 'error', text: response.message});
                        }
                    }

                });


            });

        }, 100);

    });

</script>

{{-- Search sidebar menu --}}
<script>
    $(document).ready(function() {
        $("#menu-search, #menu-search-mini").on("keyup", function() {
            var value = $(this).val().trim();

            // if (value.length < 2) {
            //     $("#search-results").empty();
            //     return;
            // }

            $.ajax({
                url: "{{ route('guardian.search_ajax') }}",
                method: "GET",
                data: { query: value },
                beforeSend: function() {
                    $("#search-results").html("<p style='padding:8px 10px;'>Searching...</p>");
                },
                success: function(response) {
                    $("#search-results").html(response);
                },
                error: function() {
                    $("#search-results").html("<p style='padding:8px 10px;'>Error fetching results.</p>");
                }
            });
        });
    });

    $('.navbar-toggler').click(function (e) {
        e.preventDefault();

        var updatedClasses = $('body').hasClass('sidebar-icon-only');

        if (!updatedClasses) {
            $('.menu-search').addClass('d-none');
        } else {
            $('.menu-search').removeClass('d-none');
        }
    });

</script>
<script>
$('#filterBtnFees').click(function() {
    let class_section_id = $('#filter_class_section_id').val();
    let min_dues = $('#min_dues').val();

    $.get('{{ route("students.getDueData") }}', { class_section_id, min_dues }, function(res) {
        if(res.error){ alert(res.message); return; }

        window._guardiansData = res.guardians; // store for sorting
        renderTable(res.guardians);

        // Enhanced stats display
        const avgDue = (res.stats.overall_dues / res.stats.total_guardians || 0).toFixed(0);
        $('#stats').show().html(`
            <div class="row text-center">
                <div class="col-md-3"><b>Total Guardians</b><br>${res.stats.total_guardians}</div>
                <div class="col-md-3"><b>Overall Dues</b><br>₹${res.stats.overall_dues.toLocaleString()}</div>
                <div class="col-md-3"><b>Average Due</b><br>₹${Number(avgDue).toLocaleString()}</div>
                <div class="col-md-3"><b>Max Due</b><br>${res.stats.max_due_guardian} (₹${res.stats.max_due_amount.toLocaleString()})</div>
            </div>
        `);

        if (res.stats.month_wise?.length) {
            const months = res.stats.month_wise.map(m => m.month).reverse();
            const totals = res.stats.month_wise.map(m => m.total).reverse();

            const chartEl = $('<div id="duesChart" class="mt-3"></div>');
            $('#stats').append(chartEl);

            const chart = new ApexCharts(document.querySelector('#duesChart'), {
                chart: { type: 'bar', height: 180 },
                series: [{ name: 'Collection', data: totals }],
                xaxis: { categories: months },
                title: {
                    text: 'Last 6 Months Collection',
                    align: 'center',
                    style: { fontSize: '13px' }
                },
                dataLabels: { enabled: false }
            });

            chart.render();
        }
    });
});

// function renderTable(data) {
//     let tbody = '';
//     data.forEach(g => {
//         const lastPayment = g.last_payment?.amount ?
//             `₹${g.last_payment.amount} <small>(${g.last_payment.date})</small>` : '-';
//         tbody += `<tr>
//             <td><input type="checkbox" class="selectRow" value="${g.id}"></td>
//             <td>${g.id}</td>
//             <td>${g.full_name}</td>
//             <td>${g.mobile || '-'}</td>
//             <td>${g.child.map(c=>c.user.full_name).join(', ')}</td>
//             <td>${g.child.map(c=>c.class_section.class.name).join(', ')}</td>
//             <td data-dues="${g.total_dues}">₹${g.total_dues.toLocaleString()}</td>
//             <td data-amount="${g.last_payment?.amount || 0}" data-date="${g.last_payment?.date || ''}">${lastPayment}</td>
//         </tr>`;
//     });
//     $('#dueTable tbody').html(tbody);
// }
function renderTable(data) {
    let tbody = '';
    data.forEach((g, i) => {
        const lastPayment = g.last_payment?.amount
            ? `₹${g.last_payment.amount} <small>(${g.last_payment.date})</small>`
            : '-';

        tbody += `<tr>
            <td><input type="checkbox" class="selectRow" value="${g.id}"></td>
            <td>${i + 1}</td>
            <td>${g.id}</td>
            <td>${g.full_name}</td>
            <td>${g.mobile || '-'}</td>
            <td>${g.child.map(c => c.user.full_name).join(', ')}</td>
            <td>${g.child.map(c => c.class_section.class.name).join(', ')}</td>
            <td data-dues="${g.total_dues}">₹${g.total_dues.toLocaleString()}</td>
            <td data-amount="${g.last_payment?.amount || 0}" data-date="${g.last_payment?.date || ''}">
                ${lastPayment}
            </td>
        </tr>`;
    });

    $('#dueTable tbody').html(tbody);
}

$(document).on("change", "#selectAll", function () {
    $(".selectRow").prop("checked", $(this).prop("checked"));
});

// Sorting feature
$(document).on('click', 'th.sortable', function() {
    const field = $(this).data('field');
    const order = $(this).data('order') === 'asc' ? 'desc' : 'asc';
    $(this).data('order', order);

    const sorted = [...window._guardiansData].sort((a,b)=>{
        let valA, valB;
        if(field === 'dues'){ valA = a.total_dues; valB = b.total_dues; }
        if(field === 'amount'){ valA = a.last_payment?.amount || 0; valB = b.last_payment?.amount || 0; }
        if(field === 'date'){ valA = new Date(a.last_payment?.date || 0); valB = new Date(b.last_payment?.date || 0); }
        return order === 'asc' ? valA - valB : valB - valA;
    });
    renderTable(sorted);
});


// $('#sendWhatsapp').click(() => {
//     const guardian_ids = $('#dueTable tbody tr').map(function() {
//         return $(this).find('td').eq(0).text();
//     }).get();

//     $('<form>', {
//         action: '{{ route("students.sendFilteredWhatsapp") }}',
//         method: 'POST'
//     })
//     .append($('<input>', { type:'hidden', name:'_token', value:'{{ csrf_token() }}' }))
//     .append($('<input>', { type:'hidden', name:'guardian_ids', value: JSON.stringify(guardian_ids) }))
//     .appendTo('body').submit();

// });

// $('#printSlips').click(() => {
//     const guardian_ids = $('#dueTable tbody tr').map(function() {
//         return $(this).find('td').eq(0).text();
//     }).get();

//     $('<form>', {
//         action: '{{ route("students.printFilteredDueSlips") }}',
//         method: 'POST'
//     })
//     .append($('<input>', { type:'hidden', name:'_token', value:'{{ csrf_token() }}' }))
//     .append($('<input>', { type:'hidden', name:'guardian_ids', value: JSON.stringify(guardian_ids) }))
//     .appendTo('body').submit();
// });

$('#sendWhatsapp').click(() => {

    const guardian_ids = $('.selectRow:checked').map(function() {
        return $(this).val();
    }).get();

    if (guardian_ids.length === 0) {
        alert("Please select at least one guardian");
        return;
    }

    $('<form>', {
        action: '{{ route("students.sendFilteredWhatsapp") }}',
        method: 'POST'
    })
    .append($('<input>', { type:'hidden', name:'_token', value:'{{ csrf_token() }}' }))
    .append($('<input>', { type:'hidden', name:'guardian_ids', value: JSON.stringify(guardian_ids) }))
    .appendTo('body').submit();
});

$('#printSlips').click(() => {

    const guardian_ids = $('.selectRow:checked').map(function() {
        return $(this).val();
    }).get();

    if (guardian_ids.length === 0) {
        alert("Please select at least one guardian");
        return;
    }

    $('<form>', {
        action: '{{ route("students.printFilteredDueSlips") }}',
        method: 'POST'
    })
    .append($('<input>', { type:'hidden', name:'_token', value:'{{ csrf_token() }}' }))
    .append($('<input>', { type:'hidden', name:'guardian_ids', value: JSON.stringify(guardian_ids) }))
    .appendTo('body').submit();
});
</script>
