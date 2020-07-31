@extends('core::admin.'.$theme_cms->value.'.templates.parent')

@section('title_dashboard', ' Group')

@section('breadcrumb')
        <ul class="m-subheader__breadcrumbs m-nav m-nav--inline">
            <li class="m-nav__item m-nav__item--home">
                <a href="#" class="m-nav__link m-nav__link--icon">
                    <i class="m-nav__link-icon la la-home"></i>
                </a>
            </li>
            <li class="m-nav__separator">-</li>
            <li class="m-nav__item">
                <a href="" class="m-nav__link">
                    <span class="m-nav__link-text">Home</span>
                </a>
            </li>
            <li class="m-nav__separator">-</li>
            <li class="m-nav__item">
                <a href="" class="m-nav__link">
                    <span class="m-nav__link-text">Group</span>
                </a>
            </li>
        </ul>
@endsection

@section('content')

<div class="row">
    <div class="col-md-12">

        <!--begin::Portlet-->
        <div class="m-portlet m-portlet--tab">
            <div class="m-portlet__head">
                <div class="m-portlet__head-caption">
                    <div class="m-portlet__head-title">
                        <span class="m-portlet__head-icon m--hide">
                            <i class="fa fa-gear"></i>
                        </span>
                        <h3 class="m-portlet__head-text">
                            Master Data of Group
                        </h3>
                    </div>
                </div>
            </div>

            <div class="m-portlet__body">
                <div class="col-md-8">
                    @if (!empty(session('global_message')))
                        <div class="alert {{session('global_message')['status'] == 200 ? 'alert-info' : 'alert-warning' }} alert-dismissible fade show">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"></button>
                            {{session('global_message')['message']}}
                        </div>
                    @endif
                    @if (count($errors) > 0)
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>

                @can('create-user')
                    <div class="row mb-4">
                        <div class="col-md-5">
                            <a href="{{action('\Gdevilbat\SpardaCMS\Modules\User\Http\Controllers\GroupController@create')}}" class="btn btn-brand m-btn m-btn--custom m-btn--icon m-btn--pill m-btn--air">
                                <span>
                                    <i class="la la-plus"></i>
                                    <span>Add New Group</span>
                                </span>
                            </a>
                        </div>
                    </div>
                @endcan

                <form action="{{route('cms.group-scope.store')}}" method="post" id="form-role">
                    <div class="table-responsive">
                        <table class="table table-striped" id="html_table" width="100%">
                            <thead>
                                <thead>
                                    <tr>
                                        <th style="width: 10px">No.</th>
                                        <th>Group Name</th>
                                        <th class="no-sort">Total Staff</th>
                                        @foreach($modules as $module)
                                            <th><center>{{title_case($module->slug)}}</center></th>
                                        @endforeach
                                        <th style="width: 50px; vertical-align: middle;">Action</th>
                                    </tr>
                                </thead>
                            </thead>
                            <tbody>
                                @foreach ($groups as $group)
                                    @if(empty(Auth::user()->group) || (!empty(Auth::user()->group) && Auth::user()->group->group_slug != $group->group_slug))
                                        <tr>
                                            <td>{{ $group->getKey() }}</td>
                                            <td>{{ $group->group_name }}</td>
                                            <td>{{ $group->users->count() }}</td>
                                            @foreach ($modules as $module)
                                                <td>
                                                    @can('permission-'.$module->slug)
                                                        <div class="m-form__group form-group">
                                                            <div class="m-checkbox-list">
                                                                @foreach($module->scope as $scope)
                                                                    <label class="m-checkbox">
                                                                           <input type="checkbox" class="checkbox" {{Route::current()->getController()->checkRole( $scope, $group->modules, $module->getKey()) ? "checked" : ""}}>
                                                                            {{$scope}}
                                                                            <input type="hidden" class="role" name="access[{{$loop->parent->parent->index}}][{{$loop->parent->index}}][access_scope][{{$scope}}]">
                                                                            <input type="hidden" name="access[{{$loop->parent->parent->index}}][{{$loop->parent->index}}][{{\Gdevilbat\SpardaCMS\Modules\User\Entities\Group::FOREIGN_KEY}}]" value="{{encrypt($group->getKey())}}">
                                                                            <input type="hidden" name="access[{{$loop->parent->parent->index}}][{{$loop->parent->index}}][{{\Gdevilbat\SpardaCMS\Modules\Core\Entities\Module::FOREIGN_KEY}}]" value="{{encrypt($module->getKey())}}">
                                                                            <span></span>
                                                                    </label>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @endcan
                                                </td>
                                            @endforeach
                                            <td style="vertical-align: middle;">
                                                <div class="btn-group">
                                                    <a href="javascript:void(0)" class="btn btn-outline-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        Actions
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-left">
                                                        @can('group-user', $group)
                                                            <button class="dropdown-item" type="button">
                                                                <a class="m-link m-link--state m-link--info" href="{{action('\Gdevilbat\SpardaCMS\Modules\User\Http\Controllers\GroupController@create').'?code='.encrypt($group->getKey())}}"><i class="fa fa-edit"> Edit</i></a>
                                                            </button>
                                                        @endcan
                                                        @can('group-user', $group)
                                                            <form action="{{action('\Gdevilbat\SpardaCMS\Modules\User\Http\Controllers\GroupController@destroy')}}" method="post" accept-charset="utf-8">
                                                                {{method_field('DELETE')}}
                                                                {{csrf_field()}}
                                                                <input type="hidden" name="{{\Gdevilbat\SpardaCMS\Modules\User\Entities\Group::getPrimaryKey()}}" value="{{encrypt($group->getKey())}}">
                                                            </form>
                                                            <button class="dropdown-item confirm-delete" type="button"><a class="m-link m-link--state m-link--accent" data-toggle="modal" href="#small"><i class="fa fa-trash"> Delete</i></a></button>
                                                        @endcan
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{method_field('POST')}}
                    {{csrf_field()}}
                    @can('group-user')
                        <div class="col-md-12 d-flex justify-content-end">
                            <button id="submit-role" type="button" class="btn btn-info m-btn m-btn--custom m-btn--icon m-btn--pill m-btn--air">Submit</button>
                        </div>
                    @endcan
                </form>

            </div>


        </div>

        <!--end::Portlet-->

    </div>
</div>
{{-- End of Row --}}

@endsection

@section('page_level_js')
    {{Html::script(module_asset_url('role:resources/views/admin/'.$theme_cms->value.'/js/role.js').'?id='.filemtime(module_asset_path('role:resources/views/admin/'.$theme_cms->value.'/js/role.js')))}}
@endsection