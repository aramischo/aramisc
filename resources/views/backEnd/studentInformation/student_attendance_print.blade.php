<!DOCTYPE html>
<html lang="en">
<head>
  <title>@lang('student.student_aramiscAttendance')  </title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1"> 
</head>
<style>
 #aramiscAttendance.th,#aramiscAttendance.tr,#aramiscAttendance.td{
     font-size: 10px !important;
     padding: 0px !important;
     text-align: center !important;
     border:1px solid #ddd;
     vertical-align: middle !important;
     
 }
 #aramiscAttendance th{
     background: #ddd;
     text-align: center;
 }
 #aramiscAttendance{
     border: 1px solid var(--border_color);
        border-collapse: collapse;
 }
 #aramiscAttendance tr{
     border: 1px solid var(--border_color);
        border-collapse: collapse;
 }
 #aramiscAttendance th{
     border: 1px solid var(--border_color);
        border-collapse: collapse;
        text-align: center !important;
        font-size: 11px;
 }
 #aramiscAttendance td{
     border: 1px solid var(--border_color);
        border-collapse: collapse;
        text-align: center;
        font-size: 10px;
 }
 table#aramiscAttendance th {
    padding: 5px 10px;
}

table#aramiscAttendance td {
    padding: 5px 10px;
    border-right: 1px solid #d3d3d3;
}
table#aramiscAttendance td:first-child {
    border-left:1px solid #d3d3d3;
}

