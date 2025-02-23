@extends('backEnd.master')
@section('title')
    @lang('academics.class_routine')
@endsection

@push('css')
<style>
    #default_table tr td{
        min-width: 200px;
    }
    table tr th{
        font-weight: 400;
        background-color: var(--table_header) !important;
		text-transform: capitalize !important;
        border-bottom: 1px solid var(--border_color) !important;
    }
    table tr td{
        border-bottom: 1px solid var(--border_color) !important;
    }
</style>
@endpush
@section('mainContent')
    <section class="sms-breadcrumb mb-20">
        <div class="container-fluid">
            <div class="row justify-content-between">
                <h1>@lang('academics.class_routine')</h1>
                <div class="bc-pages">
                    <a href="{{ route('dashboard') }}">@lang('common.dashboard')</a>
                    <a
                        href="{{ route('parent_class_routine', [$student_detail->id]) }}">@lang('academics.class_routine')</a>
                </div>
            </div>
        </div>
    </section>
    <section class="mt-20">
        <div class="container-fluid p-0">
            <div class="row">
                <!-- Start Student Details -->
                <div class="col-lg-12 student-details up_admin_visitor">
                    <ul class="nav nav-tabs tabs_scroll_nav ml-0" role="tablist">

                        @foreach ($records as $key => $record)
                            <li class="nav-item">
                                <a class="nav-link @if ($key == 0) active @endif "
                                    href="#tab{{ $key }}" role="tab" data-toggle="tab">
                                    @if (moduleStatusCheck('University'))
                                        {{ $record->semesterLabel->name }} ({{ $record->unSection->section_name }}) -
                                        {{ @$record->unAcademic->name }}
                                    @else
                                        {{ $record->class->class_name }} ({{ $record->section->section_name }})
                                    @endif
                                </a>
                            </li>
                        @endforeach

                    </ul>
                    <!-- Tab panes -->
                    <div class="tab-content mt-10">
                        <!-- Start Fees Tab -->
                        @foreach ($records as $key => $record)
                            <div role="tabpanel" class="tab-pane fade  @if ($key == 0) active show @endif"
                                id="tab{{ $key }}">
                                <div class="container-fluid p-0">
                                    <div class="white-box">
                                        <div class="row">
                                            <div class="col-lg-6 col-md-6 col-6">
                                                <div class="main-title m-0">
                                                    <h3 class="mb-15">@lang('academics.class_routine')</h3>
                                                </div>
                                            </div>
                                            <div class="col-lg-6 col-md-6 col-6 pull-right">
                                                @if (moduleStatusCheck('University'))
                                                    <a href="{{ route('university.academics.classRoutinePrint', [$record->un_semester_label_id, $record->un_section_id]) }}"
                                                        class="primary-btn small fix-gr-bg pull-right" target="_blank"><i
                                                            class="ti-printer"> </i> Print</a>
                                                @else
                                                    <a href="{{ route('classRoutinePrint', [$record->class_id, $record->section_id]) }}"
                                                        class="primary-btn small fix-gr-bg pull-right" target="_blank"><i
                                                            class="ti-printer"> </i> @lang('common.print')</a>
                                                @endif
                                            </div>
                                        </div>
    
                                        <div>
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <div class="table-responsive">
                                                        <table id="default_table" class="table " cellspacing="0" width="100%">
                                                            <tr>
                                                                @php
                                                                    $height = 0;
                                                                    $tr = [];
                                                                @endphp
                                                                @foreach ($aramisc_weekends as $aramisc_weekend)
                                                                    @php
                                                                        if (moduleStatusCheck('University')) {
                                                                            $studentClassRoutine = App\AramiscWeekend::universityStudentClassRoutine($record->un_semester_label_id, $record->un_section_id, $aramisc_weekend->id);
                                                                        } else {
                                                                            $studentClassRoutine = App\AramiscWeekend::studentClassRoutineFromRecord($record->class_id, $record->section_id, $aramisc_weekend->id);
                                                                        }
                                                                    @endphp
                                                                    @if ($studentClassRoutine->count() > $height)
                                                                        @php
                                                                            $height = $studentClassRoutine->count();
                                                                        @endphp
                                                                    @endif
        
                                                                    <th>{{ @$aramisc_weekend->name }}</th>
                                                                @endforeach
        
                                                            </tr>
        
                                                            @php
                                                                $used = [];
                                                                $tr = [];
                                                                
                                                            @endphp
                                                            @foreach ($aramisc_weekends as $aramisc_weekend)
                                                                @php
                                                                    $i = 0;
                                                                    if (moduleStatusCheck('University')) {
                                                                        $studentClassRoutine = App\AramiscWeekend::universityStudentClassRoutine($record->un_semester_label_id, $record->un_section_id, $aramisc_weekend->id);
                                                                    } else {
                                                                        $studentClassRoutine = App\AramiscWeekend::studentClassRoutineFromRecord($record->class_id, $record->section_id, $aramisc_weekend->id);
                                                                    }
                                                                @endphp
                                                                @foreach ($studentClassRoutine as $routine)
                                                                    @php
                                                                        if (!in_array($routine->id, $used)) {
                                                                            if (moduleStatusCheck('University')) {
                                                                                $tr[$i][$aramisc_weekend->name][$loop->index]['subject'] = $routine->unSubject ? $routine->unSubject->subject_name : '';
                                                                                $tr[$i][$aramisc_weekend->name][$loop->index]['subject_code'] = $routine->unSubject ? $routine->unSubject->subject_code : '';
                                                                            } else {
                                                                                $tr[$i][$aramisc_weekend->name][$loop->index]['subject'] = $routine->subject ? $routine->subject->subject_name : '';
                                                                                $tr[$i][$aramisc_weekend->name][$loop->index]['subject_code'] = $routine->subject ? $routine->subject->subject_code : '';
                                                                            }
                                                                            $tr[$i][$aramisc_weekend->name][$loop->index]['class_room'] = $routine->classRoom ? $routine->classRoom->room_no : '';
                                                                            $tr[$i][$aramisc_weekend->name][$loop->index]['teacher'] = $routine->teacherDetail ? $routine->teacherDetail->full_name : '';
                                                                            $tr[$i][$aramisc_weekend->name][$loop->index]['start_time'] = $routine->start_time;
                                                                            $tr[$i][$aramisc_weekend->name][$loop->index]['end_time'] = $routine->end_time;
                                                                            $tr[$i][$aramisc_weekend->name][$loop->index]['is_break'] = $routine->is_break;
                                                                            $used[] = $routine->id;
                                                                        }
                                                                        
                                                                    @endphp
                                                                @endforeach
        
                                                                @php
                                                                    
                                                                    $i++;
                                                                @endphp
                                                            @endforeach
        
                                                            @for ($i = 0; $i < $height; $i++)
                                                                <tr>
                                                                    @foreach ($tr as $days)
                                                                        @foreach ($aramisc_weekends as $aramisc_weekend)
                                                                            <td>
                                                                                @php
                                                                                    $classes = gv($days, $aramisc_weekend->name);
                                                                                @endphp
                                                                                @if ($classes && gv($classes, $i))
                                                                                    @if ($classes[$i]['is_break'])
                                                                                        <strong> @lang('academics.break') </strong>
        
                                                                                        <span class="">
                                                                                            ({{ date('h:i A', strtotime(@$classes[$i]['start_time'])) }}
                                                                                            -
                                                                                            {{ date('h:i A', strtotime(@$classes[$i]['end_time'])) }})
                                                                                            <br> </span>
                                                                                    @else
                                                                                        <span class="">
                                                                                            <strong>@lang('common.time') :</strong>
                                                                                            {{ date('h:i A', strtotime(@$classes[$i]['start_time'])) }}
                                                                                            -
                                                                                            {{ date('h:i A', strtotime(@$classes[$i]['end_time'])) }}
                                                                                            <br> </span>
                                                                                        <span class=""> <strong>
                                                                                                {{ $classes[$i]['subject'] }}
                                                                                            </strong>
                                                                                            ({{ $classes[$i]['subject_code'] }})
                                                                                            <br> </span>
                                                                                        @if ($classes[$i]['class_room'])
                                                                                            <span class="">
                                                                                                <strong>@lang('academics.room') :</strong>
                                                                                                {{ $classes[$i]['class_room'] }}
                                                                                                <br> </span>
                                                                                        @endif
                                                                                        @if ($classes[$i]['teacher'])
                                                                                            <span class="">
                                                                                                {{ $classes[$i]['teacher'] }} <br>
                                                                                            </span>
                                                                                        @endif
                                                                                    @endif
                                                                                @endif
        
                                                                            </td>
                                                                        @endforeach
                                                                    @endforeach
                                                                </tr>
                                                            @endfor
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        @endforeach

                        <!-- End Fees Tab -->
                    </div>
                </div>
                <!-- End Student Details -->
            </div>
        </div>

    </section>
@endsection
