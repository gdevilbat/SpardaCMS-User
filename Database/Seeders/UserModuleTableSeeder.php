<?php

namespace Gdevilbat\SpardaCMS\Modules\User\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

use DB;

use Gdevilbat\SpardaCMS\Modules\Core\Entities\Module;

class UserModuleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        Module::firstOrCreate(
            ['slug' => 'user'],
            [
                'name' => 'User',
                'scope' => array('menu', 'create', 'read', 'update', 'delete', 'group'),
                'is_scanable' => '1',
                'created_at' => \Carbon\Carbon::now()
            ]
        );
    }
}
