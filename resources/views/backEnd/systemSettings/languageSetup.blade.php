@extends('backEnd.master')
@section('title')
@lang('system_settings.language_settings')
@endsection
@section('mainContent')
    <section class="sms-breadcrumb mb-20">
        <div class="container-fluid">
            <div class="row justify-content-between">
                <h1>@lang('system_settings.language_settings')</h1>
                <div class="bc-pages">
                    <a href="{{route('dashboard')}}">Dashboard</a>
                    <a href="#">@lang('system_settings.system_settings')</a>
                    <a href="#">@lang('system_settings.language_settings')</a>
                </div>
            </div>
        </div>
    </section>

    <section class="admin-visitor-area">
        <div class="container-fluid p-0">
            @if(isset($edit_languages))
            <div class="row">
                <div class="offset-lg-10 col-lg-2 text-right col-md-12 mb-20">
                    <a href="{{route('marks-grade')}}" class="primary-btn small fix-gr-bg">
                        <span class="ti-plus pr-2"></span>
                        @lang('common.add')
                    </a>
                </div>
            </div>
            @endif
            <div class="row">
                <div class="col-lg-12">
                    <div class="row">
                        <div class="col-lg-4 no-gutters">
                            <div class="main-title">
                                <h3 class="mb-30">
                                @lang('system_settings.language_setup')</h3>
                            </div>
                        </div>
                    </div>

                    {{ Form::open(['class' => 'form-horizontal', 'files' => true, 'route' => 'translation-term-update', 'method' => 'POST']) }}
                    <div class="row">
                        <div class="col-lg-12 mb-30">
                            <div class="white-box onchangeSearch" style="height:100px;">

                                <div class="col-xs-12 col-sm-4 pull-left">
                                <select class="primary_select form-control {{ $errors->has('module_id') ? ' is-invalid' : '' }}" id="module_id" name="module_id">
                                    <option data-display="Select Module *" value="">@lang('common.select_module') *</option>
                                    @foreach($modules as $key => $module)
                                        <optgroup label="{{ $key }}">
                                            @foreach($module as $k => $moduleName)
                                                @php
                                                    $formattedModuleName = strpos($moduleName, '::') !== false ? last(explode('::', $moduleName)) : $moduleName;
                                                    $formattedModuleName = ucwords(str_replace('_', ' ', $formattedModuleName));
                                                @endphp
                                                <option value="{{ $k }}">{{ $formattedModuleName }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                                @if ($errors->any())
                                    @foreach ($errors->all() as $error)
                                        <p class="text-danger">{{ $error }}</p>
                                    @endforeach
                                @endif
                                </div>
                                <div class="col-xs-12 col-sm-8 pull-left">
                                <input class="primary_input_field form-control"
                                   placeholder="@lang('common.search_text')"
                                   type="text"
                                       id="searchtesxt_id"
                                   name="searchttext"
                                   autocomplete="off"
                                   value="">
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-12 mb-30" style="display: none" id="preloader">
                            <div class="primary_input mb-25 pt-4" style="text-align: center;">
                                <img height="100" class="" src="http://dev.aramisc.com/public/uploads/settings/preloader/preloader1.gif" alt="">
                            </div>
                        </div>
                        <div class="clear clearfix"></div>
                        <div class="col-lg-12" >
                            <input type="hidden" id="url" value="{{url('/')}}">
                            <input type="hidden" id="language_universal" value="{{@$language_universal}}" name="language_universal">
                            <table class="table school-table-style" cellspacing="0" width="100%" id="language_table">
                                <thead>
                                <tr>
                                    <th>File</th>
                                    <th>Default Phrases</th>
                                    <th>{{$language_universal}} Phrases</th>
                                </tr>
                                </thead>
                                <tbody>
                                    @php $count=1; @$sms_languages =[]; @endphp
                                    @foreach($sms_languages as $row)
                                    <tr>
                                        <td>{{@$row->en}}</td>
                                        <td>{{@$row->en}}</td>
                                        <td>
                                            <div class="primary_input">
                                                <input type="hidden" name="InputId[{{@$row->id}}]" value="{{@$row->id}}">
                                                <input class="primary_input_field form-control{{ $errors->has('language_universal') ? ' is-invalid' : '' }}"
                                                    type="text" name="LU[{{@$row->id}}]" autocomplete="off" value="{{@$row->$language_universal}}">
                                                @if ($errors->has('language_universal'))
                                                    <span class="text-danger" >
                                                        {{ $errors->first('language_universal') }}
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <div id="language-tables"></div>
                            <div class="pull-right">
                                <div class="row mt-40">
                                    <div class="col-lg-12 text-center">
                                        <button class="primary-btn fix-gr-bg submit" style="display: none;" disabled>
                                            <span class="ti-check"></span>
                                            @lang('system_settings.update_language')
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </section>
@endsection
