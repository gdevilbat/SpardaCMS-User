<?php

namespace Gdevilbat\SpardaCMS\Modules\User\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use Gdevilbat\SpardaCMS\Modules\Core\Http\Controllers\CoreController;

use Gdevilbat\SpardaCMS\Modules\User\Entities\Group as Group_m;
use Gdevilbat\SpardaCMS\Modules\User\Entities\RltGroupUsers as RltGroupUsers_m;
use Gdevilbat\SpardaCMS\Modules\Core\Entities\User as User_m;

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
    }

    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        return view('user::admin.'.$this->data['theme_cms']->value.'.content.group.master', $this->data);
    }

    public function serviceMaster(Request $request)
    {
        $column = [Group_m::getPrimaryKey(), 'group_name', 'group_email','group_telp', 'group_address','total_staff','created_at'];;

        $length = !empty($request->input('length')) ? $request->input('length') : 10 ;
        $column = !empty($request->input('order.0.column')) ? $column[$request->input('order.0.column')] : Group_m::getPrimaryKey() ;
        $dir = !empty($request->input('order.0.dir')) ? $request->input('order.0.dir') : 'DESC' ;
        $searchValue = $request->input('search')['value'];

        $query = $this->group_repository->buildQueryByCreatedUser([])
                                        ->with('users')
                                        ->orderBy($column, $dir);

        $recordsTotal = $query->count();
        $filtered = $query;

        if($searchValue)
        {
            $filtered->where(function($query) use ($searchValue){
                        $query->where(DB::raw("CONCAT(group_name,'-',ifnull(".$this->group_repository::getTableName().".group_email,''),'-',ifnull(".$this->group_repository::getTableName().".group_telp,''),'-',ifnull(".$this->group_repository::getTableName().".group_telp,''),'-',ifnull(".$this->group_repository::getTableName().".created_at,''))"), 'like', '%'.$searchValue.'%')
                                ;

                    });
        }

        $filteredTotal = $filtered->count();

        $this->data['length'] = $length;
        $this->data['column'] = $column;
        $this->data['dir'] = $dir;
        $this->data['groups'] = $filtered->offset($request->input('start'))->limit($length)->get();

        $table =  $this->parsingDataTable($this->data['groups']);

        return ['data' => $table, 'draw' => (integer)$request->input('draw'), 'recordsTotal' => $recordsTotal, 'recordsFiltered' => $filteredTotal];
    }

    public function parsingDataTable($groups)
    {
        /*=========================================
        =            Parsing Datatable            =
        =========================================*/
            
            $data = array();
            $i = 0;
            foreach ($groups as $key_group => $group) 
            {
                if(Auth::user()->can('read-user', $group))
                {
                    $data[$i][] = $group->getKey();
                    $data[$i][] = $group->group_name;
                    $data[$i][] = $group->group_email ?: '-';
                    $data[$i][] = $group->group_telp ?: '-';
                    $data[$i][] = $group->group_address ?: '-';
                    $data[$i][] = $group->users->count();

                    if(!empty($group->created_at))
                    {
                        $data[$i][] = $group->created_at->toDateTimeString();
                    }
                    else
                    {
                        $data[$i][] = '-';
                    }

                    $data[$i][] = $this->getActionTable($group);
                    $i++;
                }
            }

            return $data;
        
        /*=====  End of Parsing Datatable  ======*/
    }

    public function getActionTable($group)
    {
        $view = View::make('user::admin.'.$this->data['theme_cms']->value.'.content.Group.service_master', [
            'group' => $group
        ]);

        $html = $view->render();
       
       return $html;
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
                                                        ->get();
        if(isset($_GET['code']))
        {
            $this->data['group'] = $this->group_repository->with('users')->findOrFail(decrypt($request->input('code')));
            $this->data['method'] = method_field('PUT');
            $this->authorize('update-user', $this->data['group']);
        }

        return view('user::admin.'.$this->data['theme_cms']->value.'.content.group.form', $this->data);
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
