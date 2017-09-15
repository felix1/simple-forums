<?php namespace Config;

use App\Domains\Users\UserManager;
use CodeIgniter\Config\Services as CoreServices;
use Myth\Auth\Authenticate\LocalAuthentication;
use Myth\Auth\Authorize\FlatAuthorization;
use Myth\Auth\Authorize\FlatGroupsModel;
use Myth\Auth\Authorize\FlatPermissionsModel;
use Myth\Auth\Config\Auth;
use Myth\Auth\Models\LoginModel;

require_once BASEPATH.'Config/Services.php';

/**
 * Services Configuration file.
 *
 * Services are simply other classes/libraries that the system uses
 * to do its job. This is used by CodeIgniter to allow the core of the
 * framework to be swapped out easily without affecting the usage within
 * the rest of your application.
 *
 * This file holds any application-specific services, or service overrides
 * that you might need. An example has been included with the general
 * method format you should use for your service methods. For more examples,
 * see the core Services file at system/Config/Services.php.
 */
class Services extends CoreServices
{
	/**
	 * Return the authentication library.
	 *
	 * @param bool $getShared
	 *
	 * @return \Myth\Auth\Authenticate\LocalAuthentication
	 */
	public static function authentication(bool $getShared = true)
	{
		if ($getShared)
		{
			return self::getSharedInstance('authentication');
		}

		return new LocalAuthentication(new Auth(), new UserManager(), new LoginModel());
	}

	/**
	 * Return the Authorization library.
	 *
	 * @param bool $getShared
	 *
	 * @return \Myth\Auth\Authorize\FlatAuthorization
	 */
	public static function authorization(bool $getShared=true)
	{
		if ($getShared)
		{
			return self::getSharedInstance('authorization');
		}

		return new FlatAuthorization(new FlatGroupsModel(), new FlatPermissionsModel());
	}
}
