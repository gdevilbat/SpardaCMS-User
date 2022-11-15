<?php

namespace Gdevilbat\SpardaCMS\Modules\User\Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class GroupTest  extends DuskTestCase
{
    use DatabaseMigrations, \Gdevilbat\SpardaCMS\Modules\Core\Tests\ManualRegisterProvider;
    
    /**
     * A basic browser test example.
     *
     * @return void
     */
    public function testCRUDGroup()
    {
        $user = \App\Models\User::find(1);

        //create Role
        $faker = \Faker\Factory::create();

     
        $this->browse(function (Browser $browser) use ($user) {
            $faker = \Faker\Factory::create();
            $password = $faker->name;

            $browser->loginAs($user)
                    ->visit(action('\Gdevilbat\SpardaCMS\Modules\User\Http\Controllers\GroupController@index'))
                    ->assertSee('Master Data of Group')
                    ->clickLink('Add New Group')
                    ->waitForText('Group Form')
                    ->assertSee('Group Form')
                    ->type('group_email', $faker->unique()->safeEmail)
                    ->type('group_name', $faker->name)
                    ->type('group_address', $faker->name)
                    ->type('group_telp', '82299575233');

            $browser->press('Submit')
                    ->waitForText('Master Data of Group')
                    ->assertSee('Successfully Add Group!');

            $browser->waitForText('Actions')
                    ->clickLink('Actions')
                    ->clickLink('Edit')
                    ->type('group_email', $faker->unique()->safeEmail)
                    ->type('group_name', $faker->name)
                    ->type('group_telp', '82299575233')
                    ->type('group_address', $faker->name)
                    ->press('Submit')
                    ->waitForText('Master Data of Group')
                    ->assertSee('Successfully Update Group!');

            $browser->waitForText('Actions')
                    ->clickLink('Actions')
                    ->clickLink('Delete')
                    ->waitForText('Delete Confirmation')
                    ->press('Delete')
                    ->waitForText('Master Data of Group')
                    ->assertSee('Successfully Delete Group!');
        });
    }

}
