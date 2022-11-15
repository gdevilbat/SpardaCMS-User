@extends('core::admin.'.$theme_cms->value.'.templates.parent')

@section('title_dashboard', 'User')

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
                    <span class="m-nav__link-text">User</span>
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
                            User Form
                        </h3>
                    </div>
                </div>
            </div>

            <!--begin::Form-->
            <form class="m-form m-form--fit m-form--label-align-right" action="{{action('\Gdevilbat\SpardaCMS\Modules\User\Http\Controllers\UserController@store')}}" method="post">
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
                    <div class="form-group m-form__group d-flex">
                        <div class="col-md-4 d-flex justify-content-end py-3">
                            <label for="exampleInputEmail1">Name<span class="ml-1 m--font-danger" aria-required="true">*</span></label>
                        </div>
                        <div class="col-md-8">
                            <input type="text" class="form-control m-input" placeholder="Full Name" name="name" value="{{old('name') ? old('name') : (!empty($user) ? $user->name : '')}}">
                        </div>
                    </div>
                    <div class="form-group m-form__group d-flex">
                        <div class="col-md-4 d-flex justify-content-end py-3">
                            <label for="exampleInputEmail1">Email<span class="ml-1 m--font-danger" aria-required="true">*</span></label>
                        </div>
                        <div class="col-md-8">
                            <input type="email" class="form-control m-input" placeholder="User Email" name="email" value="{{old('email') ? old('email') : (!empty($user) ? $user->email : '')}}">
                        </div>
                    </div>
                    <div class="form-group m-form__group d-flex">
                        <div class="col-md-4 d-flex justify-content-end py-3">
                            <label for="exampleInputEmail1">Password
                                @if(empty($user))
                                    <span class="ml-1 m--font-danger" aria-required="true">*</span>
                                @endif
                            </label>
                        </div>
                        <div class="col-md-8">
                            <input type="password" class="form-control m-input" placeholder="User Password" name="password">
                        </div>
                    </div>
                    <div class="form-group m-form__group d-flex">
                        <div class="col-md-4 d-flex justify-content-end py-3">
                            <label for="exampleInputEmail1">Re-enter Password
                                @if(empty($user))
                                    <span class="ml-1 m--font-danger" aria-required="true">*</span>
                                @endif
                            </label>
                        </div>
                        <div class="col-md-8">
                            <input type="password" class="form-control m-input" placeholder="Password Confirmation" name="password_confirmation">
                        </div>
                    </div>
                    <div class="form-group m-form__group d-flex">
                        <div class="col-md-4 d-flex justify-content-end py-3">
                            <label for="exampleInputEmail1">Role<span class="ml-1 m--font-danger" aria-required="true">*</span></label>
                        </div>
                        <div class="col-md-8">
                            <select name="role_id" class="form-control m-input m-input--solid">
                                <option value="" selected disabled>-- Select One --</option>
                                @foreach ($roles as $role)
                                    <option value="{{encrypt($role->getKey())}}" {{old('role_id') && old('role_id') == $role->getKey() ? 'selected' : (!empty($user) && $user->role->getKey() == $role->getKey() ? 'selected' : '')}}> {{ucfirst($role->name)}} </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                {{csrf_field()}}
                @if(isset($_GET['code']))
                    <input type="hidden" name="id" value="{{$_GET['code']}}">
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