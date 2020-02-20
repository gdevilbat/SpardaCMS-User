<?php

namespace Gdevilbat\SpardaCMS\Modules\User\Entities;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $fillable = [];
    protected $table = 'group';
    protected $primaryKey = 'id_group';

    const FOREIGN_KEY = "group_id";

    public function users()
    {
        return $this->belongsToMany(\Gdevilbat\SpardaCMS\Modules\Core\Entities\User::class, 'rlt_group_users', SELF::FOREIGN_KEY, \Gdevilbat\SpardaCMS\Modules\Core\Entities\User::FOREIGN_KEY);
    }

    public function rltGroupUsers()
    {
        return $this->hasMany(RltGroupUsers::class, SELF::FOREIGN_KEY);
    }

    public static function getTableName()
    {
        return with(new Static)->getTable();
    }

    public static function getPrimaryKey()
    {
        return with(new Static)->getKeyName();
    }
}
