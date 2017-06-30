<?php

return [
	'no_authenticate'      => 'No Authentication System chosen.',
	'no_authorize'         => 'No Authorization System chosen.',
	'not_enough_privilege' => 'You do not have sufficient privileges to view that page.',
	'not_logged_in'        => 'You must be logged in to view that page.',

	'did_login'             => 'Welcome back!',
	'did_logout'            => 'You have been logged out. Come back soon!',
	'did_register'          => 'Account created. Please login.',
	'unkown_register_error' => 'Unable to create user currently. Please try again later.',

	'invalid_user'         => 'Invalid credentials. Please try again.',
	'invalid_credentials'  => 'Credentials used are not allowed.',
	'too_many_credentials' => 'Too many credentials were passed in. Must be limited to one besides password.',
	'invalid_email'        => 'Unable to find a user with that email address.',
	'invalid_password'     => 'Unable to find a valid login with that password.',
	'bruteBan_notice'      => "Your account has had excessive login attempts. To protect the account you must wait 15 minutes before another attempt can be made.",

	'remember_label' => 'Remember me on this device',
	'email'          => 'Email Address',
	'password'       => 'Password',
	'pass_confirm'   => 'Password (Again)',
	'signin'         => 'Sign In',
	'register'       => 'Join Us!',

	'password_strength' => 'Password Strength',
	'pass_not_strong'   => 'The Password must be stronger.',

	'have_account' => 'Already a member? <a href="'.route_to('login').'">Sign In</a>',
	'need_account' => 'Need an Account? <a href="'.route_to('register').'">Sign Up</a>',
	'forgot_pass'  => '<a href="'.route_to('forgot_pass').'">Forgot your Password?</a>',

	'first_name' => 'First Name',
	'last_name'  => 'Last Name',
	'username'   => 'Username',

	'forgot'       => 'Forgot Password',
	'forgot_note'  => 'No problem. Enter your email and we will send instructions.',
	'send'         => 'Send',
	'send_success' => 'The email is on its way!',

	'reset'                => 'Reset Password',
	'reset_note'           => 'Please follow the instructions in the email to reset your password.',
	'pass_code'            => 'Reset Code',
	'new_password'         => 'Choose a New Password',
	'new_password_success' => 'Your password has been changed. Please sign in.',

	'force_change_note' => 'You must select a new password.',
	'current_password'  => 'Current Password',
	'change_password'   => 'Change Password',
	'bad_current_pass'  => 'The current password does not match.',
	'pass_must_match'   => 'The new passwords must match.',

	'activate_account' => 'Activate Account',
	'activate'         => 'Activate',
	'inactive_account' => 'Your account is not active.',

	'register_subject' => "Open to activate your account.",
	'activate_no_user' => 'Unable to find a user with those credentials.',
	'remind_subject'   => "Here's how to reset your password...",
	'need_reset_code'  => 'You must provide the Reset Code.',
	'reset_no_user'    => 'Unable to find an account with that email and reset code. Please try again.',
	'reset_subject'    => "Your password has been reset.",

	'permission_not_found' => 'Unable to locate that Permission.',
	'group_not_found'      => 'Unable to locate that Group.',
];
