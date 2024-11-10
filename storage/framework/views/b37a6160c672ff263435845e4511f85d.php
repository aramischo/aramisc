<?php $__env->startSection('title'); ?>
    <?php echo app('translator')->get('study.upload_content_list'); ?>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('mainContent'); ?>
    <section class="sms-breadcrumb mb-20">
        <div class="container-fluid">
            <div class="row justify-content-between">
                <h1><?php echo app('translator')->get('study.upload_content_list'); ?></h1>
                <div class="bc-pages">
                    <a href="<?php echo e(route('dashboard')); ?>"><?php echo app('translator')->get('common.dashboard'); ?></a>
                    <a href="#"><?php echo app('translator')->get('study.study_material'); ?></a>
                    <a href="#"><?php echo app('translator')->get('study.upload_content_list'); ?></a>
                </div>
            </div>
        </div>
    </section>
    <section class="admin-visitor-area up_admin_visitor">
        <div class="container-fluid p-0">
            <div class="row">
                <div class="col-lg-4 col-xl-3">
                    <div class="row">
                        <div class="col-lg-12">
                            <?php if(isset($editData)): ?>
                                <?php echo e(Form::open(['class' => 'form-horizontal', 'files' => true, 'route' => 'update-upload-content', @$editData->id, 'method' => 'POST', 'enctype' => 'multipart/form-data'])); ?>

                                <input type="hidden" name="id" value="<?php echo e(@$editData->id); ?>">
                            <?php else: ?>
                                <?php if(userPermission('save-upload-content')): ?>
                                    <?php echo e(Form::open(['class' => 'form-horizontal', 'files' => true, 'route' => 'save-upload-content', 'method' => 'POST', 'enctype' => 'multipart/form-data'])); ?>

                                <?php endif; ?>
                            <?php endif; ?>
                            <div class="white-box">
                                <div class="main-title">
                                    <h3 class="mb-15">
                                        <?php if(isset($editData)): ?>
                                            <?php echo app('translator')->get('study.edit_upload_content'); ?>
                                        <?php else: ?>
                                            <?php echo app('translator')->get('study.upload_content'); ?>
                                        <?php endif; ?>
    
                                    </h3>
                                </div>
                                <div class="add-visitor">
                                    <div class="row mb-25">
                                        <div class="col-lg-12 mb-30">
                                            <div class="primary_input">
                                                <label> <?php echo app('translator')->get('study.content_title'); ?> <span class="text-danger"> *</span> </label>
                                                <input
                                                    class="primary_input_field form-control<?php echo e($errors->has('content_title') ? ' is-invalid' : ''); ?>"
                                                    type="text" name="content_title" autocomplete="off"
                                                    value="<?php echo e(isset($editData) ? @$editData->content_title : ''); ?>">


                                                <?php if($errors->has('content_title')): ?>
                                                    <span class="text-danger">
                                                        <?php echo e($errors->first('content_title')); ?>

                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="col-lg-12 mb-30">
                                            <label class="primary_input_label" for="">
                                                <?php echo e(__('study.content_type')); ?>

                                                <span class="text-danger"> *</span>
                                            </label>
                                            <select
                                                class="primary_select  form-control<?php echo e($errors->has('content_type') ? ' is-invalid' : ''); ?>"
                                                name="content_type" id="content_type">
                                                <option data-display="<?php echo app('translator')->get('study.content_type'); ?> *" value=""><?php echo app('translator')->get('study.content_type'); ?>
                                                    *</option>
                                                <option value="as"
                                                    <?php echo e(isset($editData) && @$editData->content_type == 'as' ? 'selected' : ''); ?>>
                                                    <?php echo app('translator')->get('study.assignment'); ?></option>
                                                
                                                <option value="sy"
                                                    <?php echo e(isset($editData) && @$editData->content_type == 'sy' ? 'selected' : ''); ?>>
                                                    <?php echo app('translator')->get('study.syllabus'); ?></option>
                                                <option value="ot"
                                                    <?php echo e(isset($editData) && @$editData->content_type == 'ot' ? 'selected' : ''); ?>>
                                                    <?php echo app('translator')->get('study.other_download'); ?></option>
                                            </select>
                                            <?php if($errors->has('content_type')): ?>
                                                <span class="text-danger invalid-select" role="alert">
                                                    <?php echo e($errors->first('content_type')); ?>

                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-lg-12">
                                            <label class="primary_input_label" for=""><?php echo app('translator')->get('study.available_for'); ?><span
                                                    class="text-danger"> *</span></label><br>
                                            <div class="">
                                                <input type="checkbox" id="all_admin"
                                                    class="common-checkbox form-control"
                                                    name="available_for[]" value="admin"
                                                    <?php echo e(isset($editData) && @$editData->available_for_admin == '1' ? 'checked' : ''); ?>>
                                                <label style="top: 50% !important;"
                                                    for="all_admin"><?php echo app('translator')->get('study.all_admin'); ?></label>
                                            </div>
                                            <div class="">
                                                <input type="checkbox" id="student"
                                                    class="common-checkbox form-control"
                                                    name="available_for[]" value="student"
                                                    <?php echo e((isset($editData) && @$editData->available_for_all_classes == '1') || @$editData->un_semester_label_id != '' || @$editData->class != '' || @$editData->section != '' ? 'checked' : ''); ?>>
                                                <label for="student"><?php echo app('translator')->get('common.student'); ?></label>
                                            </div>
                                            <?php if($errors->has('available_for')): ?>
                                                <span class="text-danger validate-textarea-checkbox" role="alert">
                                                    <?php echo e($errors->first('available_for')); ?>

                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <?php
                                            // if( @$editData->available_for_all_classes == "1" || @$editData->class != "" || @$editData->section != ""){
                                            if (@$editData->available_for_all_classes == '1') {
                                                $show = '';
                                                $show1 = 'disabledbutton';
                                            } elseif (@$editData->class != '' || @$editData->section != '') {
                                                $show = 'disabledbutton';
                                                $show1 = '';
                                            } else {
                                                $show = 'disabledbutton';
                                                $show1 = 'disabledbutton';
                                            }
                                        ?>
                                        <?php if(!moduleStatusCheck('University')): ?>
                                            <div class="col-lg-12 <?php echo e(@$show); ?> mb-30" id="availableClassesDiv">

                                                <div class="">
                                                    <input type="checkbox" id="all_classes"
                                                        class="common-checkbox form-control" name="all_classes"
                                                        <?php echo e(isset($editData) && @$editData->available_for_all_classes == '1' ? 'checked' : ''); ?>>
                                                    <label for="all_classes"><?php echo app('translator')->get('study.available_for_all_classes'); ?></label>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <div class="forStudentWrapper col-lg-12 mb-20 <?php echo e($errors->has('class') || $errors->has('section') ? '' : @$show1); ?>"
                                            id="contentDisabledDiv">
                                            <?php if(moduleStatusCheck('University')): ?>
                                            <?php if ($__env->exists('university::common.session_faculty_depart_academic_semester_level',['required' => ['USN','UF', 'UD', 'US', 'USL'] , 'hide' => ['USUB','UA'],'row' => 1, 'div' => 'col-lg-12', 'mt' =>'mt-0'])) echo $__env->make('university::common.session_faculty_depart_academic_semester_level',['required' => ['USN','UF', 'UD', 'US', 'USL'] , 'hide' => ['USUB','UA'],'row' => 1, 'div' => 'col-lg-12', 'mt' =>'mt-0'], \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                                            <input type="hidden" name="un_academic_id" id="select_academic" value="<?php echo e(getAcademicId()); ?>">
                                            <?php else: ?> 

                                            <div class="row">
                                                <div class="col-lg-12 mb-20">
                                                    <div class="primary_input">
                                                        <label class="primary_input_label" for="">
                                                            <?php echo e(__('common.class')); ?>

                                                                
                                                        </label>
                                                        <select
                                                            class="primary_select  form-control<?php echo e($errors->has('class') ? ' is-invalid' : ''); ?>"
                                                            name="class" id="classSelectStudent">
                                                            <option data-display="<?php echo app('translator')->get('common.select_class'); ?> "
                                                                    value=""><?php echo app('translator')->get('common.select'); ?></option>
                                                                <?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                    <option value="<?php echo e(@$class->id); ?>"
                                                                        <?php echo e(isset($editData) && $editData->class == $class->id ? 'selected' : ''); ?>>
                                                                        <?php echo e(@$class->class_name); ?></option>
                                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                            </select>

                                                            <?php if($errors->has('class')): ?>
                                                                <span class="text-danger invalid-select" role="alert">
                                                                    <?php echo e($errors->first('class')); ?></span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>

                                                    <div class="col-lg-12 mb-20">
                                                        <div class="primary_input" id="sectionStudentDiv">
                                                            <label class="primary_input_label" for="">
                                                                <?php echo e(__('common.section')); ?>

                                                                <span class="text-danger"> </span>
                                                            </label>
                                                            <select
                                                                class="primary_select  form-control<?php echo e($errors->has('section') ? ' is-invalid' : ''); ?>"
                                                                name="section" id="sectionSelectStudent">
                                                                <option data-display="<?php echo app('translator')->get('common.select_section'); ?> "
                                                                    value=""><?php echo app('translator')->get('common.section'); ?>
                                                                </option>
                                                                <?php if(isset($editData->section)): ?>
                                                                    <?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                        <option value="<?php echo e($section->id); ?>"
                                                                            <?php echo e($editData->section == $section->id ? 'selected' : ''); ?>>
                                                                            <?php echo e($section->section_name); ?></option>
                                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                                <?php endif; ?>
                                                            </select>
                                                            <div class="pull-right loader loader_style"
                                                                id="select_section_loader">
                                                                <img class="loader_img_style"
                                                                    src="<?php echo e(asset('public/backEnd/img/demo_wait.gif')); ?>"
                                                                    alt="loader">
                                                            </div>

                                                            <?php if($errors->has('section')): ?>
                                                                <span class="text-danger invalid-select" role="alert">
                                                                    <?php echo e($errors->first('section')); ?>

                                                                </span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>

                                            <?php endif; ?>

                                        </div>
                                        <input type="hidden" name="url" id="url"
                                            value="<?php echo e(URL::to('/')); ?>">
                                    </div>
                                    <div class="row  mb-20">
                                        <div class="col-lg-12">
                                            <div class="primary_input">
                                                <label class="primary_input_label" for=""><?php echo app('translator')->get('common.date'); ?>
                                                    <span></span> </label>
                                                <div class="primary_datepicker_input">
                                                    <div class="no-gutters input-right-icon">
                                                        <div class="col">
                                                            <div class="">
                                                                <input
                                                                    class="primary_input_field  primary_input_field date form-control form-control<?php echo e($errors->has('upload_date') ? ' is-invalid' : ''); ?>"
                                                                    id="upload_date" type="text"
                                                                    name="upload_date"
                                                                    value="<?php echo e(isset($editData) ? date('m/d/Y', strtotime(@$editData->upload_date)) : date('m/d/Y')); ?>">
                                                            </div>
                                                        </div>
                                                        <button class="btn-date" data-id="#upload_date" type="button">
                                                            <label class="m-0 p-0" for="upload_date">
                                                                <i class="ti-calendar" id="start-date-icon"></i>
                                                            </label>
                                                        </button>
                                                    </div>
                                                </div>
                                                <span class="text-danger"><?php echo e($errors->first('upload_date')); ?></span>
                                            </div>
                                        </div>
                                    </div>


                                    <div class="row mb-20">
                                        <div class="col-lg-12">
                                            <div class="primary_input">
                                                <label class="primary_input_label" for=""><?php echo app('translator')->get('study.description'); ?>
                                                    <span></span> </label>
                                                <textarea class="primary_input_field form-control" cols="0" rows="3" name="description"
                                                    id="description"><?php echo e(@$editData->description); ?></textarea>


                                            </div>
                                        </div>
                                    </div>


                                    <div class="row no-gutters input-right-icon mb-20">
                                        <div class="col-lg-12">
                                            <div class="primary_input">
                                                <label> <?php echo app('translator')->get('study.source_url'); ?></label>
                                                <input
                                                    class="primary_input_field form-control<?php echo e($errors->has('source_url') ? ' is-invalid' : ''); ?>"
                                                    type="text" name="source_url" autocomplete="off"
                                                    value="<?php echo e(isset($editData) ? @$editData->source_url : ''); ?>">


                                                <?php if($errors->has('source_url')): ?>
                                                    <span class="text-danger">
                                                        <?php echo e($errors->first('source_url')); ?>

                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-20">
                                        <div class="col-lg-12 mt-15">
                                            <div class="primary_input">
                                                <div class="primary_file_uploader">
                                                    <input
                                                        class="primary_input_field form-control <?php echo e($errors->has('content_file') ? ' is-invalid' : ''); ?>"
                                                        readonly="true" type="text"
                                                        placeholder="<?php echo e(isset($editData->upload_file) && @$editData->upload_file != '' ? getFilePath3(@$editData->upload_file) : trans('study.file') . ''); ?>"
                                                        id="placeholderUploadContent">
                                                    <button class="" type="button">
                                                        <label class="primary-btn small fix-gr-bg"
                                                            for="upload_content_file"><?php echo e(__('common.browse')); ?></label>
                                                        <input type="file" class="d-none" name="content_file"
                                                            id="upload_content_file">
                                                    </button>
                                                    <code>(jpg,png,jpeg,pdf,doc,docx,mp4,mp3,txt are allowed for
                                                        upload)</code>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                        $tooltip = '';
                                        if (userPermission('save-upload-content')) {
                                            @$tooltip = '';
                                        } else {
                                            @$tooltip = 'You have no permission to add';
                                        }
                                    ?>
                                    <div class="row mt-40">
                                        <div class="col-lg-12 text-center">
                                            <button class="primary-btn fix-gr-bg" data-toggle="tooltip"
                                                title="<?php echo e(@$tooltip); ?>">
                                                <span class="ti-check"></span>
                                                <?php if(isset($editData)): ?>
                                                <?php echo app('translator')->get('common.update'); ?>  
                                                <?php else: ?> 
                                                <?php echo app('translator')->get('common.save'); ?>
                                                <?php endif; ?> 
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php echo e(Form::close()); ?>

                        </div>
                    </div>
                </div>

                <div class="col-lg-8 col-xl-9">
                    <div class="white-box">
                        <div class="row">
                            <div class="col-lg-4 no-gutters">
                                <div class="main-title">
                                    <h3 class="mb-15"> <?php echo app('translator')->get('study.upload_content_list'); ?></h3>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <?php if (isset($component)) { $__componentOriginal163c8ba6efb795223894d5ffef5034f5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal163c8ba6efb795223894d5ffef5034f5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.table','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('table'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                                    <table id="table_id" class="table data-table" cellspacing="0" width="100%">
                                        <thead>
                                            <tr>
                                                <th><?php echo app('translator')->get('common.sl'); ?></th>
                                                <th> <?php echo app('translator')->get('study.content_title'); ?></th>
                                                <th> <?php echo app('translator')->get('common.type'); ?></th>
                                                <th> <?php echo app('translator')->get('common.date'); ?></th>
                                                <th> <?php echo app('translator')->get('study.available_for'); ?></th>
                                                <?php if(moduleStatusCheck('University')): ?>
                                                    <th> <?php echo app('translator')->get('university::un.semester_label'); ?></th>
                                                    
                                                <?php else: ?>
                                                    <th> <?php echo app('translator')->get('study.classSec'); ?></th>
                                                <?php endif; ?>
                                                <th> <?php echo app('translator')->get('common.action'); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            
                                        </tbody>
                                    </table>
                                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal163c8ba6efb795223894d5ffef5034f5)): ?>
<?php $attributes = $__attributesOriginal163c8ba6efb795223894d5ffef5034f5; ?>
<?php unset($__attributesOriginal163c8ba6efb795223894d5ffef5034f5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal163c8ba6efb795223894d5ffef5034f5)): ?>
<?php $component = $__componentOriginal163c8ba6efb795223894d5ffef5034f5; ?>
<?php unset($__componentOriginal163c8ba6efb795223894d5ffef5034f5); ?>
<?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    
    <div class="modal fade admin-query" id="deleteUpContentModal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><?php echo app('translator')->get('study.delete_upload_content'); ?></h4>
                    <button type="button" class="close" data-dismiss="modal">
                        &times;
                    </button>
                </div>

                <div class="modal-body">
                    <div class="text-center">
                        <h4><?php echo app('translator')->get('common.are_you_sure_to_delete'); ?></h4>
                    </div>

                    <div class="mt-40 d-flex justify-content-between">
                        <button type="button" class="primary-btn tr-bg"
                            data-dismiss="modal"><?php echo app('translator')->get('common.cancel'); ?></button>
                        <?php echo e(Form::open(['route' => 'delete-upload-content', 'method' => 'POST'])); ?>

                        <input type="hidden" name="id" value="">
                        <button class="primary-btn fix-gr-bg" type="submit"><?php echo app('translator')->get('common.delete'); ?></button>
                        <?php echo e(Form::close()); ?>

                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>

    
