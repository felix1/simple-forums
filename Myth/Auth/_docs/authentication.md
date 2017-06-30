# User Authentication

The Authentication library that is included strives to work with known best practices for protecting your users. To this end, an `Auth` module has been provided that takes advantage of this for processes like user registration, login, logout, password resets, and more. This guide does NOT talk about the included module, since it should be easy to digest by looking through it. Instead, this guide goes over how configure the system and how to use the underlying technology so that you can intelligently modify the authentication system to meet your site's specific needs.

When modifying any provided module located in the `myth/CIModules` folder, you should copy that module's folder and place it the `application/modules` folder before making any changes. This allows you to upgrade to newer versions of Sprint without overwriting your own code. Your modules will be ran in place of the myth modules.

> DISCLAIMER: I am, by no means, a security expert. Any knowledge that I have has been gathered from reading articles from people smarter and more experienced than I am. If you know that I've explained something incorrectly and can tell me the correct solution, or can point to research that invalidates anything said here, please feel free to drop me a line and correct me. I'll make sure to read through it and try to correct either my docs or the code itself.

## LocalAuthentication Class
The included class, `Myth\Auth\LocalAuthentication`, together with the `Login_model` is the power behind the features listed below. If you want to create your own, create a new class that implements the `Myth\Auth\AuthenticateInterface`. Then modify `application/config/application.php` to use the new class. This will be automatically loaded up and readied for any class that uses the [Auth Trait](security/auth_trait).

## Logging Users In
Use the `login()` method to attempt to log users in. The first parameter is an array of credentials to verify the user against. The library does not enforce a specific set of credentials to confirm against. You are free to use any combination of fields that exist within the `users` table, but typical uses would be either `email` or `username`. You must include a field name `password`, though as it will be verified against the hashed version in the database.

	$auth = new \Myth\Auth\LocalAuthentication();
	
	$credentials = [
		'email' => $this->input->post('email', true),
		'password' => $this->input->post('password', true)
	];
	
	$auth->login($credentials);

The method returns either `TRUE` or `FALSE` depending on the success/failure of the login. Upon an error, you can retrieve the error string by calling the `error()` method.

	if (! $auth->login($credentials) )
	{
		$this->setMessage($auth->error(), 'danger');
	}

