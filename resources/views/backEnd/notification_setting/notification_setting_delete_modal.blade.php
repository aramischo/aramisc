<link rel="stylesheet" href="{{ asset('public/backEnd/vendors/editor/summernote-bs4.css') }}">
<input type="hidden" id="id" value="{{ $id }}">
<input type="hidden" id="key" value="{{ $key }}">
<div class="container-fluid mt-30">
    <div class="row">
        <div class="col-lg-12">
            <div class="text-center">
                <h4 class="alert alert-danger">
                    @lang('common.are_you_sure_to_delete') ?
                </h4>
            </div>
            <div class="mt-40 d-flex justify-content-between">
                <button type="button" class="primary-btn tr-bg" data-dismiss="modal">@lang('common.cancel')</button>
                <button type="submit" class="primary-btn fix-gr-bg text-nowrap deleteNotificationModal"
                        data-toggle="tooltip">
                    <span class="ti-trash"></span>
                    @lang('common.delete')
                </button>
            </div>
        </div>
    </div>
</div>

