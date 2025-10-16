@extends('layouts.app')
@section('title','Sale Agent Commission Report')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">Sale Agent Commission Report</h1>
</section>

<!-- Main content -->
<section class="content no-print">
    <div class="row">
        <div class="col-md-12">
           @component('components.filters', ['title' => __('report.filters')])
              {!! Form::open(['url' => '#', 'method' => 'get', 'id' => 'agent_commission_report_form' ]) !!}
                
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('location_id', __('purchase.business_location').':') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-map-marker"></i>
                            </span>
                            {!! Form::select('location_id', $business_locations, null, ['class' => 'form-control select2', 'placeholder' => __('messages.all'), 'required','style' => 'width: 100%;']); !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">

                        {!! Form::label('spr_date_filter', __('report.date_range') . ':') !!}
                        {!! Form::text('date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'id' => 'acr_date_filter', 'readonly']); !!}
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
                    id="agent_commission_report_table">
                        <thead>
                            <tr>
                                <th>&nbsp;</th>
                                <th>Agent Name</th>
                                <th>Agent Email</th>
                                <th>Agent Contact</th>
                                <th>Total Sale (Before Tax)</th>
                                <th>Total Sale</th>
                                <th>Individual Commission</th>
                                <th>Global Commission</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr class="bg-gray font-17 footer-total text-center">
                                <td colspan="4"><strong>Total:</strong></td>
                                <td><span class="display_currency" id="footer_total_before_tax" data-currency_symbol ="true"></span></td>
                                <td><span class="display_currency" id="footer_total_sale" data-currency_symbol ="true"></span></td>
                                <td><span class="display_currency" id="footer_total_individual" data-currency_symbol ="true"></span></td>
                                <td><span class="display_currency" id="footer_total_global" data-currency_symbol ="true"></span></td>
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
    <script src="{{ asset('js/payment.js?v=' . $asset_v) }}"></script>
@endsection