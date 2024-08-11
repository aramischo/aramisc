<x-table>
    <table id="table_id" class="table" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>@lang('aramiscTeacherEvaluation.staff_id')</th>
                <th>@lang('aramiscTeacherEvaluation.teacher_name')</th>
                <th>@lang('aramiscTeacherEvaluation.submitted_by')</th>
                <th>@lang('aramiscTeacherEvaluation.class')(@lang('aramiscTeacherEvaluation.section'))</th>
                <th>@lang('aramiscTeacherEvaluation.rating')</th>
                <th>@lang('aramiscTeacherEvaluation.comment')</th>
                <th>@lang('aramiscTeacherEvaluation.status')</th>
                <th>@lang('aramiscTeacherEvaluation.actions')</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($aramiscTeacherEvaluations as $aramiscTeacherEvaluation)
                @if ($aramiscTeacherEvaluation->status == 1 && $approved_evaluation_button_enable == false)
                    <tr>
                        <td>{{ $aramiscTeacherEvaluation->staff->id }}</td>
                        <td>{{ $aramiscTeacherEvaluation->staff->full_name }}</td>
                        <td>
                            @if ($aramiscTeacherEvaluation->role_id == 2)
                                {{ $aramiscTeacherEvaluation->studentRecord->studentDetail->full_name }}(@lang('aramiscTeacherEvaluation.student'))
                            @else
                                {{ $aramiscTeacherEvaluation->studentRecord->studentDetail->parents->fathers_name }}(@lang('aramiscTeacherEvaluation.parent'))
                            @endif
                        </td>
                        <td>{{ $aramiscTeacherEvaluation->studentRecord->class->class_name }}({{ $aramiscTeacherEvaluation->studentRecord->section->section_name }})
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
                                    name="rating{{ $aramiscTeacherEvaluation->id }}" value="3"
                                    {{ $aramiscTeacherEvaluation->rating == 3 ? 'checked' : '' }}
                                    disabled />
                                <label for="3-stars{{ $aramiscTeacherEvaluation->id }}"
                                    class="star">&#9733;</label>
                                    
                                <input type="radio"
                                    id="2-stars{{ $aramiscTeacherEvaluation->id }}"
                                    name="rating{{ $aramiscTeacherEvaluation->id }}" value="2"
                                    {{ $aramiscTeacherEvaluation->rating == 2 ? 'checked' : '' }}
                                    disabled />
                                <label for="2-stars{{ $aramiscTeacherEvaluation->id }}"
                                    class="star">&#9733;</label>
                                    
                                <input type="radio"
                                    id="1-star{{ $aramiscTeacherEvaluation->id }}"
                                    name="rating{{ $aramiscTeacherEvaluation->id }}" value="1"
                                    {{ $aramiscTeacherEvaluation->rating == 1 ? 'checked' : '' }}
                                    disabled />
                                <label for="1-star{{ $aramiscTeacherEvaluation->id }}"
                                    class="star">&#9733;</label>
                            </div>
                        </td>
                        <td data-bs-toggle="tooltip" title="{{ $aramiscTeacherEvaluation->comment }}">
                            {{ $aramiscTeacherEvaluation->comment }}</td>
                        <td>
                            @if ($aramiscTeacherEvaluation->status == 0)
                                <button
                                    class="primary-btn small bg-danger text-white border-0">@lang('aramiscTeacherEvaluation.pending')</button>
                            @else
                                <button
                                    class="primary-btn small bg-success text-white border-0">@lang('aramiscTeacherEvaluation.approved')</button>
                            @endif
                        </td>
                        <td>
                            <a class="primary-btn small fix-gr-bg"
                                href="{{ route('teacher-evaluation-approve-delete', $aramiscTeacherEvaluation->id) }}"
                                style="padding: 0px 10px;!important"
                                data-bs-toggle="tooltip" title="Delete">&#x292C;</a>
                        </td>
                    </tr>
                @endif
                @if ($aramiscTeacherEvaluation->status == 0 && $approved_evaluation_button_enable == true)
                    <tr>
                        <td>{{ $aramiscTeacherEvaluation->staff->id }}</td>
                        <td>{{ $aramiscTeacherEvaluation->staff->full_name }}</td>
                        <td>
                            @if ($aramiscTeacherEvaluation->role_id == 2)
                                {{ $aramiscTeacherEvaluation->studentRecord->studentDetail->full_name }}(@lang('aramiscTeacherEvaluation.student'))
                            @else
                                {{ $aramiscTeacherEvaluation->studentRecord->studentDetail->parents->fathers_name }}(@lang('aramiscTeacherEvaluation.parent'))
                            @endif
                        </td>
                        <td>{{ $aramiscTeacherEvaluation->studentRecord->class->class_name }}({{ $aramiscTeacherEvaluation->studentRecord->section->section_name }})
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
                                    name="rating{{ $aramiscTeacherEvaluation->id }}" value="3"
                                    {{ $aramiscTeacherEvaluation->rating == 3 ? 'checked' : '' }}
                                    disabled />
                                <label for="3-stars{{ $aramiscTeacherEvaluation->id }}"
                                    class="star">&#9733;</label>
                                <input type="radio"
                                    id="2-stars{{ $aramiscTeacherEvaluation->id }}"
                                    name="rating{{ $aramiscTeacherEvaluation->id }}" value="2"
                                    {{ $aramiscTeacherEvaluation->rating == 2 ? 'checked' : '' }}
                                    disabled />
                                <label for="2-stars{{ $aramiscTeacherEvaluation->id }}"
                                    class="star">&#9733;</label>
                                <input type="radio"
                                    id="1-star{{ $aramiscTeacherEvaluation->id }}"
                                    name="rating{{ $aramiscTeacherEvaluation->id }}" value="1"
                                    {{ $aramiscTeacherEvaluation->rating == 1 ? 'checked' : '' }}
                                    disabled />
                                <label for="1-star{{ $aramiscTeacherEvaluation->id }}"
                                    class="star">&#9733;</label>
                            </div>
                        </td>
                        <td data-bs-toggle="tooltip" title="{{ $aramiscTeacherEvaluation->comment }}">
                            {{ $aramiscTeacherEvaluation->comment }}</td>
                        <td>
                            <button
                                class="primary-btn small bg-danger text-white border-0">@lang('aramiscTeacherEvaluation.pending')</button>
                        </td>
                        <td>
                            <a class="primary-btn small fix-gr-bg"
                                href="{{ route('teacher-evaluation-approve-submit', $aramiscTeacherEvaluation->id) }}"
                                style="padding: 0px 10px;!important"
                                data-bs-toggle="tooltip" title="Approve">&#10003;</a>
                            <a class="primary-btn small fix-gr-bg"
                                href="{{ route('teacher-evaluation-approve-delete', $aramiscTeacherEvaluation->id) }}"
                                style="padding: 0px 10px;!important"
                                data-bs-toggle="tooltip" title="Delete">&#x292C;</a>
                        </td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>
</x-table>
