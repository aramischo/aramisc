@extends('backEnd.master')
@section('title')
@lang('front_settings.course_heading')
@endsection
@section('mainContent')
    <section class="sms-breadcrumb mb-20">
        <div class="container-fluid">
            <div class="row justify-content-between">
                <h1>@lang('front_settings.course_heading')</h1>
                <div class="bc-pages">
                    <a href="{{route('dashboard')}}">@lang('common.dashboard')</a>
                    <a href="#">@lang('front_settings.front_settings')</a>
                    <a href="#">@lang('front_settings.course_heading')</a>
                </div>
            </div>
        </div>
    </section>
    <section class="admin-visitor-area">
        <div class="container-fluid p-0">
            <div class="row">
                <div class="col-lg-12">
                    <div class="row">
                        <div class="col-lg-12">
                            @if(userPermission('course-heading-update'))
                                {{ Form::open(['class' => 'form-horizontal', 'files' => true, 'route' => 'course-heading-update',
                                'method' => 'POST', 'enctype' => 'multipart/form-data']) }}
                            @endif
                            <div class="white-box">
                                <div class="main-title">
                                    <h3 class="mb-15">
                                        @lang('front_settings.update_course_heading_section')
                                         
                                    </h3>
                                </div> 
                                <div class="add-visitor {{isset($update)? '':'isDisabled'}}">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="primary_input">
                                                <label> @lang('common.title')<span class="text-danger"> *</span></label>
                                                <input
                                                    class="primary_input_field "
                                                    type="text" name="title" autocomplete="off"
                                                    value="{{isset($update)? ($AramiscCoursePage != ''? $AramiscCoursePage->title:''):''}}">
                                               
                                                
                                                @if ($errors->has('title'))
                                                    <span class="text-danger" >
                                                    {{ $errors->first('title') }}
                                                </span>
                                                @endif
                                            </div>
                                            <div class="primary_input mt-15">
                                                <div class="primary_input">
                                                    <label> @lang('common.description') <span class="text-danger"> *</span> </label>
                                                    <textarea class="primary_input_field form-control" cols="0" rows="5" name="description" id="description">{{isset($update)? ($AramiscCoursePage != ''? $AramiscCoursePage->description:''):'' }}</textarea>
                                                   
                                                    @if($errors->has('description'))
                                                        <span class="text-danger" >
                                                        {{ $errors->first('description') }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="primary_input mt-15">
                                                <label> @lang('front_settings.main_title')<span class="text-danger"> *</span></label>
                                                <input
                                                    class="primary_input_field form-control{{ $errors->has('main_title') ? ' is-invalid' : '' }}"
                                                    type="text" name="main_title" autocomplete="off"
                                                    value="{{isset($update)? ($AramiscCoursePage != ''? $AramiscCoursePage->main_title:''):''}}">
                                              
                                                
                                                @if ($errors->has('main_title'))
                                                    <span class="text-danger" >
                                                    {{ $errors->first('main_title') }}
                                                </span>
                                                @endif
                                            </div>
                                            <div class="primary_input mt-15">
                                                <div class="primary_input">
                                                    <label> @lang('front_settings.main_description') <span class="text-danger"> *</span> </label>
                                                    <textarea class="primary_input_field form-control" cols="0" rows="5" name="main_description" id="main_description">{{isset($update)? ($AramiscCoursePage != ''? $AramiscCoursePage->main_description:''):'' }}</textarea>
                                                    
                                                    @if($errors->has('main_description'))
                                                        <span class="text-danger" >
                                                        {{ $errors->first('main_description') }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="primary_input mt-15">
                                                <label> @lang('front_settings.button_text')<span class="text-danger"> *</span></label>
                                                <input
                                                    class="primary_input_field form-control{{ $errors->has('button_text') ? ' is-invalid' : '' }}"
                                                    type="text" name="button_text" autocomplete="off"
                                                    value="{{isset($update)? ($AramiscCoursePage != ''? $AramiscCoursePage->button_text:''):'' }}">
                                                
                                                
                                                @if ($errors->has('button_text'))
                                                    <span class="text-danger" >
                                                    {{ $errors->first('button_text') }}
                                                </span>
                                                @endif
                                            </div>
                                            <div class="primary_input mt-15">
                                                <label> @lang('front_settings.button_url')<span class="text-danger"> *</span></label>
                                                <input
                                                    class="primary_input_field form-control{{ $errors->has('button_text') ? ' is-invalid' : '' }}"
                                                    type="text" name="button_url" autocomplete="off"
                                                    value="{{isset($update)? ($AramiscCoursePage != ''? $AramiscCoursePage->button_url:''):'' }}">
                                              
                                                
                                                @if ($errors->has('button_url'))
                                                    <span class="text-danger" >
                                                    {{ $errors->first('button_url') }}
                                                </span>
                                                @endif
                                            </div>

                                        </div>
                                    </div>
                                    <div class="row mt-15">
                                        <div class="col-lg-12 mt-15">
                                            <div class="primary_input">
                                                <div class="primary_file_uploader">
                                                    <input class="primary_input_field form-control{{ $errors->has('image') ? ' is-invalid' : '' }}" id="placeholderInput" type="text"
                                                       placeholder="{{isset($update)? ($AramiscCoursePage and $AramiscCoursePage->image !="") ? getFilePath3($AramiscCoursePage->image) :trans('front_settings.image') .' *' :trans('front_settings.image') .' *' }}"
                                                       readonly>
                                                    <button class="" type="button">
                                                        <label class="primary-btn small fix-gr-bg" for="browseFile">{{ __('common.browse') }} @lang('front_settings.image')(1420px*450px)</label>
                                                        <input type="file" class="d-none" name="image" id="browseFile">
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    @php 
                                        $tooltip = "";
                                        if(userPermission('course-heading-update')){
                                                $tooltip = "";
                                            }else{
                                                $tooltip = "You have no permission to add";
                                            }
                                    @endphp
                                    <div class="row mt-40">
                                        <div class="col-lg-12 text-center">
                                            @if(Illuminate\Support\Facades\Config::get('app.app_sync'))
                                                    <span class="d-inline-block" tabindex="0" data-toggle="tooltip" title="Disabled For Demo "> <button class="primary-btn fix-gr-bg  demo_view" style="pointer-events: none;" type="button" >@lang('front_settings.update') </button></span>
                                            @else

                                                <button class="primary-btn fix-gr-bg" data-toggle="tooltip" title="{{@$tooltip}}">
                                                    <span class="ti-check"></span>
                                                    @if(isset($update))
                                                        @lang('front_settings.update')
                                                    @else
                                                        @lang('front_settings.save')
                                                    @endif
                                                </button>
                                            @endif   
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {{ Form::close() }}
                        </div>
                    </div>
                </div>
 
            </div>
        </div>
    </section>


    <div class="modal fade admin-query" id="showimageModal">
        <div class="modal-dialog modal-dialog-centered large-modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">@lang('front_settings.course_details')</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body p-0">
                    <div class="container student-certificate">
                        <div class="row justify-content-center">
                            <div class="col-lg-12 text-center">
                                <div class="mt-20">
                                    <section class="container box-1420">
                                        <div class="banner-area" style="background: linear-gradient(0deg, rgba(124, 50, 255, 0.6), rgba(199, 56, 216, 0.6)), url({{@$AramiscCoursePage->image != ""? @$AramiscCoursePage->image : '../img/client/common-banner1.jpg'}}) no-repeat center;background-size: 100%">
                                            <div class="banner-inner">
                                                <div class="banner-content">
                                                    <h2 style="color: whitesmoke">{{@$AramiscCoursePage->title}}</h2>
                                                    <p style="color: whitesmoke">{{@$AramiscCoursePage->description}}</p>
                                                    <a class="primary-btn fix-gr-bg semi-large" href="{{@$AramiscCoursePage->button_url}}">{{@$AramiscCoursePage->button_text}}</a>
                                                </div>
                                            </div>
                                        </div>
                                    </section>
                                    <div class="mt-10 row">
                                        <div class="col-md-6">
                                            <div class="academic-item">
                                                <div class="academic-img">
                                                    <img class="img-fluid" src="{{asset(@$AramiscCoursePage->main_image)}}" alt="">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="academic-text mt-30">
                                                <h4>
                                                    {{@$AramiscCoursePage->main_title}}
                                                </h4>
                                                <p>
                                                    {{@$AramiscCoursePage->main_description}}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection