<?php

namespace Gdevilbat\SpardaCMS\Modules\User\Tests\Browser;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class UserTest extends DuskTestCase
{
    use DatabaseMigrations, \Gdevilbat\SpardaCMS\Modules\Core\Tests\ManualRegisterProvider;
    
    /**
     * A basic browser test example.
     *
     * @return void
     */
    public function testCRUDUser()
    {
        $user = \App\User::find(1);

        //create Role
        $faker = \Faker\Factory::create();

        $role = new \Gdevilbat\SpardaCMS\Modules\Role\Entities\Role;
        $role->name = $faker->word;
        $role->slug = $faker->word;
        $role->description = $faker->text;
        $role->created_by = $user->getKey();
        $role->modified_by = $user->getKey();
        $role->save();


        $this->browse(function (Browser $browser) use ($user, $role) {
            $faker = \Faker\Factory::create();
            $password = $faker->word;

            $browser->loginAs($user)
                    ->visit(action('\Gdevilbat\SpardaCMS\Modules\User\Http\Controllers\UserController@index'))
                    ->assertSee('Master Data of User')
                    ->clickLink('Add New User')
                    ->waitForText('User Form')
                    ->assertSee('User Form')
                    ->type('email', $faker->unique()->safeEmail)
                    ->type('name', $faker->name)
                    ->type('password', $password)
                    ->type('password_confirmation', $password)
                    ->script('document.getElementsByName("role_id")[0].selectedIndex = 1');

            $browser->press('Submit')
                    ->waitForText('Master Data of User')
                    ->assertSee('Successfully Add User!');

            $browser->waitForText('Actions')
                    ->clickLink('Actions')
                    ->clickLink('Edit')
                    ->type('email', $faker->unique()->safeEmail)
                    ->type('name', $faker->name)
                    ->type('password', $password)
                    ->type('password_confirmation', $password)
                    ->press('Submit')
                    ->waitForText('Master Data of User')
                    ->assertSee('Successfully Update User!');

            $browser->waitForText('Actions')
                    ->clickLink('Actions')
                    ->clickLink('Delete')
                    ->waitForText('Delete Confirmation')
                    ->press('Delete')
                    ->waitForText('Master Data of User')
                    ->assertSee('Successfully Delete User!');
        });
    }

}
