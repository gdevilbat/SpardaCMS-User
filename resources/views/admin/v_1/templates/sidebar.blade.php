@can('menu-user')
    <li class="m-menu__item  {{Route::current()->getName() == 'user' ? 'm-menu__item--active' : ''}}" aria-haspopup="true">
        <a href="{{action('\Gdevilbat\SpardaCMS\Modules\User\Http\Controllers\UserController@index')}}" class="m-menu__link ">
            <i class="m-menu__link-icon flaticon-users"></i>
            <span class="m-menu__link-title"> 
                <span class="m-menu__link-wrap"> 
                    <span class="m-menu__link-text">
                        Users
                    </span>
                 </span>
             </span>
         </a>
    </li>
@endcan
@can('group-user')
    <li class="m-menu__item  {{Route::current()->getName() == 'group' ? 'm-menu__item--active' : ''}}" aria-haspopup="true">
        <a href="{{action('\Gdevilbat\SpardaCMS\Modules\User\Http\Controllers\GroupController@index')}}" class="m-menu__link ">
            <i class="m-menu__link-icon fa fa-user-tie"></i>
            <span class="m-menu__link-title"> 
                <span class="m-menu__link-wrap"> 
                    <span class="m-menu__link-text">
                        Group
                    </span>
                 </span>
             </span>
         </a>
    </li>
@endcan