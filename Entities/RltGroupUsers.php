<?php

namespace Gdevilbat\SpardaCMS\Modules\User\Entities;

use Illuminate\Database\Eloquent\Model;

class RltGroupUsers extends Model
{
    protected $fillable = [];
    protected $table = "rlt_group_users";
    protected $primaryKey = 'id_rlt_group_users';

    public static function getTableName()
    {
        return with(new Static)->getTable();
    }

    public static function getPrimaryKey()
    {
        return with(new Static)->getKeyName();
    }
}
