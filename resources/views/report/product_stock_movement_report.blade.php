@extends('layouts.app')
@section('title', 'Product Stock Movement Report')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">Product Stock Movement Report</h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            @component('components.filters', ['title' => __('report.filters')])
            {!! Form::open(['url' => action([\App\Http\Controllers\ReportController::class, 'getProductStockMovementReport']), 'method' => 'get', 'id' => 'product_stock_movement_report_form' ]) !!}

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('category_id', 'Category:') !!}
                        {!! Form::select('category_id', $categories, null, [
                        'class' => 'form-control select2',
                        'placeholder' => __('messages.please_select'),
                        'required',
                        'style' => 'width:100%'
                        ]); !!}
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('location_id', __('purchase.business_location').':') !!}
                        {!! Form::select('location_id', $business_locations, null, [
                        'class' => 'form-control select2',
                        'placeholder' => __('messages.please_select'),
                        'required',
                        'style' => 'width:100%'
                        ]); !!}
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('product_stock_movement_date_filter', __('report.date_range') . ':') !!}
                        {!! Form::text('date_range', null, [
                        'placeholder' => __('lang_v1.select_a_date_range'),
                        'class' => 'form-control',
                        'id' => 'product_stock_movement_date_filter',
                        'readonly'
                        ]); !!}
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('balance_status', 'Balance Status:') !!}
                        {!! Form::select('balance_status', [
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
                    id="product_stock_movement_report_table">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Product</th>
                            <th>Product Ref</th>
                            <th>Opening Stock</th>
                            <th>Purchase Stock</th>
                            <th>Sold Stock</th>
                            <th>Balance</th>
                        </tr>
                    </thead>

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