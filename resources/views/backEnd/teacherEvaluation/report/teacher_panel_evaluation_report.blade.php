@extends('backEnd.master')
@push('css')
    <style>
        .star-rating {
            display: flex;
            flex-direction: row-reverse;
            font-size: 1.5em;
            justify-content: space-around;
            text-align: center;
            width: 5em;
        }

        .star-rating input {
            display: none;
        }

        .star-rating label {
            color: #ccc;
            cursor: pointer;
        }

        .star-rating :checked~label {
            color: #f90;
        }

        article {
            background-color: #ffe;
            box-shadow: 0 0 1em 1px rgba(0, 0, 0, .25);
            color: #006;
            font-family: cursive;
            font-style: italic;
            margin: 4em;
            max-width: 30em;
            padding: 2em;
        }
    </style>
@endpush
@section('title')
    @lang('aramiscTeacherEvaluation.my_report')
@endsection
@section('mainContent')
    <section class="sms-breadcrumb mb-20">
        <div class="container-fluid">
            <div class="row justify-content-between">
                <h1>@lang('aramiscTeacherEvaluation.my_report')</h1>
                <div class="bc-pages">
                    <a href="{{ route('dashboard') }}">@lang('aramiscTeacherEvaluation.dashboard')</a>
                    <a href="#">@lang('aramiscTeacherEvaluation.teacher_evaluation')</a>
                    <a href="#">@lang('aramiscTeacherEvaluation.my_report')</a>
                </div>
            </div>
        </div>
    </section>
    <section class="admin-visitor-area up_admin_visitor">
        <div class="container-fluid p-0">
            <div class="row mt-20">
                <div class="col-lg-12 student-details up_admin_visitor">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="white-box">
                            <div class="row">
                                <div class="col-lg-4 no-gutters">
                                    <div class="main-title">
                                        <h3 class="mb-15">@lang('aramiscTeacherEvaluation.my_report') </h3>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12">
                                    <x-table>
                                        <table id="table_id" class="table" cellspacing="0" width="100%">
                                            <thead>
                                                <tr>
                                                    <th>@lang('aramiscTeacherEvaluation.class')</th>
                                                    <th>@lang('aramiscTeacherEvaluation.section')</th>
                                                    <th>@lang('aramiscTeacherEvaluation.submitted_by')</th>
                                                    <th>@lang('aramiscTeacherEvaluation.rating')</th>
                                                    <th>@lang('aramiscTeacherEvaluation.comment')</th>
                                                    <th>@lang('aramiscTeacherEvaluation.submitted_on')</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($aramiscTeacherEvaluations as $aramiscTeacherEvaluation)
                                                    <tr>
                                                        <td>{{ $aramiscTeacherEvaluation->studentRecord->class->class_name }}
                                                        </td>
                                                        <td>{{ $aramiscTeacherEvaluation->studentRecord->section->section_name }}
                                                        </td>
                                                        <td>
                                                            @if ($aramiscTeacherEvaluation->role_id == 2)
                                                                @lang('aramiscTeacherEvaluation.student')
                                                            @else
                                                                @lang('aramiscTeacherEvaluation.parent')
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <div class="star-rating">
                                                                <input type="radio"
                                                                    id="5-stars{{ $aramiscTeacherEvaluation->id }}"
                                                                    name="rating{{ $aramiscTeacherEvaluation->id }}" value="5"
                                                                    {{ $aramiscTeacherEvaluation->rating == 5 ? 'checked' : '' }}
                                                                    disabled />
                                                                <label for="5-stars{{ $aramiscTeacherEvaluation->id }}"
                                                                    class="star">&#9733;</label>
                                                                <input type="radio"
                                                                    id="4-stars{{ $aramiscTeacherEvaluation->id }}"
                                                                    name="rating{{ $aramiscTeacherEvaluation->id }}" value="4"
                                                                    {{ $aramiscTeacherEvaluation->rating == 4 ? 'checked' : '' }}
                                                                    disabled />
                                                                <label for="4-stars{{ $aramiscTeacherEvaluation->id }}"
                                                                    class="star">&#9733;</label>
                                                                <input type="radio"
                                                                    id="3-stars{{ $aramiscTeacherEvaluation->id }}"
                                                                    name="rating{{ $aramiscTeacherEvaluation->id }}"
                                                                    value="3"
                                                                    {{ $aramiscTeacherEvaluation->rating == 3 ? 'checked' : '' }}
                                                                    disabled />
                                                                <label for="3-stars{{ $aramiscTeacherEvaluation->id }}"
                                                                    class="star">&#9733;</label>
                                                                <input type="radio"
                                                                    id="2-stars{{ $aramiscTeacherEvaluation->id }}"
                                                                    name="rating{{ $aramiscTeacherEvaluation->id }}"
                                                                    value="2"
                                                                    {{ $aramiscTeacherEvaluation->rating == 2 ? 'checked' : '' }}
                                                                    disabled />
                                                                <label for="2-stars{{ $aramiscTeacherEvaluation->id }}"
                                                                    class="star">&#9733;</label>
                                                                <input type="radio"
                                                                    id="1-star{{ $aramiscTeacherEvaluation->id }}"
                                                                    name="rating{{ $aramiscTeacherEvaluation->id }}"
                                                                    value="1"
                                                                    {{ $aramiscTeacherEvaluation->rating == 1 ? 'checked' : '' }}
                                                                    disabled />
                                                                <label for="1-star{{ $aramiscTeacherEvaluation->id }}"
                                                                    class="star">&#9733;</label>
                                                            </div>
                                                        </td>
                                                        <td data-bs-toggle="tooltip"
                                                            title="{{ $aramiscTeacherEvaluation->comment }}">
                                                            {{ $aramiscTeacherEvaluation->comment }}</td>
                                                        <td>{{ dateConvert($aramiscTeacherEvaluation->created_at) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </x-table>

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
@include('backEnd.partials.data_table_js')
@push('script')
    <script>
        $(document).ready(function() {
            $('[data-bs-toggle="tooltip"]').tooltip();
        });
    </script>
@endpush
