<?php

return [
	'noAuthenticate'     => 'No Authentication System chosen.',
	'noAuthorize'        => 'No Authorization System chosen.',
	'notEnoughPrivilege' => 'You do not have sufficient privileges to view that page.',
	'notLoggedIn'        => 'You must be logged in to view that page.',

	'didLogin'             => 'Welcome back!',
	'didLogout'            => 'You have been logged out. Come back soon!',
	'didRegister'          => 'Account created. Please login.',
	'unknownRegisterError' => 'Unable to create user currently. Please try again later.',

	'invalidUser'        => 'Invalid credentials. Please try again.',
	'invalidCredentials' => 'Credentials used are not allowed.',
	'tooManyCredentials' => 'Too many credentials were passed in. Must be limited to one besides password.',
	'invalidEmail'       => 'Unable to find a user with that email address.',
	'invalidPassword'    => 'Unable to find a valid login with that password.',
	'bruteBanNotice'     => "Your account has had excessive login attempts. To protect the account you must wait 15 minutes before another attempt can be made.",

	'rememberLabel' => 'Remember me on this device',
	'email'         => 'Email Address',
	'password'      => 'Password',
	'passConfirm'   => 'Password (Again)',
	'signin'        => 'Sign In',
	'register'      => 'Join Us!',

	'passwordStrength' => 'Password Strength',
	'passNotStrong'    => 'The Password must be stronger.',

	'haveAccount' => 'Already a member? <a href="'.route_to('login').'">Sign In</a>',
	'needAccount' => 'Need an Account? <a href="'.route_to('register').'">Sign Up</a>',
	'forgotPass'  => '<a href="'.route_to('forgot_pass').'">Forgot your Password?</a>',

	'firstName' => 'First Name',
	'lastName'  => 'Last Name',
	'username'  => 'Username',

	'forgot'      => 'Forgot Password',
	'forgotNote'  => 'No problem. Enter your email and we will send instructions.',
	'send'        => 'Send',
	'sendSuccess' => 'The email is on its way!',

	'reset'              => 'Reset Password',
	'resetNote'          => 'Please follow the instructions in the email to reset your password.',
	'passCode'           => 'Reset Code',
	'newPassword'        => 'Choose a New Password',
	'newPasswordSuccess' => 'Your password has been changed. Please sign in.',

	'forceChangeNote' => 'You must select a new password.',
	'currentPassword' => 'Current Password',
	'changePassword'  => 'Change Password',
	'badCurrentPass'  => 'The current password does not match.',
	'passMustMatch'   => 'The new passwords must match.',

	'activateAccount' => 'Activate Account',
	'activate'        => 'Activate',
	'inactiveAccount' => 'Your account is not active.',

	'registerSubject' => "Open to activate your account.",
	'activateNoUser'  => 'Unable to find a user with those credentials.',
	'remindSubject'   => "Here's how to reset your password...",
	'needResetCode'   => 'You must provide the Reset Code.',
	'resetNoUser'     => 'Unable to find an account with that email and reset code. Please try again.',
	'resetSubject'    => "Your password has been reset.",

	'permissionNotFound' => 'Unable to locate that Permission.',
	'groupNotFound'      => 'Unable to locate that Group.',
];
