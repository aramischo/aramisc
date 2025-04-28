<h4>{{ __('common.Available menu items') }}</h4>
<div class="">
    <div class="row">
        <div class="col-xl-12">
            <!-- menu_setup_wrap  -->
            <div class="dd available_list  menu_item_div menu-list" data-section="1">
                <div class="  available-items-container unused_menu" data-id="remove" data-section_id="remove" data-type="un_used"
                    id="available_list">
                    @php
                        $hasIds = [];
                    @endphp
                    @isset($unused_menus)
                        @if ($unused_menus->count())                            
                            @foreach ($unused_menus as $key=>$menu) 
                                    <ol class="dd-list">
                                        <li class="dd-item" data-id="{{ $menu->id }}"
                                            data-section_id="{{ $menu->parent }}"
                                            data-permission_id="{{ $menu->permission_id }}"
                                            data-parent_route="{{ $menu->parent }}">
                                            <div class="card accordion_card" id="accordion_{{ $menu->id }}">
                                                <div class="card-header item_header" id="heading_{{ $menu->id }}">
                                                    <div class="dd-handle">
                                                        <div class="float-left">
                                                            {{ __($menu->permissionInfo->lang_name) }}
                                                        </div>
                                                    </div>
                                                    <div class="float-right btn_div">
                                                        <div class="edit_icon btn-md" style="top: 35%;padding-right: 30px;">
                                                            <i class="ti-pencil-alt edit_menu" data-id="{{$menu->permissionInfo->id}}"></i>
                                                            <span>&nbsp;|&nbsp;</span>
                                                            {{--<i class="ti-close remove_menu"></i>--}}
                                                            <i class="ti-trash sidebarmenu_delete" data-id="{{$menu->permissionInfo->id}}"></i>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>

                                            <ol class="dd-list">   
                                                @if($menu->count() > 0)                                            
                                                    @foreach ($menu->deActiveChild as $submenu)
                                                            @php
                                                                
                                                            @endphp
                                                            <li class="dd-item" data-id="{{ $submenu->id }}">
                                                                <div class="card accordion_card"
                                                                    id="accordion_{{ $submenu->id }}">
                                                                    <div class="card-header item_header"
                                                                        id="heading_{{ $submenu->id }}">
                                                                        <div class="dd-handle">
                                                                            <div class="float-left">
                                                                                {{ __($submenu->permissionInfo->lang_name) }}
                                                                               
                                                                            </div>
                                                                        </div>

                                                                    </div>
                                                                </div>
                                                            </li>
                                                    
                                                    @endforeach
                                                @endif
                                            </ol>
                                        </li>
                                    </ol>                      
                            @endforeach
                        @else
                            <ol class="dd-list">
                            </ol>

                        @endif
                    @endif
                   
                    </div>
                </div>
            </div>
        </div>
    </div>
