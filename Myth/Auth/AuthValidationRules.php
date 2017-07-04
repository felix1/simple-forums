<?php namespace Myth\Auth;

class AuthValidationRules
{
	/**
	 * Ensures the password passed in meets the requirement set out in the config rules.
	 *
	 * @param string|null $str
	 * @param string      $field
	 * @param array       $data
	 *
	 * @return bool
	 */
	public function strong_password(string $str=null, string $field=null, array $data=null): bool
	{
		helper('Myth\Auth\Helpers\password');

		return isStrongPassword($str);
	}

	//--------------------------------------------------------------------
}
