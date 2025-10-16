@extends('layouts.app')
@section('title', 'Price List Report')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">Price list Report</h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-md-12">
            @component('components.filters', ['title' => __('report.filters')])
            {!! Form::open(['url' => action([\App\Http\Controllers\ReportController::class, 'getProductValuationReport']), 'method' => 'get', 'id' => 'product_valuation_report_form' ]) !!}

            <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('category_id', 'Category:') !!}
                    {!! Form::select('category_id', $categories, null, ['class' => 'form-control select2','style' => 'width:100%', 'placeholder' => __('messages.please_select'), 'required']); !!}
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('location_id', __('purchase.business_location').':') !!}
                    {!! Form::select('location_id', $business_locations, null, ['class' => 'form-control select2','style' => 'width:100%', 'placeholder' => __('messages.please_select'), 'required']); !!}
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('brand_id', 'Brand:') !!}
                    {!! Form::select('brand_id', $brands, null, ['class' => 'form-control select2','style' => 'width:100%', 'placeholder' => __('messages.please_select'), 'required']); !!}
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">

                    {!! Form::label('product_valuation_date_filter', __('report.date_range') . ':') !!}
                    {!! Form::text('date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'id' => 'product_valuation_date_filter', 'readonly']); !!}
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
                    id="product_valuation_report_table">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Brand</th>
                            <th>Product SKU</th>
                            <th>Product</th>
                            <th>Purchase Price</th>
                            <th>Sale Price</th>
                            <th>Profit</th>
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