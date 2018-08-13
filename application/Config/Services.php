<?php namespace Config;

use App\Domains\Users\UserModel;
use CodeIgniter\Config\Services as CoreServices;
use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Model;
use Myth\Auth\Authorization\FlatAuthorization;
use Myth\Auth\Authorization\GroupModel;
use Myth\Auth\Config\Auth;
use Myth\Auth\Models\LoginModel;
use Myth\Authorization\PermissionModel;

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
    public static function honeypot(BaseConfig $config = null, $getShared = true)
    {
        if ($getShared)
        {
            return self::getSharedInstance('honeypot', $config);
        }

        if (is_null($config))
        {
            $config = new \Config\Honeypot();
        }

        return new \CodeIgniter\Honeypot\Honeypot($config);
    }

	public static function authentication(string $lib = 'local', Model $userModel=null, Model $loginModel=null, bool $getShared = true)
	{
		if ($getShared)
		{
			return self::getSharedInstance('authentication', $lib, $userModel, $loginModel);
		}

		$config = config(Auth::class);

		$class = $config->authenticationLibs[$lib];

		$instance = new $class($config);

		if (empty($userModel))
		{
			$userModel = new UserModel();
		}

		if (empty($loginModel))
		{
			$loginModel = new LoginModel();
		}

		return $instance
			->setUserModel($userModel)
			->setLoginModel($loginModel);
	}

	public static function authorization(Model $groupModel=null, Model $permissionModel=null, Model $userModel=null, bool $getShared = true)
	{
		if ($getShared)
		{
			return self::getSharedInstance('authorization', $groupModel, $permissionModel);
		}

		if (is_null($groupModel))
		{
			$groupModel = new GroupModel();
		}

		if (is_null($permissionModel))
		{
			$permissionModel = new PermissionModel();
		}

		$instance = new FlatAuthorization($groupModel, $permissionModel);

		if (is_null($userModel))
		{
			$userModel = new UserModel();
		}

		return $instance->setUserModel($userModel);
	}

	/**
	 * Returns an instance of the password validator.
	 *
	 * @param null $config
	 * @param bool $getShared
	 *
	 * @return mixed|PasswordValidator
	 */
	public static function passwords($config = null, bool $getShared = true)
	{
		if ($getShared)
		{
			return self::getSharedInstance('passwords', $config);
		}

		if (empty($config))
		{
			$config = config(Auth::class);
		}

		return new PasswordValidator($config);
	}

}
