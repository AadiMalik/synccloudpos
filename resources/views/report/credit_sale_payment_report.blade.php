@extends('layouts.app')
@section('title', 'Credit Sale Payment Report')
@section('css')
<style>
      @media print {
            @page {
                  size: A4;
                  margin: 20mm;
                  /* adjust margins if needed */
            }

            html,
            body {
                  height: auto !important;
                  overflow: visible !important;
            }

            .content,
            .content-wrapper,
            .wrapper,
            .row {
                  height: auto !important;
                  overflow: visible !important;
            }

            /* Hide unwanted elements */
            .no-print {
                  display: none !important;
            }

            /* Ensure tables split properly across pages */
            table {
                  page-break-inside: auto;
            }

            tr {
                  page-break-inside: avoid;
                  page-break-after: auto;
            }

            thead {
                  display: table-header-group;
                  /* Repeat table header on each page */
            }

            tfoot {
                  display: table-footer-group;
                  /* Repeat table footer on each page */
            }

            /* Remove padding/margins that break layout */
            .content,
            .row,
            .col-xs-6,
            .col-sm-12 {
                  float: none !important;
                  width: 100% !important;
            }
      }
</style>
@endsection

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
      <h1 class="tw-text-xl md:tw-text-3xl tw-font-bold tw-text-black">Credit Sale Payment Report
            <small class="tw-text-sm md:tw-text-base tw-text-gray-700 tw-font-semibold">credit sale payment for the selected date range</small>
      </h1>
</section>

<!-- Main content -->
<section class="content">
      <div class="print_section">
            <h2>{{session()->get('business.name')}}</h2>
      </div>
      <div class="row no-print">
            <div class="col-md-3 col-md-offset-7 col-xs-6">
                  <div class="input-group">
                        <span class="input-group-addon bg-light-blue"><i class="fa fa-map-marker"></i></span>
                        <select class="form-control select2" id="credit_sale_payment_location_filter">
                              @foreach($business_locations as $key => $value)
                              <option value="{{ $key }}">{{ $value }}</option>
                              @endforeach
                        </select>
                  </div>
            </div>
            <div class="col-md-2 col-xs-6">
                  <div class="form-group pull-right">
                        <div class="input-group">
                              <button type="button" class="tw-dw-btn tw-dw-btn-primary tw-text-white tw-dw-btn-sm" id="credit_sale_payment_date_filter">
                                    <span>
                                          <i class="fa fa-calendar"></i> {{ __('messages.filter_by_date') }}
                                    </span>
                                    <i class="fa fa-caret-down"></i>
                              </button>
                        </div>
                  </div>
            </div>
      </div>
      <br>
      <div class="row">
            <div class="col-xs-12">
                  @component('components.widget')
                  <h3 class="text-muted">
                        Shop:
                        <span class="location_name">
                              <i class="fas fa-sync fa-spin fa-fw"></i>
                        </span>
                  </h3>
                  @endcomponent
            </div>
      </div>
      <div class="row">
            <div class="col-xs-6">
                  @component('components.widget', ['title' => 'Payment Method Wise'])
                  <table class="table table-bordered" id="payment_method_table">
                        <thead>
                              <tr>
                                    <th>Payment Method</th>
                                    <th>Amount</th>
                              </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                              <tr>
                                    <th>Total</th>
                                    <th class="text-right" id="payment_method_total">0</th>
                              </tr>
                        </tfoot>
                  </table>
                  @endcomponent
            </div>

            <div class="col-xs-6">
                  @component('components.widget', ['title' => 'Debtors Payment Detail'])
                  <table class="table table-bordered" id="customer_payment_table">
                        <thead>
                              <tr>
                                    <th>Customer</th>
                                    <th>Amount</th>
                              </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                              <tr>
                                    <th>Total</th>
                                    <th class="text-right" id="customer_payment_total">0</th>
                              </tr>
                        </tfoot>
                  </table>
                  @endcomponent
            </div>
      </div>


      <div class="row no-print">
            <div class="col-sm-12">
                  <button class="tw-dw-btn tw-dw-btn-primary tw-text-white pull-right" aria-label="Print"
                        onclick="window.print();">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                              class="icon icon-tabler icons-tabler-outline icon-tabler-printer">
                              <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                              <path d="M17 17h2a2 2 0 0 0 2 -2v-4a2 2 0 0 0 -2 -2h-14a2 2 0 0 0 -2 2v4a2 2 0 0 0 2 2h2" />
                              <path d="M17 9v-4a2 2 0 0 0 -2 -2h-6a2 2 0 0 0 -2 2v4" />
                              <path d="M7 13m0 2a2 2 0 0 1 2 -2h6a2 2 0 0 1 2 2v4a2 2 0 0 1 -2 2h-6a2 2 0 0 1 -2 -2z" />
                        </svg> @lang('messages.print')
                  </button>
            </div>
      </div>


</section>
<!-- /.content -->
@stop
@section('javascript')
<script src="{{ asset('js/report.js?v=' . $asset_v) }}"></script>

@endsection