The second parameter is a boolean value that tells whether we should remember the user. See the section on [Remembering Users](#remembering_users) for more details. The system does allow for a user to be remembered on more than one device and more than one browser at a time. Which allows them to maintain separate persistent logins at home and work and even on their mobile device simultaneously. 

	$auth->login($credentials, true);
	
Once a user has been successfully logged in a `logged_in` value will be set in the session's userdata.

## Validating Credentials
If you need to validate a user's credentials without actually logging them in, you can do so with the `validate()`. This is the same method  used by the `login()` method so the results will be identical. The first parameter is an array of credentials as described above.

	$credentials = [
		'email' => $this->input->post('email', true),
		'password' => $this->input->post('password', true)
	];
	
	if ($auth->validate($credentials) ) {...}
	
By default, the method will return either TRUE or FALSE. However, if you want to have the user object returned to you on successfuly validation, you can pass in TRUE as the second parameter.

	if (! $user = $auth->validate($credentials) )
	{
		...
	}

## Determining If Logged In
You can use the `isLoggedIn()` to check if a user is logged in. As long as a user's session is valid -- it hasn't timed out, and they haven't logged out -- this is the only check needed to do to ensure that a user is validly logged in.

	if (! $auth->isLoggedIn() )
	{
		$this->session->set_userdata('redirect_url', current_url() );
		redirect('login');
	}

## Current User
Once logged in, the current user is stored in memory as a class variable. This can be accessed at any time with the `user()` method. There are no parameters. The method will return an array of all of the user's information.

	$current_user = $auth->user();

## Logging Out
You can log a user out by calling the `logout()` method. This will destroy their current session and invalidate the current RememberMe Token. Note this only affects a logout on the current machine. If a user logs out at work, they can remain logged in at home.

	$auth->logout();

### Current User Id
Often, you will only need the ID of the current user. You can get this with the `id()` method. It will return either an INT with the user's id, or NULL.

	$user_id = $auth->id();

## Remembering Users
You can have a user be remembered, through the user of cookies, by passing TRUE in as the second parameter to the `login()` method, as described above. But what happens then? I have tried to make the process as secure as possible, and will describe the process here so that you can understand the flow.

If you do NOT want your users to be able to use persistent logins, you can turn this off in the `application/config/auth.php` config file, along with a number of other settings. See the section on [Configuration](#configuration), below.

### Security Flow

- When a user is set to be remembered, a Token is created that consists of a modified version of the user's email and a random 128-character, alpha-numeric string.
- The Token is saved to a cookie on the user's machine. This will later be used to identify the user when logging in automatically.
- The Token is then salted, hashed and stored in the database. The original token is then discarded and the system doesn't know anything about it anymore.
- When logging in automatically, the Token is retrieved from the cookie, salted and hashed and we attempt to find a match in the database.
- After automatic logins, the old tokens are discarded, both from the cookie and the database, and a new Token is generated and the process continues as described here.

### Automatic Logins
You can attempt to log a user in automatically, if they've been remembered, with the `viaRemember()` method. There are no parameters. The method will return either TRUE or FALSE on success/fail.

	if (! $auth->isLoggedIn() )
	{
		if (! $auth->viaRemember() )
		{
			$this->session->set_userdata('redirect_url', current_url() );
			redirect('login');
		}
	}
	
## Removing All User's Persistent Logins
To allow a user to remove all login attempts associated with their email address, across all devices they might be logged in as, you can use the `purgeRememberTokens()` method. The only parameter is the email address of the user.

	$auth->purgeRememberTokens($email);

## Throttling Login Attempts
The Authentication library is designed to help protect against a number of types of threats, include Brute Force attacks and Distributed Brute Force attacks, but attempts to balance these needs with usability. There are a number of [configuration options](#configuration) available to help you customize this to your site's needs as it grows.

### Determining Throttle Delay
The method `isThrottled()` will determine whether the user is under any form of throttling and will return the number of seconds until they can try again. The only parameter is the email address of the user that you are checking.

	if ($delay = $auth->isThrottled($email) )
	{
		$this->setMessage( $auth->error(), 'warning');
		return false;
	}

This is automatically checked during the `login()` method and you will probably never need to call this method directly.


## Attaching the User Model
Before the system can work, you must tell it which model to use when working with Users.

	$this->load->model('user_model');
	$auth->useModel( $this->user_model );

## Removing All Login Attempts for A User
If you need to remove all failed login attempts for a user you can use the `purgeLoginAttempts()` method. The only parameter is the email of the user.

	$auth->purgeLoginAttempts($email);

## Configuration
Many aspects of the system can be configured in the `application/config/auth.php` config file. These options are described here. 

### auth.authorize_lib
Specifies the Authorization library that will be used by the Auth Trait. This should include the fully namespaced class name. 

	$config['auth.authorize_lib'] = '\Myth\Auth\FlatAuthorization';

### auth.authentication_lib
Specifies the Authorization library that will be used by the Auth Trait. This should include the fully namespaced class name. 

	$config['auth.authenticate_lib'] = '\Myth\Auth\LocalAuthentication';

### auth.valid_fields
The names of the fields in the user table that are allowed by used when testing credentials in the `validate()` method. 

	$config['auth.valid_fields'] = ['email', 'username'];

### auth.allow_remembering
This can be either TRUE or FALSE, and determines whether or not the system allows persistent logins (Remember Me). For most sites, you will likely want to leave this turned on for your user's convenience. If your site holds extremely confidential information and you cannot have your site hacked for any reason, you should set this to FALSE and not allow persistent connections ever.

	$config['auth.allow_remembering'] = true;

### auth.salt
This is used to salt the hashes when storing persistent login tokens. A default is provided but you should change it to a unique value for every site.

	$config['auth.salt'] = 'ASimpleSalt';

### auth.remember_length
This is the number of SECONDS that a persistent login lasts. A quick reference of common values is provided in the config file for your convenience. The default value is 2 weeks.

	$config['auth.remember_length'] = 1209600;

### auth.min_password_strength
The minimum value of  entropy that a password must meet to be considered a "strong-enough" password. This is primarily based on [NIST Special Publication 800-63](http://en.wikipedia.org/wiki/Password_strength#NIST_Special_Publication_800-63) with additional features by [Thomas Hruska](http://cubicspot.blogspot.com/) as part of his [Barebones CMS SSO Server/Client package](http://barebonescms.com/documentation/sso/).

	$config['auth.min_password_strength'] = 18;

While the formula is a bit complex, the following are good guidelines to use based on the type of site that you are running:

- 18 bits of entropy = minimum for ANY website.
- 25 bits of entropy = minimum for a general purpose web service used relatively widely (e.g. Hotmail).
- 30 bits of entropy = minimum for a web service with business critical applications (e.g. SAAS).
- 40 bits of entropy = minimum for a bank or other financial service.

### auth.use_dictionary
Determines whether the passwords should be compared against an 300,000+ word English-language dictionary to eliminate common words and their variations that would be pretty simple for a hacker to guess.

	$config['auth.use_dictionary'] = false;

### auth.password_cost
The BCRYPT method of encryption allows you to define the "cost", or number of iterations made, whenever a password hash is created. This defaults to a value of 10 which is an acceptable number. However, depending on the security needs of your application and the power of your server, you might want to increase the cost. This makes the hasing process takes longer.

Valid range is between 4 - 31.

	$config['auth.hash_cost'] = 10;

A **command line tool** has been provided that determines the appropriate value to set the hash_cost to based upon a desired processing time. The command defaults to a value of 50 milliseconds, which is an acceptable time for interactive logins, though this value could be raised to 100 milliseconds or higher, depending on the type of site, how busy it is, and the patience of your users.

	$ php sprint auth test index

If you want to test against a different value, enter the number of milliseconds after the command.

	$ php sprint auth test index 100

### auth.default_role_id
The ID of the role that a member should be assigned when they first sign up.

	$config['auth.default_role_id'] = 1;
