@push('script')
    <script type="text/javascript" src="{{asset('public/backEnd/multiselect/')}}/js/jquery.multiselect.js"></script>
    <script type="text/javascript">
        $(function () {
            $("select[multiple].active.multypol_check_select").multiselect({ 
                columns: 1, 
                placeholder: "@lang('common.select')", 
                search: true, 
                searchOptions: {default: "Select"},
                selectAll: true, 
            }); 
        }); 
    </script>
@endpush 