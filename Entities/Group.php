<?php

namespace Gdevilbat\SpardaCMS\Modules\User\Entities;

use Illuminate\Database\Eloquent\Model;

use Str;

class Group extends Model
{
    protected $fillable = [];
    protected $table = 'group';
    protected $primaryKey = 'id_group';

    const FOREIGN_KEY = "group_id";

    /**
     * Set the user's Slug.
     *
     * @param  string  $value
     * @return void
     */
    public function setGroupSlugAttribute($value)
    {
        $this->attributes['group_slug'] = Str::slug($value, '-');
    }

    public function users()
    {
        return $this->belongsToMany(\Gdevilbat\SpardaCMS\Modules\Core\Entities\User::class, 'rlt_group_users', SELF::FOREIGN_KEY, \Gdevilbat\SpardaCMS\Modules\Core\Entities\User::FOREIGN_KEY);
    }

    public function modules()
    {
        return $this->belongsToMany('\Gdevilbat\SpardaCMS\Modules\Core\Entities\Module', 'rlt_access_group', SELF::FOREIGN_KEY, \Gdevilbat\SpardaCMS\Modules\Core\Entities\Module::FOREIGN_KEY)->withPivot(['access_scope']);
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