<?php $__env->stopSection(); ?>

<?php echo $__env->make('backEnd.partials.date_picker_css_js', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<?php echo $__env->make('backEnd.partials.data_table_js', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php echo $__env->make('backEnd.partials.server_side_datatable', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->startPush('script'); ?>  
<script>
   $(document).ready(function() {
       $('.data-table').DataTable({
                     processing: true,
                     serverSide: true,
                     "ajax": $.fn.dataTable.pipeline( {
                           url: "<?php echo e(route('upload-content-list-datatable')); ?>",
                           data: { 
                            },
                           pages: "<?php echo e(generalSetting()->ss_page_load); ?>" // number of pages to cache
                           
                       } ),
                       columns: [
                           {data: 'DT_RowIndex', name: 'id'},
                           {data: 'content_title', name: 'content_title'},
                           {data: 'type', name: 'type'},
                           {data: 'date', name: 'date'},
                           {data: 'avaiable', name: 'avaiable'},
                           {data: 'class_sections', name: 'class_sections'},
                           {data: 'action', name: 'action', orderable: false, searchable: true},
                        ],
                        bLengthChange: false,
                bDestroy: true,
                language: {
                    search: "<i class='ti-search'></i>",
                    searchPlaceholder: window.jsLang('quick_search'),
                    paginate: {
                        next: "<i class='ti-arrow-right'></i>",
                        previous: "<i class='ti-arrow-left'></i>",
                    },
                },
                dom: "Bfrtip",
                buttons: [{
                    extend: "copyHtml5",
                    text: '<i class="fa fa-files-o"></i>',
                    title: $("#logo_title").val(),
                    titleAttr: window.jsLang('copy_table'),
                    exportOptions: {
                        columns: ':visible:not(.not-export-col)'
                    },
                },
                    {
                        extend: "excelHtml5",
                        text: '<i class="fa fa-file-excel-o"></i>',
                        titleAttr: window.jsLang('export_to_excel'),
                        title: $("#logo_title").val(),
                        margin: [10, 10, 10, 0],
                        exportOptions: {
                            columns: ':visible:not(.not-export-col)'
                        },
                    },
                    {
                        extend: "csvHtml5",
                        text: '<i class="fa fa-file-text-o"></i>',
                        titleAttr: window.jsLang('export_to_csv'),
                        exportOptions: {
                            columns: ':visible:not(.not-export-col)'
                        },
                    },
                    {
                        extend: "pdfHtml5",
                        text: '<i class="fa fa-file-pdf-o"></i>',
                        title: $("#logo_title").val(),
                        titleAttr: window.jsLang('export_to_pdf'),
                        exportOptions: {
                            columns: ':visible:not(.not-export-col)'
                        },
                        orientation: "landscape",
                        pageSize: "A4",
                        margin: [0, 0, 0, 12],
                        alignment: "center",
                        header: true,
                        customize: function(doc) {
                            doc.content[1].margin = [100, 0, 100, 0]; //left, top, right, bottom
                            doc.content.splice(1, 0, {
                                margin: [0, 0, 0, 12],
                                alignment: "center",
                                image: "data:image/png;base64," + $("#logo_img").val(),
                            });
                            doc.defaultStyle = {
                                font: 'DejaVuSans'
                            }
                        },
                    },
                    {
                        extend: "print",
                        text: '<i class="fa fa-print"></i>',
                        titleAttr: window.jsLang('print'),
                        title: $("#logo_title").val(),
                        exportOptions: {
                            columns: ':visible:not(.not-export-col)'
                        },
                    },
                    {
                        extend: "colvis",
                        text: '<i class="fa fa-columns"></i>',
                        postfixButtons: ["colvisRestore"],
                    },
                ],
                columnDefs: [{
                    visible: false,
                }, ],
                responsive: true,
            });
        } );
    </script>
    <script>
        function deleteUpContent(id) {
            var modal = $('#deleteUpContentModal');
            modal.find('input[name=id]').val(id)
            modal.modal('show');
        }
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('backEnd.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\aramisc\resources\views/backEnd/teacher/uploadContentList.blade.php ENDPATH**/ ?>