table#aramiscAttendance tr {
    border-bottom: 1px solid  #d3d3d3;
}
img.logo-img {
  max-width: 110px !important;
}
</style>
<body style="font-family: 'dejavu sans', sans-serif;">
<div class="container-fluid">                     
    <table  cellspacing="0" width="100%">
        <tr>
            <td width="43%"> 
                <img class="logo-img" src="{{ url('/')}}/{{generalSetting()->logo }}" alt=""> 
            </td>
            <td style="padding-left: 10px"> 
                <h3 style="font-size:22px !important" class="text-white"> {{isset(generalSetting()->school_name)?generalSetting()->school_name:'Aramisc School Management ERP'}} </h3>
                <p style="font-size:18px !important" class="text-white mb-0"> {{isset(generalSetting()->address)?generalSetting()->address:'Aramisc School Address'}} </p>
            </td>
            <td style="text-aligh:center"> 
                @if (moduleStatusCheck('University')) 
                <p style="font-size:14px !important; border-bottom:1px solid gray" align="left" class="text-white">{{ __('university::un.department') }}:{{ isset($unSemesterLabel) ? $unSemesterLabel->departmentDetails->name .'('. (isset($unDepartment) ? $unDepartment->name:'').')':''}} </p>
             
                <p style="font-size:14px !important; border-bottom:1px solid gray" align="left" class="text-white">@lang('university::un.semester(label)'): {{ isset($unSemesterLabel) ? $unSemesterLabel->semesterDetails->name .'('. (isset($unSemesterLabel) ? $unSemesterLabel->name : '') .')' :''}} </p>
               @else
                <p style="font-size:14px !important; border-bottom:1px solid gray" align="left" class="text-white">{{ __('academics.class') }}: {{ $class->class_name}} </p>
                <p style="font-size:14px !important; border-bottom:1px solid gray" align="left" class="text-white">{{__('academics.section')}}: {{ $section->section_name}} </p>
                @endif
                <p style="font-size:14px !important; border-bottom:1px solid gray" align="left" class="text-white">{{ __('hr.month') }}: {{ date("F", strtotime('00-'.$month.'-01')) }} </p>
                <p style="font-size:14px !important; border-bottom:1px solid gray" align="left" class="text-white">{{ __('common.year') }}: {{ $year }} </p>
                
            </td>
        </tr>
    </table>
    <h3 style="text-align:center">@lang('student.student_aramiscAttendance_report')</h3>
    <table  style="width: 100%; table-layout: fixed" id="aramiscAttendance">
        <tr>
            <th>SL</th>
            <th width="12%">@lang('common.name')</th>
            <th width="5%">@lang('student.admission_no')</th>
            <th>P</th>
            <th>L</th>
            <th>A</th>
            <th>F</th>
            <th>H</th>
            <th width="5%">%</th>
            @for($i = 1; $i<=$days; $i++)
            <th class="{{($i<=18)? 'all':'none'}}">
                {{$i}} 
                <br>
                @php
                    $date = $year.'-'.$month.'-'.$i;
                    $day = date("D", strtotime($date));
                    echo substr($day,0,2);
                @endphp
            </th>
            @endfor
        </tr>
        @php 
            $total_grand_present = 0; 
            $total_late = 0; 
            $total_absent = 0; 
            $total_holiday = 0; 
            $total_halfday = 0; 
            $count_student=1;
        @endphp
        @foreach($aramiscAttendances as $values)
        @php 
            $total_aramiscAttendance = 0; 
        @endphp
        @php 
            $count_absent = 0; 
        @endphp
        <tr>
            <td>{{$count_student++}}</td>
            <td style="text-align: left !important;">
                @php $student = 0; @endphp
                @foreach($values as $value)
                    @php $student++; @endphp
                    @if($student == 1)
                        {{$value->studentInfo->full_name}}
                    @endif
                @endforeach
            </td>
            <td>
                @php $student = 0; @endphp
                @foreach($values as $value)
                    @php $student++; @endphp
                    @if($student == 1)
                        {{$value->studentInfo->admission_no}}
                    @endif
                @endforeach
            </td>
            <td>
                @php $p = 0; @endphp
                @foreach($values as $value)
                    @if($value->aramiscAttendance_type == 'P')
                        @php $p++; $total_aramiscAttendance++; $total_grand_present++; @endphp
                    @endif
                @endforeach
                {{$p}}
            </td>
            <td>
                @php $l = 0; @endphp
                @foreach($values as $value)
                    @if($value->aramiscAttendance_type == 'L')
                        @php $l++; $total_aramiscAttendance++; $total_late++; @endphp
                    @endif
                @endforeach
                {{$l}}
            </td>
            <td>
                @php $a = 0; @endphp
                @foreach($values as $value)
                    @if($value->aramiscAttendance_type == 'A')
                        @php $a++; $count_absent++; $total_aramiscAttendance++; $total_absent++; @endphp
                    @endif
                @endforeach
                {{$a}}
            </td>
            <td>
                @php $f = 0; @endphp
                @foreach($values as $value)
                    @if($value->aramiscAttendance_type == 'F')
                        @php $f++; $total_aramiscAttendance++; $total_halfday++; @endphp
                    @endif
                @endforeach
                {{$f}}
            </td>
            <td>
                @php $h = 0; @endphp
                @foreach($values as $value)
                    @if($value->aramiscAttendance_type == 'H')
                        @php $h++; $total_aramiscAttendance++; $total_holiday++; @endphp
                    @endif
                @endforeach
                {{$h}}
            </td>
            <td>  
                @php
                    $total_present = $total_aramiscAttendance - $count_absent;
                    if($count_absent == 0){
                        echo '100%';
                    }else{
                        $percentage = $total_present / $total_aramiscAttendance * 100;
                        echo number_format((float)$percentage, 2, '.', '').'%';
                    }
                @endphp
            </td>
            @for($i = 1;  $i<=$days; $i++)
            @php
                $date = $year.'-'.$month.'-'.$i;
                $y = 0;
            @endphp
            <td class="{{($i<=18)? 'all':'none'}}">
                @foreach($values as $value)
                    @if(strtotime($value->aramiscAttendance_date) == strtotime($date))
                        {{$value->aramiscAttendance_type}}
                    @endif
                @endforeach
            </td>
            @endfor
        </tr>
        @endforeach 
    </table>
</div>  
</body>
</html>
    

