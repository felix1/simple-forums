<?php namespace Myth\Auth\Authenticate;

/**
 * Sprint
 *
 * A set of power tools to enhance the CodeIgniter framework and provide consistent workflow.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package     Sprint
 * @author      Lonnie Ezell
 * @copyright   Copyright 2014-2015, New Myth Media, LLC (http://newmythmedia.com)
 * @license     http://opensource.org/licenses/MIT  (MIT)
 * @link        http://sprintphp.com
 * @since       Version 1.0
 */
use App\Domains\Users\User;
use App\Domains\Users\UserModel;
use CodeIgniter\HTTP\Request;
use Config\App;
use Config\Services;
use CodeIgniter\Events\Events;
use Myth\Auth\Models\LoginModel;

/**
 * Class LocalAuthentication
 *
 * Provides most of the Authentication that web applications would need,
 * at least as far as local authentication goes. It does NOT provide
 * social authentication through third-party applications.
 *
 * The system attempts to incorporate as many of the ideas and best practices
 * set forth in the following documents:
 *
 *  - http://stackoverflow.com/questions/549/the-definitive-guide-to-form-based-website-authentication
 *  - https://www.owasp.org/index.php/Guide_to_Authentication
 *
 * todo: Set the error string for all error states here.
 *
 * @package Myth\Auth
 */
class LocalAuthentication implements AuthenticateInterface
{
	protected $user;

	/**
	 * @var \App\Domains\Users\UserModel
	 */
	public $userModel;

	/**
	 * @var \Myth\Auth\Models\LoginModel
	 */
	public $loginModel;

	public $error;

	/**
	 * @var \Myth\Auth\Config\Auth
	 */
	protected $config;

	//--------------------------------------------------------------------

	public function __construct($config, UserModel $userModel, LoginModel $loginModel)
	{
		$this->config     = $config;
		$this->userModel  = $userModel;
		$this->loginModel = $loginModel;

		// Ensure session is running
		session()->start();
	}

	//--------------------------------------------------------------------

