@extends('layouts.app')
@section('title', 'Customer Sale Ledger Report')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">Customer Sale Ledger Report</h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            @component('components.filters', ['title' => __('report.filters')])
          {!! Form::open(['url' => action([\App\Http\Controllers\ReportController::class, 'getCustomerSaleLedgerReport']), 'method' => 'get', 'id' => 'customer_sale_ledger_report_form' ]) !!}
            
            <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('location_id', __('purchase.business_location').':') !!}
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-map-marker"></i>
                        </span>
                        {!! Form::select('location_id', $business_locations, null, ['class' => 'form-control select2','style' => 'width:100%', 'placeholder' => __('messages.please_select'), 'required']); !!}
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">

                    {!! Form::label('customer_sale_ledger_date_filter', __('report.date_range') . ':') !!}
                    {!! Form::text('date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'id' => 'customer_sale_ledger_date_filter', 'readonly']); !!}
                </div>
            </div>
            <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('balance_csl_status', 'Balance Status:') !!}
                        {!! Form::select('balance_csl_status', [
                        '' => __('messages.please_select'),
                        'negative' => 'Negative Balance',
                        'positive' => 'Positive Balance',
                        'zero' => 'Zero Balance',
                        ], null, [
                        'class' => 'form-control select2',
                        'style' => 'width:100%'
                        ]); !!}
                    </div>
                </div>
            {!! Form::close() !!}
            @endcomponent
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            @component('components.widget', ['class' => 'box-primary'])
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" 
                    id="customer_sale_ledger_report_table">
                        <thead>
                            <tr>
                                <th>Sr.</th>
                                <th>Customer Name</th>
                                <th>Total Sales</th>
                                <th>Total Paid</th>
                                <th>Balance Due</th>
                            </tr>
                        </thead>
                        <tfoot>
                        <tr class="bg-gray font-17 footer-total text-center">
                            <td colspan="2"><strong>Total:</strong></td>
                            <td class="total_sales_value"></td>
                            <td class="total_paid_value"></td>
                            <td class="total_balance_value"></td>
                        </tr>
                    </tfoot>
                    </table>
                </div>
            @endcomponent
        </div>
    </div>
</section>
<!-- /.content -->
<div class="modal fade view_register" tabindex="-1" role="dialog" 
    aria-labelledby="gridSystemModalLabel">
</div>

@endsection

@section('javascript')
    <script src="{{ asset('js/report.js?v=' . $asset_v) }}"></script>
@endsection