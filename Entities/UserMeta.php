<?php

namespace Gdevilbat\SpardaCMS\Modules\User\Entities;

use Illuminate\Database\Eloquent\Model;

class UserMeta extends Model
{
    protected $fillable = [];
    protected $table = 'usermeta';
    protected $primaryKey = 'id_usermeta';
    protected $casts = [
        'meta_value' => 'array',
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
