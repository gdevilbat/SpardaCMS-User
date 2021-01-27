<?php

namespace Gdevilbat\SpardaCMS\Modules\User\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use Gdevilbat\SpardaCMS\Modules\Core\Http\Controllers\CoreController;

use Gdevilbat\SpardaCMS\Modules\User\Entities\Group as Group_m;
use Gdevilbat\SpardaCMS\Modules\User\Entities\RltGroupUsers as RltGroupUsers_m;
use Gdevilbat\SpardaCMS\Modules\Core\Entities\Module as Module_m;
use Gdevilbat\SpardaCMS\Modules\Core\Entities\User as User_m;
use Gdevilbat\SpardaCMS\Modules\User\Entities\RltAccessGroup as RltAccessGroup_m;

use Gdevilbat\SpardaCMS\Modules\Core\Repositories\Repository;

use Validator;
use Auth;
use View;

class GroupController extends CoreController
{
    public function __construct()
    {
        parent::__construct();
        $this->group_repository = new Repository(new Group_m, resolve(\Gdevilbat\SpardaCMS\Modules\Role\Repositories\Contract\AuthenticationRepository::class));
        $this->group_repository->setModule('user');
        $this->module_m = new Module_m;
        $this->module_repository = new Repository(new Module_m, resolve(\Gdevilbat\SpardaCMS\Modules\Role\Repositories\Contract\AuthenticationRepository::class));
        $this->access_role_m = new RltAccessGroup_m;
    }

    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        $this->data['modules'] = $this->module_repository->all();
        $this->data['groups'] = $this->group_repository->with(['users', 'modules'])->get();
        return view('user::admin.'.$this->data['theme_cms']->value.'.content.Group.master', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create(Request $request)
    {
        $this->data['method'] = method_field('POST');
        $this->data['users'] = User_m::with('role', 'group')->whereDoesntHave('role', function($query){
                                                                $query->where('slug', \Gdevilbat\SpardaCMS\Modules\Role\Entities\Role::ROLE_SUPER_ADMIN)
                                                                    ->orWhere('slug', \Gdevilbat\SpardaCMS\Modules\Role\Entities\Role::ROLE_ADMIN)
                                                                    ->orWhere('slug', \Gdevilbat\SpardaCMS\Modules\Role\Entities\Role::ROLE_PUBLIC);
                                                            })
                                                            ->where(User_m::getPrimaryKey(), '!=', Auth::id())
                                                        ->get();
        if(isset($_GET['code']))
        {
            $this->data['group'] = $this->group_repository->with('users')->findOrFail(decrypt($request->input('code')));
            $this->data['method'] = method_field('PUT');
            $this->authorize('update-user', $this->data['group']);
        }

        return view('user::admin.'.$this->data['theme_cms']->value.'.content.Group.form', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'group_name' => 'required|max:191',
            'group_email' => 'max:191',
            'group_telp' => 'max:191',
        ]);

        if($request->isMethod('POST'))
        {
            $validator->addRules([
                'slug' => 'max:191|unique:'.Group_m::getTableName().',slug'
            ]);
        }
        else
        {
            $validator->addRules([
                'slug' => 'max:191|unique:'.Group_m::getTableName().',slug,'.decrypt($request->input(\Gdevilbat\SpardaCMS\Modules\User\Entities\Group::getPrimaryKey())).','.\Gdevilbat\SpardaCMS\Modules\User\Entities\Group::getPrimaryKey()
            ]);
        }

        $validator->sometimes('group_email', 'email', function ($input) {
            return strlen($input->group_email) > 0;
        });

        if ($validator->fails()) {
            return redirect()->back()
                        ->withErrors($validator)
                        ->withInput();
        }

        if($request->isMethod('POST'))
        {
            $data = $request->except('_token', '_method', User_m::FOREIGN_KEY);
            $group = new Group_m;
        }
        else
        {
            $data = $request->except('_token', '_method', User_m::FOREIGN_KEY, Group_m::getPrimaryKey());
            $group = $this->group_repository->findOrFail(decrypt($request->input(Group_m::getPrimaryKey())));
            $this->authorize('update-user', $group);
        }

        foreach ($data as $key => $value) 
        {
            $group->$key = $value;
        }

        if($request->isMethod('POST'))
        {
            $group->created_by = Auth::user()->id;
        }

        $group->modified_by = Auth::user()->id;

        if($group->save())
        {

            if($request->has(User_m::FOREIGN_KEY))
            {
                $rlt_group_users = [];
                foreach ($request->input(User_m::FOREIGN_KEY) as $key => $value) 
                {
                    $rlt_group_user = RltGroupUsers_m::where([User_m::FOREIGN_KEY => decrypt($value), Group_m::FOREIGN_KEY => $group->getKey()])->first();
                    if(empty($rlt_group_user))
                    {
                        $rlt_group_user = new RltGroupUsers_m;
                        $rlt_group_user->created_by = Auth::user()->id;
                    }

                    $rlt_group_user[User_m::FOREIGN_KEY] = decrypt($value);

                    if($request->isMethod('POST'))
                    {
                        $rlt_group_user->created_by = Auth::user()->id;
                    }

                    $rlt_group_user->modified_by = Auth::user()->id;

                    $rlt_group_users[] = $rlt_group_user;
                }

                $group->rltGroupUsers()->saveMany($rlt_group_users);


                $remove_related_relation = RltGroupUsers_m::where(Group_m::FOREIGN_KEY, $group->getKey())
                                                        ->whereNotIn(User_m::FOREIGN_KEY, collect($rlt_group_users)->pluck(User_m::FOREIGN_KEY))
                                                        ->pluck(RltGroupUsers_m::getPrimaryKey());

                RltGroupUsers_m::whereIn(RltGroupUsers_m::getPrimaryKey(), $remove_related_relation)->delete();
            }
            else
            {
                RltGroupUsers_m::where(Group_m::FOREIGN_KEY, $group->getKey())->delete();
            }
            
            if($request->isMethod('POST'))
            {
                return redirect(action('\\'.get_class($this).'@index'))->with('global_message', array('status' => 200,'message' => 'Successfully Add Group!'));
            }
            else
            {
                return redirect(action('\\'.get_class($this).'@index'))->with('global_message', array('status' => 200,'message' => 'Successfully Update Group!'));
            }
        }
        else
        {
            if($request->isMethod('POST'))
            {
                return redirect()->back()->with('global_message', array('status' => 400, 'message' => 'Failed To Add Group!'));
            }
            else
            {
                return redirect()->back()->with('global_message', array('status' => 400, 'message' => 'Failed To Update Group!'));
            }
        }
    }

