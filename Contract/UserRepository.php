<?php

namespace Gdevilbat\SpardaCMS\Modules\User\Contract;

use Illuminate\Http\Request;

/**
 * Interface CoreRepository
 * @package Modules\Core\Repositories
 */
interface UserRepository extends \Gdevilbat\SpardaCMS\Modules\Core\Repositories\Contract\BaseRepository
{
    public function __construct(\Gdevilbat\SpardaCMS\Modules\Core\Entities\User $model, \Gdevilbat\SpardaCMS\Modules\Role\Repositories\Contract\AuthenticationRepository $acl);

	/**
	 * [save description]
	 * @param  Request $request [description]
	 * @return [type]           [description]
	 */
    public function save(Request $request, $callback = null);

    /**
     * [validatePost description]
     * @param  Request $request [description]
     * @return [type]           [description]
     */
    public function validateUser(Request $request);
}
