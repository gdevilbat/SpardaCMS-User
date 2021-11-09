<?php

namespace Gdevilbat\SpardaCMS\Modules\User\Repositories;

use Illuminate\Http\Request;

use Gdevilbat\SpardaCMS\Modules\User\Entities\UserMeta as UserMeta_m;
use Gdevilbat\SpardaCMS\Modules\Core\Entities\User;

use Validator;
use Auth;
use ArrayObject;
use stdClass;

/**
 * Class EloquentCoreRepository
 *
 * @package Gdevilbat\SpardaCMS\Modules\Core\Repositories\Eloquent
 */
class UserMetaRepository extends \Gdevilbat\SpardaCMS\Modules\Core\Repositories\AbstractRepository
{
	public function __construct(UserMeta_m $model, \Gdevilbat\SpardaCMS\Modules\Role\Repositories\Contract\AuthenticationRepository $acl)
    {
        parent::__construct($model, $acl);
    }

    public function getMeta($data)
    {
        $validator = Validator::make($data, [
            User::FOREIGN_KEY => 'required',
            'meta_key' => 'required'
        ]);

        if($validator->fails())
            throw new \Illuminate\Http\Exceptions\HttpResponseException(response()->json($validator->errors(), 422));

        $row = $this->model->where(User::FOREIGN_KEY, $data[User::FOREIGN_KEY])
                    ->where('meta_key', $data['meta_key'])
                    ->first();

        if(!empty($row))
        {
            if(is_array($row->meta_value))
            {
                return new SoftObject(json_decode(json_encode($row->meta_value)));
            }

            return $row->meta_value;

        }

        return new SoftObject(json_decode(json_encode([])));
    }

    public function getMetaData($meta_key)
    {
        return $this->getMeta([
            User::FOREIGN_KEY => $this->user->getKey(),
            'meta_key' => $meta_key
        ]);
    }
}

class SoftObject extends ArrayObject{
    private $obj;

    public function __construct($data) {
        if(is_object($data)){
            $this->obj = $data;
        }elseif(is_array($data)){
            // turn it into a multidimensional object
            $this->obj = json_decode(json_encode($data), false);
        }
    }

    public function __get($a) {
        if(isset($this->obj->$a)) {
            return $this->obj->$a;
        }else {
            // return an empty object in order to prevent errors with chain call
            $tmp = new stdClass();
            return new SoftObject($tmp);
        }
    }

    public function __isset($a) {
        return isset($this->obj->$a);
    } 

    public function __set($key, $value) {
        $this->obj->$key = $value;
    }

    public function __call($method, $args) {
        call_user_func_array(Array($this->obj,$method),$args);
    }

    public function get()
    {
        return $this->obj;
    }

    public function __toString() {
        return "";
    }
}