    public function accessScope(Request $request)
    {
        $this->validate($request, [
                'access' => 'required'
        ]);

        $input = $request->input('access');

        foreach ($input as $role_group) 
        {
            foreach($role_group as $value)
            {
                $role =  $this->access_role_m->where(\Gdevilbat\SpardaCMS\Modules\User\Entities\Group::FOREIGN_KEY, decrypt($value[\Gdevilbat\SpardaCMS\Modules\User\Entities\Group::FOREIGN_KEY]))->where(\Gdevilbat\SpardaCMS\Modules\Core\Entities\Module::FOREIGN_KEY, decrypt($value[\Gdevilbat\SpardaCMS\Modules\Core\Entities\Module::FOREIGN_KEY]))->first();
                if(empty($role))
                    $role = new $this->access_role_m;

                $role[\Gdevilbat\SpardaCMS\Modules\User\Entities\Group::FOREIGN_KEY] = decrypt($value[\Gdevilbat\SpardaCMS\Modules\User\Entities\Group::FOREIGN_KEY]);
                $role[\Gdevilbat\SpardaCMS\Modules\Core\Entities\Module::FOREIGN_KEY] = decrypt($value[\Gdevilbat\SpardaCMS\Modules\Core\Entities\Module::FOREIGN_KEY]);
                $role->access_scope = $value['access_scope'];
                if(!$role->save())
                {
                    return redirect(route('cms.group.master'))->with('global_message', array('status' => 400, 'message' => 'Failed To Update Group Provider!'));
                }
            }
        }

        return redirect(route('cms.group.master'))->with('global_message', array('status' => 200, 'message' => 'Successfully To Update Group Provider!'));
    }

    public function checkRole($scope ,$modules, $id)
    {
        $modules = $modules->where(\Gdevilbat\SpardaCMS\Modules\Core\Entities\Module::getPrimaryKey(), $id);
        foreach ($modules as $module) 
        {
            if(!property_exists(json_decode($module->pivot->access_scope), $scope))
                return false;

            return json_decode(json_decode($module->pivot->access_scope)->$scope);
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy(Request $request)
    {
        $query = Group_m::findOrFail(decrypt($request->input(Group_m::getPrimaryKey())));
        $this->authorize('delete-user', $query);

        try {
            if($query->delete())
            {
                return redirect(action('\\'.get_class($this).'@index'))->with('global_message', array('status' => 200,'message' => 'Successfully Delete Group!'));
            }
            
        } catch (\Exception $e) {
            return redirect(action('\\'.get_class($this).'@index'))->with('global_message', array('status' => 400,'message' => 'Failed Delete Group, It\'s Has Been Used!'));
        }
    }
}
