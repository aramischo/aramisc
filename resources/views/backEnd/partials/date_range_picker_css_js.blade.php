@push('css')
<link rel="stylesheet" href="{{ asset('public/backEnd/vendors/css/daterangepicker.css') }}" />
@endpush

@push('script')
<script src="{{asset('public/backEnd/')}}/vendors/js/daterangepicker.min.js"></script>
<script type="text/javascript">
    // Format de la plage selon la langue (FR : j/m/aaaa ; autres : aaaa-mm-jj), comme les champs date simples.
    var drpFormat = {!! json_encode(dateConvert('2000-12-31') === '31/12/2000' ? 'DD/MM/YYYY' : 'YYYY-MM-DD') !!};
    @if (@$date_from && @$date_to)
        $('input[name="date_range"]').daterangepicker({
            ranges: {
                {!! json_encode(__('calender.Today')) !!}: [moment(), moment()],
                {!! json_encode(__('calender.Yesterday')) !!}: [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                {!! json_encode(__('calender.Last 7 Days')) !!}: [moment().subtract(6, 'days'), moment()],
                {!! json_encode(__('calender.Last 30 Days')) !!}: [moment().subtract(29, 'days'), moment()],
                {!! json_encode(__('calender.This Month')) !!}: [moment().startOf('month'), moment().endOf('month')],
                {!! json_encode(__('calender.Last Month')) !!}: [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            },
            "locale": {
                "format": drpFormat,
                "separator": " - ",
                "applyLabel": {!! json_encode(__('calender.applyLabel')) !!},
                "cancelLabel": {!! json_encode(__('calender.cancelLabel')) !!},
                "fromLabel": {!! json_encode(__('calender.fromLabel')) !!},
                "toLabel": {!! json_encode(__('calender.toLabel')) !!},
                "customRangeLabel": {!! json_encode(__('calender.customRangeLabel')) !!},
                "weekLabel": {!! json_encode(__('calender.weekLabel')) !!},
                "daysOfWeek": {!! json_encode(__('calender.daysMin')) !!},
                "monthNames": {!! json_encode(__('calender.months')) !!}
            },
            "startDate": {!! json_encode(dateConvert($date_from)) !!},
            "endDate": {!! json_encode(dateConvert($date_to)) !!}
            }, function(start, end, label) {
            console.log('New date range selected: ' + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD') + ' (predefined range: ' + label + ')');
        });
    @else
        $('input[name="date_range"]').daterangepicker({
                ranges: {
                    {!! json_encode(__('calender.Today')) !!}: [moment(), moment()],
                    {!! json_encode(__('calender.Yesterday')) !!}: [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    {!! json_encode(__('calender.Last 7 Days')) !!}: [moment().subtract(6, 'days'), moment()],
                    {!! json_encode(__('calender.Last 30 Days')) !!}: [moment().subtract(29, 'days'), moment()],
                    {!! json_encode(__('calender.This Month')) !!}: [moment().startOf('month'), moment().endOf('month')],
                    {!! json_encode(__('calender.Last Month')) !!}: [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                },
                "locale": {
                    "format": drpFormat,
                "separator": " - ",
                    "applyLabel": {!! json_encode(__('calender.applyLabel')) !!},
                    "cancelLabel": {!! json_encode(__('calender.cancelLabel')) !!},
                    "fromLabel": {!! json_encode(__('calender.fromLabel')) !!},
                    "toLabel": {!! json_encode(__('calender.toLabel')) !!},
                    "customRangeLabel": {!! json_encode(__('calender.customRangeLabel')) !!},
                    "weekLabel": {!! json_encode(__('calender.weekLabel')) !!},
                    "daysOfWeek": {!! json_encode(__('calender.daysMin')) !!},
                    "monthNames": {!! json_encode(__('calender.months')) !!}
                },
                "startDate": moment().subtract(7, 'days'),
                "endDate": moment()
                }, function(start, end, label) {
                console.log('New date range selected: ' + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD') + ' (predefined range: ' + label + ')');
            });
    @endif
</script>
@endpush