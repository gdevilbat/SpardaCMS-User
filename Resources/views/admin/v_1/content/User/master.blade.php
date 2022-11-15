@extends('core::admin.'.$theme_cms->value.'.templates.parent')

@section('title_dashboard', ' User')

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
                            Master Data of User
                        </h3>
                    </div>
                </div>
            </div>

            <div class="m-portlet__body">
                <div class="col-md-5">
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

                @if(Auth::user()->can('create-user'))
                    <div class="row mb-4">
                        <div class="col-md-5">
                            <a href="{{action('\Gdevilbat\SpardaCMS\Modules\User\Http\Controllers\UserController@create')}}" class="btn btn-brand m-btn m-btn--custom m-btn--icon m-btn--pill m-btn--air">
                                <span>
                                    <i class="la la-plus"></i>
                                    <span>Add New User</span>
                                </span>
                            </a>
                        </div>
                    </div>
                @else
                    <div class="row mb-4">
                        <div class="col-md-5">
                            <a href="javascript:void(0)" class="btn btn-brand m-btn m-btn--custom m-btn--icon m-btn--pill m-btn--air" data-toggle="m-popover" title="" data-content="You're not Allowed To Take This Action. Pleas Ask Admin !!!" data-original-title="Forbidden Action">
                                <span>
                                    <i class="la la-ban"></i>
                                    <span>Add New User</span>
                                </span>
                            </a>
                        </div>
                    </div>
                @endif

                <!--begin: Datatable -->
                <table class="table table-striped" id="data-user" width="100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th class="no-sort">Role</th>
                            <th>Created At</th>
                            <th class="no-sort">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>

                <!--end: Datatable -->
            </div>


        </div>

        <!--end::Portlet-->

    </div>
</div>
{{-- End of Row --}}

@endsection

@section('page_script_js')
    <script type="text/javascript">
        $(document).ready(function() {
            $('#data-user').DataTable( {
                "processing": true,
                "serverSide": true,
                "order": [],
                "ajax": $.fn.dataTable.pipeline( {
                    url: '{{action('\Gdevilbat\SpardaCMS\Modules\User\Http\Controllers\UserController@serviceMaster')}}',
                    pages: 5 // number of pages to cache
                }),
                 "columnDefs": [
                ],
                "drawCallback": function( settings ) {
                    deleteData();
                }
            } );
        } );
    </script>
@endsection