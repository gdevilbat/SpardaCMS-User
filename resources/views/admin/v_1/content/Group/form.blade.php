@extends('core::admin.'.$theme_cms->value.'.templates.parent')

@section('title_dashboard', 'Group')

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
    <div class="col-sm-12">

        <!--begin::Portlet-->
        <div class="m-portlet m-portlet--tab">
            <div class="m-portlet__head">
                <div class="m-portlet__head-caption">
                    <div class="m-portlet__head-title">
                        <span class="m-portlet__head-icon m--hide">
                            <i class="fa fa-gear"></i>
                        </span>
                        <h3 class="m-portlet__head-text">
                            Group Form
                        </h3>
                    </div>
                </div>
            </div>

            <!--begin::Form-->
            <form class="m-form m-form--fit m-form--label-align-right" action="{{action('\Gdevilbat\SpardaCMS\Modules\User\Http\Controllers\GroupController@store')}}" method="post">
                <div class="m-portlet__body">
                    <div class="col-md-5 offset-md-4">
                        @if (!empty(session('global_message')))
                            <div class="alert {{session('global_message')['status'] == 200 ? 'alert-info' : 'alert-warning' }}">
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
                    <div class="form-group m-form__group d-md-flex">
                        <div class="col-md-4 d-md-flex justify-content-end py-3">
                            <label for="exampleInputEmail1">Group Name<span class="ml-1 m--font-danger" aria-required="true">*</span></label>
                        </div>
                        <div class="col-md-8">
                            <input type="text" class="form-control m-input slugify" placeholder="Group Name" name="group_name" data-target="slug" value="{{old('group_name') ? old('group_name') : (!empty($group) ? $group->group_name : '')}}">
                        </div>
                    </div>
                    <div class="form-group m-form__group d-flex">
                        <div class="col-md-4 d-flex justify-content-end py-3">
                            <label for="exampleInputEmail1">Group Slug<span class="ml-1 m--font-danger" aria-required="true">*</span></label>
                        </div>
                        <div class="col-md-8">
                            <input type="text" class="form-control m-input" name="group_slug" placeholder="Group slug" id="slug" value="{{old('group_slug') ? old('group_slug') : (!empty($group) ? $group->group_slug : '')}}">
                        </div>
                    </div>
                    <div class="form-group m-form__group d-md-flex">
                        <div class="col-md-4 d-md-flex justify-content-end py-3">
                            <label for="exampleInputEmail1">Group Email</label>
                        </div>
                        <div class="col-md-8">
                            <input type="email" class="form-control m-input" placeholder="Group Email" name="group_email" value="{{old('group_email') ? old('group_email') : (!empty($group) ? $group->group_email : '')}}">
                        </div>
                    </div>
                    <div class="form-group m-form__group d-md-flex">
                        <div class="col-md-4 d-md-flex justify-content-end py-3">
                            <label for="exampleInputEmail1">Group Telp</label>
                        </div>
                        <div class="col-md-8">
                            <div class="input-group">
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        +62
                                    </span>
                                </div>
                                <input class="form-control m-input phone" type="text" value="{{!empty($group->group_telp) ? $group->group_telp : old('group_telp')}}" name="group_telp" placeholder="82299xxxx">
                            </div>
                        </div>
                    </div>
                    <div class="form-group m-form__group d-md-flex">
                        <div class="col-md-4 d-md-flex justify-content-end py-3">
                            <label for="exampleInputEmail1">Group Address</label>
                        </div>
                        <div class="col-md-8">
                            <textarea type="text" class="form-control m-input autosize" placeholder="Group Address" name="group_address">{{old('group_address') ? old('group_address') : (!empty($group) ? $group->group_address : '')}}</textarea>
                        </div>
                    </div>
                    <div class="form-group m-form__group d-md-flex">
                        <div class="col-md-4 d-md-flex justify-content-end py-3">
                            <label for="exampleInputEmail1">Member/Staff</label>
                        </div>
                        <div class="col-md-8">
                            <select class="form-control select2" name="user_id[]" multiple placeholder="Select Member Of This Group">
                                @foreach ($users as $user)
                                    @if(!empty($group))
                                        <option value="{{encrypt($user->getKey())}}" {{!empty($group) && $group->users->where('id', $user->getKey())->count() > 0 ? 'selected' : ''}} {{!empty($user->group) && $user->group->getKey() != $group->getKey() ? 'disabled' : ''}}>{{$user->name}}{{!empty($user->group) && $user->group->getKey() != $group->getKey() ? ' -- Terdaftar di '.$user->group->group_name : ''}}</option>
                                    @else
                                        <option value="{{encrypt($user->getKey())}}" {{!empty($user->group) ? 'disabled' : ''}}>{{$user->name}}{{!empty($user->group) ? ' -- Terdaftar di '.$user->group->group_name : ''}}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                {{csrf_field()}}
                @if(isset($_GET['code']))
                    <input type="hidden" name="{{\Gdevilbat\SpardaCMS\Modules\User\Entities\Group::getPrimaryKey()}}" value="{{$_GET['code']}}">
                @endif
                {{$method}}
                <div class="m-portlet__foot m-portlet__foot--fit">
                    <div class="m-form__actions">
                        <div class="offset-md-4">
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </div>
                </div>
            </form>

            <!--end::Form-->
        </div>

        <!--end::Portlet-->

    </div>
</div>
{{-- End of Row --}}

@endsection

@section('page_level_js')
    {{Html::script(module_asset_url('core:assets/js/autosize.min.js'))}}
    {{Html::script(module_asset_url('core:assets/js/slugify.js'))}}
@endsection