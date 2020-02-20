<?php

namespace Gdevilbat\SpardaCMS\Modules\User\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GroupControllerTest extends TestCase
{
	use RefreshDatabase, \Gdevilbat\SpardaCMS\Modules\Core\Tests\ManualRegisterProvider;

    /**
     * A basic test example.
     *
     * @return void
     */
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testReadGroup()
    {
        $response = $this->get(action('\Gdevilbat\SpardaCMS\Modules\User\Http\Controllers\GroupController@index'));

        $response->assertStatus(302)
        		 ->assertRedirect(action('\Gdevilbat\SpardaCMS\Modules\Core\Http\Controllers\Auth\LoginController@showLoginForm')); // Return Not Valid, User Not Login

        $user = \App\User::find(1);

        $response = $this->actingAs($user)
        				 ->from(action('\Gdevilbat\SpardaCMS\Modules\User\Http\Controllers\GroupController@index'))
                         ->json('GET',action('\Gdevilbat\SpardaCMS\Modules\User\Http\Controllers\GroupController@serviceMaster'))
                         ->assertSuccessful()
                         ->assertJsonStructure(['data', 'draw', 'recordsTotal', 'recordsFiltered']); // Return Valid user Login
    }

    public function testFormCreateDataGroup()
    {
    	$response = $this->get(action('\Gdevilbat\SpardaCMS\Modules\User\Http\Controllers\GroupController@create'));

        $response->assertStatus(302)
                 ->assertRedirect(action('\Gdevilbat\SpardaCMS\Modules\Core\Http\Controllers\Auth\LoginController@showLoginForm')); // Return Not Valid, User Not Login

        $user = \App\User::find(1);

        $response = $this->actingAs($user)
        				 ->get(action('\Gdevilbat\SpardaCMS\Modules\User\Http\Controllers\GroupController@create'))
        				 ->assertSuccessful(); // Return Valid user Login
    }

    public function testCreateUpdateDataGroup()
    {
        $user = \App\User::find(1);

        /*===================================
        =            Create Test            =
        ===================================*/
        
        	$response = $this->post(action('\Gdevilbat\SpardaCMS\Modules\User\Http\Controllers\GroupController@store'));

            $response->assertStatus(302)
                     ->assertRedirect(action('\Gdevilbat\SpardaCMS\Modules\Core\Http\Controllers\Auth\LoginController@showLoginForm')); //Return Not Valid, User Not Login


            $response = $this->actingAs($user)
            				 ->from(action('\Gdevilbat\SpardaCMS\Modules\User\Http\Controllers\GroupController@create'))
            				 ->post(action('\Gdevilbat\SpardaCMS\Modules\User\Http\Controllers\GroupController@store'))
            				 ->assertStatus(302)
            				 ->assertRedirect(action('\Gdevilbat\SpardaCMS\Modules\User\Http\Controllers\GroupController@create'))
            				 ->assertSessionHasErrors(); //Return Not Valid, Data Not Complete

    	    $faker = \Faker\Factory::create();
    	    $email = $faker->unique()->safeEmail;
    	    $name = $faker->name;
    	    $password = $faker->name;

    		$response = $this->actingAs($user)
            				 ->from(action('\Gdevilbat\SpardaCMS\Modules\User\Http\Controllers\GroupController@create'))
            				 ->post(action('\Gdevilbat\SpardaCMS\Modules\User\Http\Controllers\GroupController@store'), [
    								'group_email' => $email,
    								'group_name' => $name,
                                    'group_telp' => $name,
                                    'group_address' => $name,
            				 	])
            				 ->assertStatus(302)
            				 ->assertRedirect(action('\Gdevilbat\SpardaCMS\Modules\User\Http\Controllers\GroupController@index'))
            				 ->assertSessionHas('global_message.status', 200)
            				 ->assertSessionHasNoErrors(); //Return Valid, Data Complete

    	 	$this->assertDatabaseHas(\Gdevilbat\SpardaCMS\Modules\User\Entities\Group::getTableName(), ['group_email' => $email]);
        
        /*=====  End of Create Test  ======*/

        
        $group = \Gdevilbat\SpardaCMS\Modules\User\Entities\Group::where(['group_email' => $email])->firstOrFail();


        /*=============================================
        =            Test Update block            =
        =============================================*/

            \Auth::logout();

            $response = $this->post(action('\Gdevilbat\SpardaCMS\Modules\User\Http\Controllers\GroupController@store'), [
                        '_method' => 'PUT'
                    ]);

            $response->assertStatus(302)
                     ->assertRedirect(action('\Gdevilbat\SpardaCMS\Modules\Core\Http\Controllers\Auth\LoginController@showLoginForm')); //Return Not Valid, User Not Login

            $response = $this->actingAs($user)
                            ->from(action('\Gdevilbat\SpardaCMS\Modules\User\Http\Controllers\GroupController@create').'?code='.encrypt($group->getKey()))
                            ->post(action('\Gdevilbat\SpardaCMS\Modules\User\Http\Controllers\GroupController@store'), [
                                'group_email' => $email,
                                'group_name' => $name,
                                'group_telp' => $name,
                                'group_address' => $name,
                                $group->getKeyName() => encrypt($group->getKey()),
                                '_method' => 'PUT'
                            ])
                            ->assertStatus(302)
                            ->assertRedirect(action('\Gdevilbat\SpardaCMS\Modules\User\Http\Controllers\GroupController@index'))
                            ->assertSessionHas('global_message.status', 200)
                            ->assertSessionHasNoErrors(); //Return Valid, Data Complete
        
        /*=====  End of Test Update block  ======*/



        /*=============================================
        =            Section comment block            =
        =============================================*/

            \Auth::logout();
        
            $response = $this->post(action('\Gdevilbat\SpardaCMS\Modules\User\Http\Controllers\GroupController@destroy'), [
                        '_method' => 'DELETE'
                    ]);

            $response->assertStatus(302)
                     ->assertRedirect(action('\Gdevilbat\SpardaCMS\Modules\Core\Http\Controllers\Auth\LoginController@showLoginForm')); //Return Not Valid, User Not Login


            $response = $this->actingAs($user)
                            ->from(action('\Gdevilbat\SpardaCMS\Modules\User\Http\Controllers\GroupController@index'))
                            ->post(action('\Gdevilbat\SpardaCMS\Modules\User\Http\Controllers\GroupController@destroy'), [
                                $group->getKeyName() => encrypt($group->getKey()),
                                '_method' => 'DELETE'
                            ])
                            ->assertStatus(302)
                            ->assertRedirect(action('\Gdevilbat\SpardaCMS\Modules\User\Http\Controllers\GroupController@index'))
                            ->assertSessionHas('global_message.status', 200);

            $this->assertDatabaseMissing(\Gdevilbat\SpardaCMS\Modules\User\Entities\Group::getTableName(), [$group->getKeyName() => $group->getKey()]);
        
        /*=====  End of Section comment block  ======*/

    }
}
