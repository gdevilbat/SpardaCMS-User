<?php

namespace Gdevilbat\SpardaCMS\Modules\User\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserControllerTest extends TestCase
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
    public function testReadUser()
    {
        $response = $this->get(action('\Gdevilbat\SpardaCMS\Modules\User\Http\Controllers\UserController@index'));

        $response->assertStatus(302)
        		 ->assertRedirect(action('\Gdevilbat\SpardaCMS\Modules\Core\Http\Controllers\Auth\LoginController@showLoginForm')); // Return Not Valid, User Not Login

        $user = \App\User::find(1);

        $response = $this->actingAs($user)
        				 ->from(action('\Gdevilbat\SpardaCMS\Modules\User\Http\Controllers\UserController@index'))
                         ->json('GET',action('\Gdevilbat\SpardaCMS\Modules\User\Http\Controllers\UserController@serviceMaster'))
                         ->assertSuccessful()
                         ->assertJsonStructure(['data', 'draw', 'recordsTotal', 'recordsFiltered']); // Return Valid user Login
    }

    public function testFormCreateDataUser()
    {
    	$response = $this->get(action('\Gdevilbat\SpardaCMS\Modules\User\Http\Controllers\UserController@create'));

        $response->assertStatus(302)
                 ->assertRedirect(action('\Gdevilbat\SpardaCMS\Modules\Core\Http\Controllers\Auth\LoginController@showLoginForm')); // Return Not Valid, User Not Login

        $user = \App\User::find(1);

        $response = $this->actingAs($user)
        				 ->get(action('\Gdevilbat\SpardaCMS\Modules\User\Http\Controllers\UserController@create'))
        				 ->assertSuccessful(); // Return Valid user Login
    }

    public function testCreateDataUser()
    {
    	$response = $this->post(action('\Gdevilbat\SpardaCMS\Modules\User\Http\Controllers\UserController@store'));

        $response->assertStatus(302)
                 ->assertRedirect(action('\Gdevilbat\SpardaCMS\Modules\Core\Http\Controllers\Auth\LoginController@showLoginForm')); //Return Not Valid, User Not Login

        $user = \App\User::find(1);

        $response = $this->actingAs($user)
        				 ->from(action('\Gdevilbat\SpardaCMS\Modules\User\Http\Controllers\UserController@create'))
        				 ->post(action('\Gdevilbat\SpardaCMS\Modules\User\Http\Controllers\UserController@store'))
        				 ->assertStatus(302)
        				 ->assertRedirect(action('\Gdevilbat\SpardaCMS\Modules\User\Http\Controllers\UserController@create'))
        				 ->assertSessionHasErrors(); //Return Not Valid, Data Not Complete

	    $faker = \Faker\Factory::create();
	    $email = $faker->unique()->safeEmail;
	    $name = $faker->name;
	    $password = $faker->name;

	    $role = \Gdevilbat\SpardaCMS\Modules\Role\Entities\Role::latest()->first();

		$response = $this->actingAs($user)
        				 ->from(action('\Gdevilbat\SpardaCMS\Modules\User\Http\Controllers\UserController@create'))
        				 ->post(action('\Gdevilbat\SpardaCMS\Modules\User\Http\Controllers\UserController@store'), [
								'email' => $email,
								'name' => $name,
								'password' => $password,
								'password_confirmation' => $password,
								'role_id' => encrypt($role->getKey())
        				 	])
        				 ->assertStatus(302)
        				 ->assertRedirect(action('\Gdevilbat\SpardaCMS\Modules\User\Http\Controllers\UserController@index'))
        				 ->assertSessionHas('global_message.status', 200)
        				 ->assertSessionHasNoErrors(); //Return Valid, Data Complete

	 	$this->assertDatabaseHas(\Gdevilbat\SpardaCMS\Modules\Core\Entities\User::getTableName(), ['email' => $email]);
    }

    public function testUpdateDataUser()
    {
    	$response = $this->post(action('\Gdevilbat\SpardaCMS\Modules\User\Http\Controllers\UserController@store'), [
    					'_method' => 'PUT'
			    	]);

        $response->assertStatus(302)
                 ->assertRedirect(action('\Gdevilbat\SpardaCMS\Modules\Core\Http\Controllers\Auth\LoginController@showLoginForm')); //Return Not Valid, User Not Login


        $user = \Gdevilbat\SpardaCMS\Modules\Core\Entities\User::with('role')->find(1);
        $faker = \Faker\Factory::create();
        $password = $faker->name;

        $response = $this->actingAs($user)
				        ->from(action('\Gdevilbat\SpardaCMS\Modules\User\Http\Controllers\UserController@create').'?code='.encrypt($user->getKey()))
				        ->post(action('\Gdevilbat\SpardaCMS\Modules\User\Http\Controllers\UserController@store'), [
				        	'email' => empty($user->email) ? $faker->unique()->safeEmail : $user->email,
				        	'name' => empty($user->name) ? $faker->name : $user->name,
				        	'role_id' => encrypt($user->role->first()->getKey()),
				        	$user->getKeyName() => encrypt($user->getKey()),
							'_method' => 'PUT'
				    	])
				    	->assertStatus(302)
						->assertRedirect(action('\Gdevilbat\SpardaCMS\Modules\User\Http\Controllers\UserController@index'))
						->assertSessionHas('global_message.status', 200)
						->assertSessionHasNoErrors(); //Return Valid, Data Complete
    }

    public function testDeleteDataUser()
    {
    	$response = $this->post(action('\Gdevilbat\SpardaCMS\Modules\User\Http\Controllers\UserController@destroy'), [
    					'_method' => 'DELETE'
			    	]);

        $response->assertStatus(302)
                 ->assertRedirect(action('\Gdevilbat\SpardaCMS\Modules\Core\Http\Controllers\Auth\LoginController@showLoginForm')); //Return Not Valid, User Not Login


        $user = \App\User::find(1);

        $response = $this->actingAs($user)
				        ->from(action('\Gdevilbat\SpardaCMS\Modules\User\Http\Controllers\UserController@index'))
				        ->post(action('\Gdevilbat\SpardaCMS\Modules\User\Http\Controllers\UserController@destroy'), [
				        	$user->getKeyName() => encrypt($user->getKey()),
							'_method' => 'DELETE'
				    	])
				    	->assertStatus(302)
						->assertRedirect(action('\Gdevilbat\SpardaCMS\Modules\User\Http\Controllers\UserController@index'))
						->assertSessionHas('global_message.status', 200);

		$this->assertDatabaseMissing(\Gdevilbat\SpardaCMS\Modules\Core\Entities\User::getTableName(), [$user->getKeyName() => $user->getKey()]);
    }
}
