<link rel="stylesheet" href="{{ asset('public/backEnd/vendors/editor/summernote-bs4.css') }}">

<div class="row">
    <div class="col-lg-12">
        <div class="primary_input">
            {{-- {{ Form::open(['class' => 'form-horizontal', 'route' => '', 'method' => 'PUT']) }} --}}
            <div class="primary_input alert alert-danger" id="errmess" style="display: none;">
                @lang('system_settings.please_fill_required_fields')
            </div>
            <div class="primary_input">
                <label class="primary_input_label" for="">@lang('system_settings.event') <span style="color:red;">*</span></label>
                <select class="primary_input_field form-control" name="id" id="id">
                    <option value="">@lang('system_settings.select_event')</option>
                    @isset($notificationSettings)
                        @foreach ($notificationSettings as $role => $notif)
                           <option value="{{$notif->id}}">{{ str_replace('_', ' ', $notif->event) }}</option>
                            @endforeach
                    @endisset
                </select>
            </div>
            <div class="primary_input mt-20">
                <label class="primary_input_label" for="">@lang('system_settings.notification') <span style="color:red;">*</span></label>
                <select class="primary_input_field form-control" name="key" id="key">
                    <option value="">@lang('system_settings.select_notification')</option>
                    @isset($recipients)
                        @foreach ($recipients as $role => $recipient)
                            <option value="{{$recipient}}">{{$recipient}}</option>
                        @endforeach
                    @endisset
                </select>
            </div>
            <div class="row mt-40">
                <div class="col-lg-12 text-center">
                    <button type="submit" class="primary-btn fix-gr-bg text-nowrap addNotificationModal" data-toggle="tooltip">
                        <span class="ti-check"></span>
                        @lang('common.add')
                    </button>
                </div>
            </div>
            {{-- {{ Form::close() }} --}}
        </div>
    </div>
</div>


<script src="{{asset('public/backEnd/')}}/vendors/editor/summernote-bs4.js"></script>
<script>
    $('.summer_note').summernote({
        placeholder: 'Write here',
        tabsize: 2,
        height: 400
    });
    </script>
