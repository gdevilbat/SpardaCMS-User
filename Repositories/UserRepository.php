<?php

namespace Gdevilbat\SpardaCMS\Modules\User\Repositories;

use Illuminate\Http\Request;

use Gdevilbat\SpardaCMS\Modules\Role\Entities\RoleUser as RoleUser_m;
use Gdevilbat\SpardaCMS\Modules\Core\Repositories\Repository;
use Gdevilbat\SpardaCMS\Modules\User\Entities\UserMeta as UserMeta_m;

use Validator;
use Auth;

/**
 * Class EloquentCoreRepository
 *
 * @package Gdevilbat\SpardaCMS\Modules\Core\Repositories\Eloquent
 */
class UserRepository extends \Gdevilbat\SpardaCMS\Modules\Core\Repositories\AbstractRepository implements \Gdevilbat\SpardaCMS\Modules\User\Contract\UserRepository
{
	public function __construct(\Gdevilbat\SpardaCMS\Modules\Core\Entities\User $model, \Gdevilbat\SpardaCMS\Modules\Role\Repositories\Contract\AuthenticationRepository $acl)
    {
        parent::__construct($model, $acl);
        $this->role_user_m = new RoleUser_m;
        $this->role_user_repository = new Repository(new RoleUser_m, resolve(\Gdevilbat\SpardaCMS\Modules\Role\Repositories\Contract\AuthenticationRepository::class));
        $this->usermeta_m = new UserMeta_m;
    }

    public function save(Request $request, $callback = null)
    {
        $this->validateUser($request)->validate();

        if($request->isMethod('POST'))
        {
            $data = $request->except('_token', '_method', 'password_confirmation', 'role_id', 'meta');
            $user = new $this->model;
        }
        else
        {
            $data = $request->except('_token', '_method', 'password_confirmation', 'role_id', 'id', 'meta');
            $user = $this->model->findOrFail(decrypt($request->input('id')));
            \Auth::user()->can('update-user', $user);
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

            /*=================================
            =            Meta Data            =
            =================================*/

                $meta = [];

                if($request->has('meta'))
                {
                    $meta = $request->input('meta');
                }


                foreach ($meta as $key => $value) 
                {
                    $usermeta = $this->usermeta_m->where(['user_id' => $user->getKey(), 'meta_key' => $key])->first();
                    if(empty($usermeta))
                        $usermeta = new $this->usermeta_m;

                    if(!empty($value))
                    {
                        $usermeta->user_id = $user->getKey();
                        $usermeta->meta_key = $key;
                        $usermeta->meta_value = $value;
                        $usermeta->save();
                    }
                    else
                    {
                        $usermeta->delete();
                    }
                }
            
            /*=====  End of Meta Data  ======*/

            /*==================================================
            =            Callback Action After Post            =
            ==================================================*/

                if(!empty($callback))
                {
                    call_user_func_array(array($this, $callback), array($request, $user));
                }
            
            /*=====  End of Callback Action After Post  ======*/

            return (object) [
                'status' => true,
                'data' => $user
            ];
        }
        else
        {
            return (object) [
                'status' => false
            ];
        }
    }

    public function validateUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:191',
            'email' => 'required|email|max:191',
            'role_id' => 'required',
            'password' => 'confirmed'
        ]);

        if($request->isMethod('POST'))
        {
            $validator->addRules([
                'email' => 'unique:'.$this->model->getTable().',email',
                'password' => 'required'
            ]);
        }
        else
        {
            $validator->addRules([
                'email' => 'unique:'.$this->model->getTable().',email,'.decrypt($request->input('id')).',id'
            ]);
        }

        return $validator;
    }
}
