<?php

namespace Gdevilbat\SpardaCMS\Modules\User\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

use DB;

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

        DB::table('module')->insert([
            [
                'name' => 'User',
                'slug' => 'user',
                'scope' => json_encode(array('menu', 'create', 'read', 'update', 'delete')),
                'created_at' => \Carbon\Carbon::now()
            ]
        ]);
    }
}
