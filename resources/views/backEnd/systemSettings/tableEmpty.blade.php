@extends('backEnd.master')
@section('mainContent')


    <section class="sms-breadcrumb mb-20">
        <div class="container-fluid">
            <div class="row justify-content-between">
                <h1>@lang('system_settings.manage_sample_data') </h1>
                <div class="bc-pages">
                    <a href="{{route('dashboard')}}">@lang('common.dashboard')</a>
                    <a href="#">@lang('system_settings.system_settings')</a>
                    <a href="#">@lang('system_settings.sample_data') </a>
                </div>
            </div>
        </div>
    </section>   

    <section class="admin-visitor-area up_admin_visitor empty_table_tab">
        <div class="container-fluid p-0">
            <div class="row">
                <div class="col-lg-12">
                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="home-tab" data-toggle="tab" href="#home" role="tab"
                               aria-controls="home" aria-selected="true">@lang('system_settings.empty_sample_data')  </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="profile-tab" data-toggle="tab" href="#profile" role="tab"
                               aria-controls="profile" aria-selected="false">@lang('system_settings.restore_data')</a>
                        </li>
                    </ul>
                    <div class="tab-content" id="myTabContent">
                        <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">

                            <div class="white-box">
                                <div class="add-visitor">
                                    <div class="row">
                                        @if(Illuminate\Support\Facades\Config::get('app.app_sync'))
                                            {{ Form::open(['class' => 'form-horizontal', 'files' => true, 'route' => 'admin-dashboard', 'method' => 'GET', 'enctype' => 'multipart/form-data']) }}
                                        @else
                                            {{ Form::open(['class' => 'form-horizontal', 'files' => true, 'route' => 'database-delete','method' => 'POST']) }}
                                        @endif
                                        
                                        <div class="col-lg-12 text-center">
                                            <h5 class="text-center">@lang('system_settings.all_database_table_list')</h5>
                                        </div>

                                        <div class="col-lg-12">
                                            @if(session()->has('message-success'))
                                                <div class="alert alert-success">
                                                    {{ session()->get('message-success') }}
                                                </div>
                                            @elseif(session()->has('message-danger'))
                                                <div class="alert alert-danger">
                                                    {{ session()->get('message-danger') }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row mt-40 mb-30">


                                        <?php $count = 0;

                                        foreach($table_list_with_count as $row){
                                        @$name = str_replace('aramisc_', '', @$row);
                                        @$name = str_replace('_', ' ', @$name);
                                        @$name = ucfirst(@$name);
                                        @$notdeleteable = [
                                            'users', 'aramisc_role_permissions', 'aramisc_modules', 'aramisc_module_links', 'aramisc_base_setups',
                                            'aramisc_base_groups', 'roles', 'languages', 'aramisc_languages', 'aramisc_language_phrases', 'aramisc_countries',
                                            'aramisc_currencies', 'aramisc_general_settings', 'continents', 'aramisc_email_settings', 'password_resets',
                                            'aramisc_backups', 'aramisc_dashboard_settings', 'aramisc_date_formats', 'aramisc_frontend_persmissions', 'migrations',
                                            'countries', 'aramisc_about_pages', 'aramisc_contact_pages', 'aramisc_testimonials', 'aramisc_home_page_settings', 'aramisc_courses',
                                            'aramisc_academic_years', 'aramisc_payment_gateway_settings', 'aramisc_sms_gateways', 'aramisc_payment_methhods',
                                            'aramisc_background_settings', 'aramisc_dashboard_settings', 'aramisc_setup_admins', 'aramisc_custom_links', 'aramisc_weekends',
                                            'aramisc_schools', 'aramisc_marks_grades','aramisc_styles','aramisc_news_categories','aramisc_events','aramisc_module_permission_assigns', 'aramisc_module_permissions', 'aramisc_time_zones'];

                                        if(!in_array(@$table_list[@$count], @$notdeleteable)){
                                        ?>
                                        <div class="col-lg-4">
                                            <input type="checkbox" id="D{{@$table_list[@$count]}}"
                                                   class="common-checkbox form-control{{ $errors->has('permisions') ? ' is-invalid' : '' }}"
                                                   name="permisions[]" value="{{@$table_list[@$count]}}">
                                            <label for="D{{@$table_list[@$count]}}"> {{@$name}} </label>
                                        </div>
                                        <?php
                                        }
                                        @$count++;
                                        }
                                        ?>
                                    </div>


                                    <div class="row">
                                        <div class="col-lg-9 text-right">
                                            <div class="primary-btn fix-gr-bg">
                                                <input id="selectAll" class="common-checkbox form-control"
                                                       type="checkbox" name="select-all"/><label for="selectAll"> @lang('common.select')
                                                        @lang('common.all')</label>
                                            </div>
                                        </div>

                                        <div class="col-lg-3 text-right">


                                                @if(Illuminate\Support\Facades\Config::get('app.app_sync'))
                                                <span class="d-inline-block" tabindex="0" data-toggle="tooltip" title="Disabled For Demo "> <button class="primary-btn small fix-gr-bg  demo_view" style="pointer-events: none;" type="button" >   @lang('system_settings.empty_sample_data')</button></span>
                                            @else
                                            <button class="primary-btn fix-gr-bg small">
                                                    <span class="ti-check"></span>
                                                    @lang('system_settings.empty_sample_data') 
                                                </button>
                                            @endif 

                                          
                                        </div>
                                    </div>
                                    {{ Form::close() }}

                                </div>
                            </div>

                        </div>
                        <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">

                            <div class="white-box">
                                <div class="add-visitor">
                                    <div class="row">

                                        <div class="col-lg-9 text-center">
                                            <p class="text-left"> @lang('system_settings.restore_message') </p>
                                        </div>
                                        <div class="col-lg-3 text-right">

                                            {{-- <span class="d-inline-block" tabindex="0" data-toggle="tooltip" title="Disabled For Demo ">
                                            <button class="primary-btn small fix-gr-bg  demo_view" style="pointer-events: none;" type="button" disabled>X @lang('system_settings.sample_data_restore')</button>
                                            </span> --}}

                                            <a href="{{url('database-restore')}}" class="primary-btn fix-gr-bg small"> <span  class="ti-check"></span>@lang('system_settings.sample_data_restore') </a>
                                        </div>
                                    </div>
                                </div>
                            </div>


                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>



@endsection

@section('script')
    <script language="JavaScript">

        $('#selectAll').click(function () {
            $('input:checkbox').prop('checked', this.checked);

        });


    </script>
@endsection
