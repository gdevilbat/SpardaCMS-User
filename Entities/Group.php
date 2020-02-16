<?php

namespace Gdevilbat\SpardaCMS\Modules\User\Entities;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $fillable = [];
    protected $table = 'group';

    public static function getTableName()
    {
        return with(new Static)->getTable();
    }

    public static function getPrimaryKey()
    {
        return with(new Static)->getKeyName();
    }
}
