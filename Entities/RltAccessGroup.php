<?php

namespace Gdevilbat\SpardaCMS\Modules\User\Entities;

use Illuminate\Database\Eloquent\Model;

class RltAccessGroup extends Model
{
    protected $fillable = [];
    protected $table = "rlt_access_group";
    protected $primaryKey = 'id_rlt_access_group';
     protected $casts = [
        'access_scope' => 'array',
    ];

    public static function getTableName()
    {
        return with(new Static)->getTable();
    }

    public static function getPrimaryKey()
    {
        return with(new Static)->getKeyName();
    }
}
