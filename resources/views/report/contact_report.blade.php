@extends('layouts.app')
@section('title', __('report.customer') . ' ' . __('report.reports'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>{{ __('report.customer')}} {{ __('report.reports')}}</h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section>

<!-- Main content -->
<section class="content">

    <div class="row">
        <div class="col-md-12">
            @component('components.filters', ['title' => __('report.filters')])
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('c_report_location_id', __( 'sale.location' ) . ':') !!}
                        {!! Form::select('c_report_location_id', $business_locations, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'c_report_location_id']); !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('s_contact_id', __( 'report.contact' ) . ':') !!}
                        {!! Form::select('c_contact_id', $contact_dropdown, null , ['class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'c_contact_id', 'placeholder' => __('lang_v1.all')]); !!}
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('c_date_filter', __('report.date_range') . ':') !!}
                        {!! Form::text('c_date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'id' => 'c_date_filter']); !!}
                    </div>
                </div>

            @endcomponent
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            @component('components.widget', ['class' => 'box-primary'])
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="customer_report_tbl">
                    <thead>
                        <tr>
                            <th>@lang('report.contact')</th>
                            <th>Address</th>
                            <th>ID/Passport</th>
                            <th>PEP Type</th>
                            <th>Risk Rating</th>
                            <th>Created Date</th>
                        </tr>
                    </thead>
                    <!--<tfoot>-->
                    <!--    <tr class="bg-gray font-17 footer-total text-center">-->
                    <!--        <td><strong>@lang('sale.total'):</strong></td>-->
                    <!--        <td><span class="display_currency" id="footer_total_purchase" data-currency_symbol ="true"></span></td>-->
                    <!--        <td><span class="display_currency" id="footer_total_purchase_return" data-currency_symbol ="true"></span></td>-->
                    <!--        <td><span class="display_currency" id="footer_total_sell" data-currency_symbol ="true"></span></td>-->
                    <!--        <td><span class="display_currency" id="footer_total_sell_return" data-currency_symbol ="true"></span></td>-->
                    <!--        <td><span class="display_currency" id="footer_total_opening_bal_due" data-currency_symbol ="true"></span></td>-->
                    <!--        <td><span class="display_currency" id="footer_total_due" data-currency_symbol ="true"></span></td>-->
                    <!--    </tr>-->
                    <!--</tfoot>-->
                </table>
            </div>
            @endcomponent
        </div>
    </div>
</section>
<!-- /.content -->

@endsection

@section('javascript')
    <script src="{{ asset('js/report.js?v=' . $asset_v) }}"></script>
@endsection