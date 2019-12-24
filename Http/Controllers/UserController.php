<?php

namespace Gdevilbat\SpardaCMS\Modules\User\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use Gdevilbat\SpardaCMS\Modules\Core\Http\Controllers\CoreController;

use Gdevilbat\SpardaCMS\Modules\Role\Entities\Role as Role_m;
use Gdevilbat\SpardaCMS\Modules\Role\Entities\RoleUser as RoleUser_m;
use Gdevilbat\SpardaCMS\Modules\Core\Entities\User as User_m;
use Gdevilbat\SpardaCMS\Modules\Core\Repositories\Repository;

use Validator;
use DB;
use View;
use Auth;
use Storage;

class UserController extends CoreController
{
    public function __construct()
    {
        parent::__construct();
        $this->role_m = new Role_m;
        $this->role_repository = new Repository(new Role_m);
        $this->role_user_m = new RoleUser_m;
        $this->role_user_repository = new Repository(new RoleUser_m);
        $this->user_m = new User_m;
        $this->user_repository = new Repository(new User_m);
    }

    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        return view('user::admin.'.$this->data['theme_cms']->value.'.content.master', $this->data);
    }

    public function serviceMaster(Request $request)
    {
        $column = ['id', 'name', 'email', 'role', 'created_at'];

        $length = !empty($request->input('length')) ? $request->input('length') : 10 ;
        $column = !empty($request->input('order.0.column')) ? $column[$request->input('order.0.column')] : 'id' ;
        $dir = !empty($request->input('order.0.dir')) ? $request->input('order.0.dir') : 'DESC' ;
        $searchValue = $request->input('search')['value'];

        $query = $this->user_m->with('role')
                                ->orderBy($column, $dir)
                                ->limit($length);

        $recordsTotal = $query->count();
        $filtered = $query;

        if($searchValue)
        {
            $filtered->where(DB::raw("CONCAT(name,'-',email,'-',created_at)"), 'like', '%'.$searchValue.'%')
                     ->orWhereHas('role', function($query) use ($searchValue){
                        $query->where(DB::raw("CONCAT(name,'-',slug)"), 'like', '%'.$searchValue.'%');
                     });
        }

        $filteredTotal = $filtered->count();

        $this->data['length'] = $length;
        $this->data['column'] = $column;
        $this->data['dir'] = $dir;
        $this->data['users'] = $filtered->offset($request->input('start'))->limit($length)->get();

        /*=========================================
        =            Parsing Datatable            =
        =========================================*/
            
            $data = array();
            $i = 0;
            foreach ($this->data['users'] as $key_user => $user) 
            {
                if(Auth::user()->can('read-user', $user) && Auth::user()->id != $user->id && $user->role->first()->slug != 'super-admin')
                {
                    $data[$i][0] = $user->id;
                    $data[$i][1] = $user->name;
                    $data[$i][2] = $user->email;
                    $data[$i][3] = $user->role->first()->name;
                    $data[$i][4] = $user->created_at->toDateTimeString();
                    $data[$i][5] = $this->getActionTable($user);
                    $i++;
                }
            }
        
        /*=====  End of Parsing Datatable  ======*/
        
        return ['data' => $data, 'draw' => (integer)$request->input('draw'), 'recordsTotal' => $recordsTotal, 'recordsFiltered' => $filteredTotal];
    }

    private function getActionTable($user)
    {
        $view = View::make('user::admin.'.$this->data['theme_cms']->value.'.content.service_master', [
            'user' => $user
        ]);

        $html = $view->render();
       
       return $html;
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        $this->data['method'] = method_field('POST');
        $this->data['roles'] = $this->role_m->where('slug', '!=', 'super-admin')->get();

        if(isset($_GET['code']))
        {
            $this->data['user'] = $this->user_repository->with('role')->find(decrypt($_GET['code']));
            $this->data['method'] = method_field('PUT');
            $this->authorize('update-user', $this->data['user']);
        }

        return view('user::admin.'.$this->data['theme_cms']->value.'.content.form', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:191',
            'email' => 'required|email|max:191',
            'role_id' => 'required',
            'password' => 'confirmed'
        ]);

        $validator->sometimes('password', 'min:8', function ($input) {
            return strlen($input->password) >= 1;
        });

        if($request->isMethod('POST'))
        {
            $validator->addRules([
                'email' => 'unique:'.$this->user_m->getTable().',email',
                'password' => 'required'
            ]);
        }
        else
        {
            $validator->addRules([
                'email' => 'unique:'.$this->user_m->getTable().',email,'.decrypt($request->input('id')).',id'
            ]);
        }

        if ($validator->fails()) {
            return redirect()->back()
                        ->withErrors($validator)
                        ->withInput();
        }

        if($request->isMethod('POST'))
        {
            $data = $request->except('_token', '_method', 'password_confirmation', 'role_id');
            $user = new $this->user_m;
        }
        else
        {
            $data = $request->except('_token', '_method', 'password_confirmation', 'role_id', 'id');
            $user = $this->user_repository->findOrFail(decrypt($request->input('id')));
            $this->authorize('update-user', $user);
        }

        foreach ($data as $key => $value) 
        {
            $user->$key = $value;
        }

        if($request->isMethod('POST'))
        {
            $user->created_by = Auth::id();
        }

        $user->modified_by = Auth::id();

        if($user->save())
        {
            $role = $this->role_user_repository->find($user->id);
            if(empty($role))
                $role = new $this->role_user_m;

            $role->user_id = $user->id;
            $role->role_id = decrypt($request->input('role_id'));
            $role->save();

            if($request->isMethod('POST'))
            {
                Storage::put('users/'.$user->id.'/'.'.gitattributes', '');
                return redirect(action('\Gdevilbat\SpardaCMS\Modules\User\Http\Controllers\UserController@index'))->with('global_message', array('status' => 200,'message' => 'Successfully Add User!'));
            }
            else
            {
                return redirect(action('\Gdevilbat\SpardaCMS\Modules\User\Http\Controllers\UserController@index'))->with('global_message', array('status' => 200,'message' => 'Successfully Update User!'));
            }
        }
        else
        {
            if($request->isMethod('POST'))
            {
                return redirect()->back()->with('global_message', array('status' => 400, 'message' => 'Failed To Add User!'));
            }
            else
            {
                return redirect()->back()->with('global_message', array('status' => 400, 'message' => 'Failed To Update User!'));
            }
        }
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        return view('user::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        return view('user::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy(Request $request)
    {
        $query = $this->user_m->findOrFail(decrypt($request->input('id')));
        $this->authorize('delete-user', $query);

        try {
            if($query->delete())
            {
                Storage::deleteDirectory('users/'.decrypt($request->input('id')));
                return redirect()->back()->with('global_message', array('status' => 200,'message' => 'Successfully Delete User!'));
            }
            
        } catch (\Exception $e) {
            return redirect()->back()->with('global_message', array('status' => 200,'message' => 'Failed Delete User, It\'s Has Been Used!'));
        }
    }
}