	/**
	 * Attempt to log a user into the system.
	 *
	 * $credentials is an array of key/value pairs needed to log the user in.
	 * This is often email/password, or username/password.
	 *
	 * @param array $credentials
	 * @param bool  $remember
	 *
	 * @return bool|mixed
	 */
	public function login($credentials, $remember = false)
	{
		$user = $this->validate($credentials, true);

		if (! $user)
		{
			$this->user = null;

			return $user;
		}

		$this->loginUser($user);

		if ($remember)
		{
			$this->rememberUser($user);
		}

		Events::trigger('didLogin', [$user]);

		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Validates user login information without logging them in.
	 *
	 * $credentials is an array of key/value pairs needed to log the user in.
	 * This is often email/password, or username/password.
	 *
	 * @param      $credentials
	 * @param bool $return_user
	 *
	 * @return mixed
	 */
	public function validate($credentials, $return_user = false)
	{
		// Can't validate without a password.
		if (empty($credentials['password']) || count($credentials) < 2)
		{
			return null;
		}

		$password = $credentials['password'];
		unset($credentials['password']);

		// We should only be allowed 1 single other credential to
		// test against.
		if (count($credentials) > 1)
		{
			$this->error = lang('Auth.tooManyCredentials');

			return false;
		}

		// Ensure that the fields are allowed validation fields
		if (! in_array(key($credentials), $this->config->validFields))
		{
			$this->error = lang('Auth.invalidCredentials');

			return false;
		}

		// We do not want to force case-sensitivity on things
		// like username and email for usability sake.
		if (! empty($credentials['email']))
		{
			$credentials['email'] = strtolower($credentials['email']);
		}

		// Can we find a user with those credentials?
		$user = $this->userModel->where($credentials)
		                        ->first();

		// Get ip address
		$ip_address = Services::request()
		                      ->getIPAddress();

		if (! $user)
		{
			$this->error = lang('Auth.invalidUser');
			$this->loginModel->recordLoginAttempt($ip_address);

			return false;
		}

		// Now, try matching the passwords.
		$result = password_verify($password, $user->password_hash);

		if (! $result)
		{
			$this->error = lang('Auth.invalidPassword');
			$this->loginModel->recordLoginAttempt($ip_address, $user->id);

			return false;
		}

		// Check to see if the password needs to be rehashed.
		// This would be due to the hash algorithm or hash
		// cost changing since the last time that a user
		// logged in.
		if (password_needs_rehash($user->password_hash, PASSWORD_DEFAULT, ['cost' => $this->config->hashCost]))
		{
			$user->password_hash = Password::hashPassword($password);
			$this->userModel->save($user);
		}

		// Is the user active?
		if (! $user->active)
		{
			$this->error = lang('Auth.inactiveAccount');

			return false;
		}

		return $return_user ? $user : true;
	}

	//--------------------------------------------------------------------

	/**
	 * Logs a user out and removes all session information.
	 *
	 * @return mixed
	 */
	public function logout()
	{
		helper('cookie');

		if (! Events::trigger('beforeLogout', [$this->user]))
		{
			return false;
		}

		// Destroy the session data - but ensure a session is still
		// available for flash messages, etc.
		if (isset($_SESSION))
		{
			foreach ($_SESSION as $key => $value)
			{
				$_SESSION[$key] = null;
				unset($_SESSION[$key]);
			}
		}
		// Also, regenerate the session ID for a touch of added safety.
		session()->regenerate(true);

		// Take care of any remember me functionality.
		if ($this->config->allowRemembering)
		{
			$token = get_cookie('remember');

			$this->invalidateRememberCookie($this->user->email, $token);
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Checks whether a user is logged in or not.
	 *
	 * @return bool
	 */
	public function isLoggedIn()
	{
		$id = session('logged_in');

		if (! $id)
		{
			return false;
		}

		// If the user var hasn't been filled in, we need to fill it in,
		// since this method will typically be used as the only method
		// to determine whether a user is logged in or not.
		if (! $this->user)
		{
			$this->user = $this->userModel->find($id);

			if (empty($this->user))
			{
				return false;
			}
		}

		// If logged in, ensure cache control
		// headers are in place
		$this->setHeaders();

		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Attempts to log a user in based on the "remember me" cookie.
	 *
	 * @return bool
	 */
	public function viaRemember()
	{
		if (! $this->config->allowRemembering)
		{
			return false;
		}

		helper('cookie');

		if (! $token = get_cookie('remember'))
		{
			return false;
		}

		// Attempt to match the token against our auth_tokens table.
		$query = $this->userModel->db
			->where('hash', $this->loginModel->hashRememberToken($token))
			->get('auth_tokens');
		dd($query);
		if (! $query->num_rows())
		{
			return false;
		}

		// Grab the user
		$email = $query->row()->email;

		$user = $this->userModel->as_array()
		                        ->find_by('email', $email);

		$this->loginUser($user);

		// We only want our remember me tokens to be valid
		// for a single use.
		$this->refreshRememberCookie($user, $token);

		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Registers a new user and handles activation method.
	 *
	 * @param User $user
	 *
	 * @return bool
	 */
	public function registerUser($user)
	{
		// Anything special needed for Activation?
		$method = $this->config->activationMethod;

		$user->active = $method == 'auto' ? 1 : 0;

		// If via email, we need to generate a hash
		helper('text');
		$token               = random_string('alnum', 24);
		$user->activate_hash = hash('sha1', $this->config->salt.$token);

		// Save the user
		if (! $id = $this->userModel->insert($user))
		{
			$this->error = $this->userModel->errors();

			return false;
		}

		$user->id = $id;

		$data = [
			'user'   => $user,
			'token'  => $token,
			'method' => $method,
		];

		Events::trigger('didRegisterUser', [$data]);

		return $user;
	}

	//--------------------------------------------------------------------

	/**
	 * Used to verify the user values and activate a user so they can
	 * visit the site.
	 *
	 * @param $data
	 *
	 * @return bool
	 */
	public function activateUser($data)
	{
		$post = [
			'email'         => $data['email'],
			'activate_hash' => hash('sha1', config_item('auth.salt').$data['code']),
		];

		$user = $this->userModel->where($post)
		                        ->first();

		if (! $user)
		{
			$this->error = $this->userModel->error() ? $this->userModel->error() : lang('auth.activate_no_user');

			return false;
		}

		if (! $this->userModel->update($user->id, ['active' => 1, 'activate_hash' => null]))
		{
			$this->error = $this->userModel->error();

			return false;
		}

		Events::trigger('didActivate', [(array)$user]);

		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Used to allow manual activation of a user with a known ID.
	 *
	 * @param $id
	 *
	 * @return bool
	 */
	public function activateUserById($id)
	{
		if (! $this->userModel->update($id, ['active' => 1, 'activate_hash' => null]))
		{
			$this->error = $this->userModel->error();

			return false;
		}

		Events::trigger('didActivate', [
			$this->userModel->as_array()
			                ->find($id),
		]);

		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Grabs the current user object. Returns NULL if nothing found.
	 *
	 * @return array|null
	 */
	public function user()
	{
		return $this->user;
	}

	//--------------------------------------------------------------------

	/**
	 * A convenience method to grab the current user's ID.
	 *
	 * @return int|null
	 */
	public function id()
	{
		if (! is_array($this->user) || empty($this->user['id']))
		{
			return null;
		}

		return (int)$this->user['id'];
	}

	//--------------------------------------------------------------------

	/**
	 * Checks to see if the user is currently being throttled.
	 *
	 *  - If they are NOT, will return FALSE.
	 *  - If they ARE, will return the number of seconds until they can try again.
	 *
	 * @param $user
	 *
	 * @return mixed
	 */
	public function isThrottled($user)
	{
		// Not throttling? Get outta here!
		if (! config_item('auth.allow_throttling'))
		{
			return false;
		}

		// Get user_id
		$user_id = $user ? $user['id'] : null;

		// Get ip address
		$ip_address = $this->ci->input->ip_address();

		// Have any attempts been made?
		$attempts = $this->ci->login_model->countLoginAttempts($ip_address, $user_id);

		// Grab the amount of time to add if the system thinks we're
		// under a distributed brute force attack.
		// Affect users that have at least 1 failure login attempt
		$dbrute_time = ($attempts === 0) ? 0 : $this->ci->login_model->distributedBruteForceTime();

		// If this user was found to possibly be under a brute
		// force attack, their account would have been banned
		// for 15 minutes.
		if ($time = isset($_SESSION['bruteBan']) ? $_SESSION['bruteBan'] : false)
		{
			// If the current time is less than the
			// the ban expiration, plus any distributed time
			// then the user can't login just yet.
			if ($time+$dbrute_time > time())
			{
				// The user is banned still...
				$this->error = lang('auth.bruteBan_notice');

				return ($time+$dbrute_time)-time();
			}

			// Still here? The the ban time is over...
			unset($_SESSION['bruteBan']);
		}

		// Grab the time of last attempt and
		// determine if we're throttled by amount of time passed.
		$last_time = $this->ci->login_model->lastLoginAttemptTime($ip_address, $user_id);

		$allowed = config_item('auth.allowed_login_attempts');

		// We're not throttling if there are 0 attempts or
		// the number is less than or equal to the allowed free attempts
		if ($attempts === 0 || $attempts < $allowed)
		{
			// Before we can say there's nothing up here,
			// we need to check dbrute time.
			$time_left = $last_time+$dbrute_time-time();

			if ($time_left > 0)
			{
				return $time_left;
			}

			return false;
		}

		// If the number of attempts is excessive (above 100) we need
		// to check the elapsed time of all of these attacks. If they are
		// less than 1 minute it's obvious this is a brute force attack,
		// so we'll set a session flag and block that user for 15 minutes.
		if ($attempts > 100 && $this->ci->login_model->isBruteForced($ip_address, $user_id))
		{
			$this->error = lang('auth.bruteBan_notice');

			$ban_time             = 60*15;    // 15 minutes
			$_SESSION['bruteBan'] = time()+$ban_time;

			return $ban_time;
		}

		// Get our allowed attempts out of the picture.
		$attempts = $attempts-$allowed;

		$max_time = config_item('auth.max_throttle_time');

		$add_time = 5*pow(2, $attempts);

		if ($add_time > $max_time)
		{
			$add_time = $max_time;
		}

		$next_time = $last_time+$add_time+$dbrute_time;

		$current = time();

		// We are NOT throttled if we are already
		// past the allowed time.
		if ($current > $next_time)
		{
			return false;
		}

		return $next_time-$current;
	}

	//--------------------------------------------------------------------

	/**
	 * Sends a password reset link email to the user associated with
	 * the passed in $email.
	 *
	 * @param $email
	 *
	 * @return mixed
	 */
	public function remindUser($email)
	{
		// Emails should NOT be case sensitive.
		$email = strtolower($email);

		// Is it a valid user?
		$user = $this->userModel->findWhere('email', $email);

		if (! $user)
		{
			$this->error = lang('auth.invalid_email');

			return false;
		}

		// Should be an array returned, even if only with a single item.
		$user = $user[0];

		// Generate/store our codes
		helper('text');
		$token = random_string('alnum', 24);

		$user->reset_hash = hash('sha1', $this->config->salt.$token);

		$result = $this->userModel->save($user);

		if (! $result)
		{
			$this->error = $this->userModel->error();

			return false;
		}

		Events::trigger('didRemindUser', [$user, $token]);

		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Validates the credentials provided and, if valid, resets the password.
	 *
	 * The $credentials array MUST contain a 'code' key with the string to
	 * hash and check against the reset_hash.
	 *
	 * @param $credentials
	 * @param $password
	 * @param $passConfirm
	 *
	 * @return mixed
	 */
	public function resetPassword($credentials, $password, $passConfirm)
	{
		if (empty($credentials['code']))
		{
			$this->error = lang('auth.need_reset_code');

			return false;
		}

		// Generate a hash to match against the table.
		$reset_hash = hash('sha1', config_item('auth.salt').$credentials['code']);
		unset($credentials['code']);

		if (! empty($credentials['email']))
		{
			$credentials['email'] = strtolower($credentials['email']);
		}

		// Is there a matching user?
		$user = $this->userModel->as_array()
		                        ->where($credentials)
		                        ->first();

		// If throttling time is above zero, we can't allow
		// logins now.
		$time = (int)$this->isThrottled($user);
		if ($time > 0)
		{
			$this->error = sprintf(lang('auth.throttled'), $time);

			return false;
		}

		// Get ip address
		$ip_address = $this->ci->input->ip_address();

		if (! $user)
		{
			$this->error = lang('auth.reset_no_user');
			$this->ci->login_model->recordLoginAttempt($ip_address);

			return false;
		}

		// Is generated reset_hash string matches one from the table?
		if ($reset_hash !== $user['reset_hash'])
		{
			$this->error = lang('auth.reset_no_user');
			$this->ci->login_model->recordLoginAttempt($ip_address, $user['id']);

			return false;
		}

		// Update their password and reset their reset_hash
		$data = [
			'password'     => $password,
			'pass_confirm' => $passConfirm,
			'reset_hash'   => null,
		];

		if (! $this->userModel->update($user['id'], $data))
		{
			$this->error = $this->userModel->error();

			return false;
		}

		// Clear our login attempts
		$this->ci->login_model->purgeLoginAttempts($ip_address, $user['id']);

		Events::trigger('didResetPassword', [$user]);

		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Provides a way for implementations to allow new statuses to be set
	 * on the user. The details will vary based upon implementation, but
	 * will often allow for banning or suspending users.
	 *
	 * @param      $newStatus
	 * @param null $message
	 *
	 * @return mixed
	 */
	public function changeStatus($newStatus, $message = null)
	{
		// todo actually record new users status!
	}

	//--------------------------------------------------------------------

	/**
	 * Allows the consuming application to pass in a reference to the
	 * model that should be used.
	 *
	 * The model MUST extend Myth\Models\CIDbModel.
	 *
	 * @param      $model
	 * @param bool $allow_any_parent
	 *
	 * @return mixed
	 */
	public function useModel($model, $allow_any_parent = false)
	{
		if (! $allow_any_parent && get_parent_class($model) != 'CodeIgniter\Model')
		{
			throw new \RuntimeException('Models passed into LocalAuthenticate MUST extend CodeIgniter\Model');
		}

		$this->userModel =& $model;

		return $this;
	}

	//--------------------------------------------------------------------

	public function error()
	{
		return $this->error;
	}

	//--------------------------------------------------------------------

	//--------------------------------------------------------------------
	// Login Records
	//--------------------------------------------------------------------

	/**
	 * Purges all login attempt records from the database.
	 *
	 * @param null $ip_address
	 * @param null $user_id
	 */
	public function purgeLoginAttempts($ip_address = null, $user_id = null)
	{
		$this->ci->login_model->purgeLoginAttempts($ip_address, $user_id);

		// @todo record activity of login attempts purge.
		Events::trigger('didPurgeLoginAttempts', [$email]);
	}

	//--------------------------------------------------------------------

	/**
	 * Purges all remember tokens for a single user. Effectively logs
	 * a user out of all devices. Intended to allow users to log themselves
	 * out of all devices as a security measure.
	 *
	 * @param $email
	 */
	public function purgeRememberTokens($email)
	{
		// Emails should NOT be case sensitive.
		$email = strtolower($email);

		$this->ci->login_model->purgeRememberTokens($email);

		// todo record activity of remember me purges.
		Events::trigger('didPurgeRememberTokens', [$email]);
	}

	//--------------------------------------------------------------------

	//--------------------------------------------------------------------
	// Protected Methods
	//--------------------------------------------------------------------

	/**
	 * Check if Allow Persistent Login Cookies is enable
	 *
	 * @param $user
	 */
	protected function rememberUser($user)
	{
		if (! $this->config->allowRemembering)
		{
			log_message('debug', 'Auth library set to refuse "Remember Me" functionality.');

			return false;
		}

		$this->refreshRememberCookie($user);
	}

	//--------------------------------------------------------------------

	/**
	 * Invalidates the current rememberme cookie/database entry, creates
	 * a new one, stores it and returns the new value.
	 *
	 * @param      $user
	 * @param null $token
	 *
	 * @return mixed
	 */
	protected function refreshRememberCookie($user, $token = null)
	{
		helper('cookie');

		// If a token is passed in, we know we're removing the
		// old one.
		if (! empty($token))
		{
			$this->invalidateRememberCookie($user->email, $token);
		}

		$newToken = $this->loginModel->generateRememberToken($user);

		// Save the token to the database.
		$data = [
			'email'   => $user->email,
			'hash'    => sha1($this->config->salt.$newToken),
			'created' => date('Y-m-d H:i:s'),
		];

		$this->loginModel->table('auth_tokens')
						 ->protect(false)
		                 ->insert($data);

		$appConfig = new App();

		// Create the cookie
		set_cookie(
			'remember',               // Cookie Name
			$newToken,                      // Value
			$this->config->rememberLength,  // # Seconds until it expires
			$appConfig->cookieDomain,
			$appConfig->cookiePath,
			$appConfig->cookiePrefix,
			false,                  // Only send over HTTPS?
			true                  // Hide from Javascript?
		);

		return $newToken;
	}

	//--------------------------------------------------------------------

	/**
	 * Deletes any current remember me cookies and database entries.
	 *
	 * @param $email
	 * @param $token
	 *
	 * @return string The new token (not the hash).
	 */
	protected function invalidateRememberCookie($email, $token)
	{
		// Emails should NOT be case sensitive.
		$email = strtolower($email);

		// Remove from the database
		$this->loginModel->deleteRememberToken($email, $token);

		$appConfig = new App();

		// Remove the cookie
		delete_cookie(
			'remember',
			$appConfig->cookieDomain,
			$appConfig->cookiePath,
			$appConfig->cookiePrefix
		);
	}

	//--------------------------------------------------------------------

	/**
	 * Handles the nitty gritty of actually logging our user into the system.
	 * Does NOT perform the authentication, just sets the system up so that
	 * it knows we're here.
	 *
	 * @param $user
	 */
	protected function loginUser($user)
	{
		// Save the user for later access
		$this->user = $user;

		// Get ip address
		$ip_address = Services::request()
		                      ->getIPAddress();

		// Regenerate the session ID to help protect
		// against session fixation
		session()->regenerate();

		// Let the session know that we're logged in.
		session()->set('logged_in', $user->id);

		// Record a new Login
		$this->loginModel->recordLogin($user);

		// If logged in, ensure cache control
		// headers are in place
		$this->setHeaders();

		// We'll give a 20% chance to need to do a purge since we
		// don't need to purge THAT often, it's just a maintenance issue.
		// to keep the table from getting out of control.
		if (mt_rand(1, 100) < 20)
		{
			$this->loginModel->purgeOldRememberTokens();
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the headers to ensure that pages are not cached when a user
	 * is logged in, helping to protect against logging out and then
	 * simply hitting the Back button on the browser and getting private
	 * information because the page was loaded from cache.
	 */
	protected function setHeaders()
	{
		Services::request()
		        ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
		Services::request()
		        ->setHeader('Pragma', 'no-cache');
	}

	//--------------------------------------------------------------------


}
