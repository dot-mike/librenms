@extends('layouts.librenmsv1')

@section('title', __('Alert Log'))

@section('content')
    <div class="container-fluid">
        <x-panel body-class="tw:p-0!">
            <x-slot name="heading">
                <h3 class="panel-title">@lang('Alert Log entries')</h3>
            </x-slot>

            <!-- Filter Form -->
            <table id="alertlog" class="table table-hover table-condensed table-striped"
                   data-url="{{ route('table.alertlog') }}">
                <thead>
                <tr>
                    <th data-column-id="status" data-sortable="false">@lang('State')</th>
                    <th data-column-id="time_logged" data-order="desc">@lang('Timestamp')</th>
                    <th data-column-id="details" data-sortable="false">&nbsp;</th>
                    <th data-column-id="hostname">@lang('Device')</th>
                    <th data-column-id="alert">@lang('Alert')</th>
                    <th data-column-id="severity">@lang('Severity')</th>
                    @if(auth()->user()->hasGlobalAdmin())
                        <th data-column-id="verbose_details" data-sortable="false">@lang('Details')</th>
                    @endif
                </tr>
                </thead>
            </table>
        </x-panel>
    </div>

    <!-- Alert details modal -->
    <div class="modal fade" id="alert_details_modal" tabindex="-1" role="dialog" aria-labelledby="alert_details_modal_label">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title" id="alert_details_modal_label">@lang('Alert Details')</h4>
                </div>
                <div class="modal-body">
                    <div id="alert_details_content">
                        @lang('Loading...')
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">@lang('Close')</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        var grid = $("#alertlog").bootgrid({
            ajax: true,
            rowCount: [25, 50, 100, 250, -1],
            templates: {
                header: "<div id=\"@{{ctx.id}}\" class=\"@{{css.header}} tw:flex tw:flex-wrap\">" +
                    "<form method=\"post\" action=\"{{ route('alert-log') }}\" class=\"tw:flex tw:flex-wrap tw:items-center\" role=\"form\" id=\"alertlog_filter\">" +
                    "{!! addslashes(csrf_field()) !!}" +
                    "<div class=\"tw:flex tw:items-baseline tw:mr-3 tw:mt-2\">" +
                    "<span class=\"tw:mr-1\">@lang('Device')</span>" +
                    "<select name=\"device_id\" id=\"device_id\" class=\"form-control\"></select>" +
                    "</div>" +
                    "<div class=\"tw:flex tw:items-baseline tw:mr-3 tw:mt-2\">" +
                    "<span class=\"tw:mr-1\">@lang('Alert Rule')</span>" +
                    "<select name=\"rule_id\" id=\"rule_id\" class=\"form-control\"></select>" +
                    "</div>" +
                    "<div class=\"tw:flex tw:items-baseline tw:mr-3 tw:mt-2\">" +
                    "<span class=\"tw:mr-1\">@lang('State')</span>" +
                    "<select name=\"state\" id=\"state\" class=\"form-control\">" +
                    "@foreach($alert_states as $name => $value)<option value='{{ $value }}' {{ $filter['state'] == $value ? 'selected' : '' }}>{{ $name }}</option>@endforeach" +
                    "</select>" +
                    "</div>" +
                    "<div class=\"tw:flex tw:items-baseline tw:mr-3 tw:mt-2\">" +
                    "<span class=\"tw:mr-1\">@lang('Severity')</span>" +
                    "<select name=\"min_severity\" id=\"min_severity\" class=\"form-control\">" +
                    "@foreach($alert_severities as $name => $value)<option value='{{ $value }}' {{ $filter['min_severity'] == $value ? 'selected' : '' }}>{{ $name }}</option>@endforeach" +
                    "</select>" +
                    "</div>" +
                    "<button type=\"submit\" class=\"btn btn-default tw:mr-2 tw:mt-2\">@lang('Filter')</button>" +
                    "</form>" +
                    "<div class=\"actionBar tw:ml-auto tw:relative tw:mt-2\"><div class=\"@{{css.actions}}\"></div></div>" +
                    "</div>"
            },
            post: function () {
                return @json($filter);
            },
        }).on("loaded.rs.jquery.bootgrid", function () {
            // Handle incident toggles
            grid.find(".incident-toggle").each(function () {
                $(this).parent().addClass('incident-toggle-td');
            }).on("click", function (e) {
                var target = $(this).data("target");
                $(target).collapse('toggle');
                $(this).toggleClass('fa-plus fa-minus');
            });

            // Handle verbose alert details
            grid.find(".verbose-alert-details").on("click", function(e) {
                e.preventDefault();
                var alert_log_id = $(this).data('alert_log_id');
                $('#alert_log_id').val(alert_log_id);
                $("#alert_details_modal").modal('show');
            });

            // Style incident rows
            grid.find(".incident").each(function () {
                $(this).parent().addClass('col-lg-4 col-md-4 col-sm-4 col-xs-4');
                if ($(this).parent().parent().find(".alert-status").hasClass('label-danger')){
                    $(this).parent().parent().find(".verbose-alert-details").fadeIn(0);
                }
                $(this).parent().parent().on("mouseenter", function () {
                    $(this).find(".incident-toggle").fadeIn(200);
                    if ($(this).find(".alert-status").hasClass('label-danger')){
                        $(this).find(".verbose-alert-details").fadeIn(200);
                    }
                }).on("mouseleave", function () {
                    $(this).find(".incident-toggle").fadeOut(200);
                    if ($(this).find(".alert-status").hasClass('label-danger')){
                        $(this).find(".verbose-alert-details").fadeOut(200);
                    }
                }).on("click", "td:not(.incident-toggle-td)", function () {
                    var target = $(this).parent().find(".incident-toggle").data("target");
                    if ($(this).parent().find(".incident-toggle").hasClass('fa-plus')) {
                        $(this).parent().find(".incident-toggle").toggleClass('fa-plus fa-minus');
                        $(target).collapse('toggle');
                    }
                });
            });
        });

        // Initialize device selector
        init_select2("#device_id", "device", {}, @json($device_selected), "@lang('All Devices')");
        
        // Initialize alert rule selector
        init_select2("#rule_id", "alert-rules", {}, @json($rule_selected ?? ''), "@lang('All Alert Rules')");
    </script>
@endpush

@push('styles')
    <style>
        .actionBar > .actions {
            display: flex;
        }
        .actionBar > .actions > * {
            float: none;
        }
    </style>
@endpush
