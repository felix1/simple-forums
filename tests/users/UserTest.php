<?php

use App\Domains\Users\User;

class UserTest extends CIUnitTestCase
{
	/**
	 * @expectedException \App\Exceptions\ValidationException
	 */
	public function testSetEmailValidatesAddress()
	{
		$user = new User();

		$user->setEmail('franky');
	}

	public function testSetEmailNormalizesAddress()
	{
		$user = new User();

		$user->setEmail('FrAnKie@EXamplE.com');

		$this->assertEquals('frankie@example.com', $this->getPrivateProperty($user, 'email'));
	}

	public function testSetPasswordHashActuallyHashes()
	{
		$user = new User();

		$user->setPassword('password123');

		$hash = $this->getPrivateProperty($user, 'password_hash');
		$this->assertTrue(password_verify('password123', $hash));
	}

	public function testAvatarDefaultsToGravatar()
	{
		$user = new User([
			'email' => 'Franky@example.com'
		]);

		$hash = md5('franky@example.com');

		$expected = "https://www.gravatar.com/avatar/{$hash}?s=60";
		$this->assertEquals($expected, $user->avatar(60));
	}

	public function testAvatarUsesSetAvatar()
	{
		$user = new User([
			'avatar' => '/img/avatars/franky.jpg'
		]);

		$this->assertEquals('/img/avatars/franky.jpg', $user->avatar());
	}


}
