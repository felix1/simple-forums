<?php namespace Myth\Auth\Config;

use CodeIgniter\Config\BaseConfig;

class Auth extends BaseConfig
{
	//--------------------------------------------------------------------
	// AUTHORIZATION ENGINE
	//--------------------------------------------------------------------
	// Specifies which library should be used to provide the Authorization
	// capabilities of the Auth Trait. This must include the fully
	// namespaced path of the class and it must be able to be found
	// by Composer.
	//
	public $authorizeLib = '\Myth\Auth\Authorize\FlatAuthorization';

	//--------------------------------------------------------------------
	// AUTHORIZATION DEFAULT ROLE
	//--------------------------------------------------------------------
	// Specifies the name of the default role that should be assigned
	// to new users upon their creation, if no other roles are assigned.
	//
	public $defaultGroup = 'users';

	//--------------------------------------------------------------------
	// AUTHENTICATION ENGINE
	//--------------------------------------------------------------------
	// Specifies which library should be used to provide the Authentication
	// capabilities of the Auth Trait. This must include the fully
	// namespace path of the class and it must be able to be found by
	// Composer.
	//
	public $authenticateLib = '\Myth\Auth\Authenticate\LocalAuthentication';

	//--------------------------------------------------------------------
	// AUTHENTICATION FIELDS
	//--------------------------------------------------------------------
	// The names of the fields in the user table that is allowed to
	// test credentials against. This is, by default, only 'email' and
	// 'username'
	//
	public $validFields = ['email', 'username'];


	//--------------------------------------------------------------------
	// PERSISTENT LOGINS
	//--------------------------------------------------------------------


	//--------------------------------------------------------------------
	// Allow Persistent Login Cookies (Remember me)
	//--------------------------------------------------------------------
	// While every attempt has been made to create a very strong protection
	// with the remember me system, there are some cases (like when you
	// need extreme protection, like dealing with users financials) that
	// you might not want the extra risk associated with this cookie-based
	// solution.
	//
	public $allowRemembering = true;

	//--------------------------------------------------------------------
	// Remember Me Salt
	//--------------------------------------------------------------------
	// This string is used to salt the hashes when storing RememberMe
	// tokens in the database.
	// If you are using Remember Me functionality, you should consider
	// changing this value to be unique to your site.
	//
	public $salt = 'ASimpleSalt';

	//--------------------------------------------------------------------
	// Remember Length
	//--------------------------------------------------------------------
	// The amount of time, in seconds, that you want a login to last for.
	// Defaults to 2 weeks.
	//
	// Common values are:
	//      1 hour   - 3600
	//      1 day    - 86400
	//      1 week   - 604800
	//      2 weeks  - 1209600
	//      3 weeks  - 1814400
	//      1 month  - 2419200
	//      2 months - 4838400
	//      3 months - 7257600
	//      6 months - 14515200
	//      1 year   - 29030400
	//
	public $rememberLength = 1209600;



	//--------------------------------------------------------------------
	// PASSWORDS
	//--------------------------------------------------------------------

	//--------------------------------------------------------------------
	// Minimum Entropy (password strength)
	//--------------------------------------------------------------------
	// The minimum password strength that a password must meet to be
	// considered a strong-enough value. While the formula is a bit complex
	// you can use the following guidelines:
	//      - 18 bits of entropy = minimum for ANY website.
	//      - 25 bits of entropy = minimum for a general purpose web service used relatively widely (e.g. Hotmail).
	//      - 30 bits of entropy = minimum for a web service with business critical applications (e.g. SAAS).
	//      - 40 bits of entropy = minimum for a bank or other financial service.
	//
	public $minPasswordStrength = 18;

	//--------------------------------------------------------------------
	// Use Dictionary
	//--------------------------------------------------------------------
	// Should the passwords be compared against an English-language
	// dictionary to eliminate common words and their variations that would
	// be pretty simply for a hacking attempt to guess?
	//
	public $useDictionary = false;

	//--------------------------------------------------------------------
	// PASSWORD HASHING COST
	//--------------------------------------------------------------------
	// The BCRYPT method of encryption allows you to define the "cost"
	// or number of iterations made, whenver a password hash is created.
	// This defaults to a value of 10 which is an acceptable number.
	// However, depending on the security needs of your application
	// and the power of your hardware, you might want to increase the
	// cost. This makes the hasing process takes longer.
	//
	// Valid range is between 4 - 31.
	public $hashCost = 10;

	//--------------------------------------------------------------------
	// Activation Method
	//--------------------------------------------------------------------
	// The site supports 3 methods of activating a user:
	//      - 'auto'    No extra protection, they are allowed in site after signup.
	//      - 'email'   The are sent an email with an activation link/code
	//      - 'manual'  Requires manual approval by a site administrator.
	//
	public $activationMethod = 'auto';


	//--------------------------------------------------------------------
	// Roles
	//--------------------------------------------------------------------

	//--------------------------------------------------------------------
	// Default Role ID
	//--------------------------------------------------------------------
	// Sets the Default role id to use when creating new users.
	//
	public $defaultRoleID = 3;

}
