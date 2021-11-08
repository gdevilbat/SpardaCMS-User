<?php

namespace Gdevilbat\SpardaCMS\Modules\User\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use Gdevilbat\SpardaCMS\Modules\Core\Http\Controllers\CoreController;

use Gdevilbat\SpardaCMS\Modules\Role\Entities\Role as Role_m;
use Gdevilbat\SpardaCMS\Modules\Role\Entities\RoleUser as RoleUser_m;
use Gdevilbat\SpardaCMS\Modules\Core\Entities\User as User_m;
use Gdevilbat\SpardaCMS\Modules\User\Entities\UserMeta as UserMeta_m;
use Gdevilbat\SpardaCMS\Modules\Core\Repositories\Repository;

use Validator;
use DB;
use View;
use Auth;
use Storage;

class UserController extends CoreController
{
    public function __construct(\Gdevilbat\SpardaCMS\Modules\User\Contract\UserRepository $user_repository)
    {
        parent::__construct();
        $this->role_m = new Role_m;
        $this->user_m = new User_m;
        $this->user_repository = $user_repository;
    }

    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        return view('user::admin.'.$this->data['theme_cms']->value.'.content.User.master', $this->data);
    }

    public function serviceMaster(Request $request)
    {
        $column = ['id', 'name', 'email', 'role', 'created_at'];

        $length = !empty($request->input('length')) ? $request->input('length') : 10 ;
        $column = $request->input('order.0.column') != null ? $column[$request->input('order.0.column')] : 'id' ;
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
                if(Auth::user()->can('read-user', $user) && Auth::user()->id != $user->id && $user->role->slug != 'super-admin')
                {
                    $data[$i][0] = $user->id;
                    $data[$i][1] = $user->name;
                    $data[$i][2] = $user->email;

                    if(!empty($user->role))
                    {
                        $data[$i][3] = $user->role->name;
                    }
                    else
                    {
                        $data[$i][3] = '-';
                    }

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
        $view = View::make('user::admin.'.$this->data['theme_cms']->value.'.content.User.service_master', [
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
            $this->data['user'] = $this->user_repository->with(['role', 'userMeta'])->find(decrypt($_GET['code']));
            $this->data['method'] = method_field('PUT');
            $this->authorize('update-user', $this->data['user']);
        }

        return view('user::admin.'.$this->data['theme_cms']->value.'.content.User.form', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {
        $response = $this->user_repository->save($request);

        if($response->status)
        {

            if($request->isMethod('POST'))
            {
                Storage::put('users/'.$response->data->id.'/'.'.gitattributes', '');
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
                return redirect(action('\\'.get_class($this).'@index'))->with('global_message', array('status' => 200,'message' => 'Successfully Delete User!'));
            }
            
        } catch (\Exception $e) {
            return redirect(action('\\'.get_class($this).'@index'))->with('global_message', array('status' => 200,'message' => 'Failed Delete User, It\'s Has Been Used!'));
        }
    }
}
