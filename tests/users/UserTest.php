<?php

use Mockery as m;
use App\Domains\Users\User;
use Config\Services;
use Myth\Auth\Authorize\FlatAuthorization;
use Myth\Auth\Authenticate\LocalAuthentication;

class UserTest extends CIDatabaseTestCase
{
	protected $authorization;

	protected $authentication;

	protected $basePath = APPPATH .'Database';
	protected $namespace = 'App';

	public function setUp()
	{
		parent::setUp();
	}


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

	public function testBanUser()
	{
		$user = new User();

		$user->banUser('third strike');

		$this->assertEquals('banned', $this->getPrivateProperty($user, 'status'));
		$this->assertEquals('third strike', $this->getPrivateProperty($user, 'status_message'));
	}

	public function testIsBanned()
	{
		$user = new User();

		$this->assertFalse($user->isBanned());

		$user->banUser('third strike');
		$this->assertTrue($user->isBanned());

		$user->removeBan();
		$this->assertFalse($user->isBanned());
	}

	public function testIsAdminTrue()
	{
		$this->hasInDatabase('auth_groups_users', [
			'group_id' => 1,
			'user_id' => 1
		]);

		$user = new User([
			'id' => 1
		]);

		$this->assertTrue($user->isAdmin());
	}

	public function testIsAdminFalse()
	{
		$this->hasInDatabase('auth_groups_users', [
			'group_id' => 3,
			'user_id' => 1
		]);

		$user = new User([
			'id' => 1
		]);

		$this->assertFalse($user->isAdmin());
	}

	public function testIsModeratorTrue()
	{
		$this->hasInDatabase('auth_groups_users', [
			'group_id' => 2,
			'user_id' => 1
		]);

		$user = new User([
			'id' => 1
		]);

		$this->assertTrue($user->isModerator());
	}

	public function testIsModeratorFalse()
	{
		$this->hasInDatabase('auth_groups_users', [
			'group_id' => 3,
			'user_id' => 1
		]);

		$user = new User([
			'id' => 1
		]);

		$this->assertFalse($user->isModerator());
	}

	public function testInGroupNumeric()
	{
		$this->hasInDatabase('auth_groups_users', [
			'group_id' => 2,
			'user_id' => 1
		]);

		$user = new User([
			'id' => 1
		]);

		$this->assertTrue($user->inGroup(2));
	}

	public function testInGroupNamed()
	{
		$this->hasInDatabase('auth_groups_users', [
			'group_id' => 2,
			'user_id' => 1
		]);

		$user = new User([
			'id' => 1
		]);

		$this->assertTrue($user->inGroup('moderators'));
	}

	public function testInGroupNamedMixedCase()
	{
		$this->hasInDatabase('auth_groups_users', [
			'group_id' => 2,
			'user_id' => 1
		]);

		$user = new User([
			'id' => 1
		]);

		$this->assertTrue($user->inGroup('Moderators'));
	}

	public function testAdduserToGroupNumeric()
	{
		$this->dontSeeInDatabase('auth_groups_users', [
			'group_id' => 2,
			'user_id' => 1
		]);

		$user = new User([
			'id' => 1
		]);

		$user->addToGroup(1);

		$this->seeInDatabase('auth_groups_users', [
			'group_id' => 1,
			'user_id' => 1
		]);
	}

	public function testAdduserToGroupNamed()
	{
		$this->dontSeeInDatabase('auth_groups_users', [
			'group_id' => 2,
			'user_id' => 1
		]);

		$user = new User([
			'id' => 1
		]);

		$user->addToGroup('moderators');

		$this->seeInDatabase('auth_groups_users', [
			'group_id' => 2,
			'user_id' => 1
		]);
	}

	public function testAdduserToGroupNamedMixedCase()
	{
		$this->dontSeeInDatabase('auth_groups_users', [
			'group_id' => 2,
			'user_id' => 1
		]);

		$user = new User([
			'id' => 1
		]);

		$user->addToGroup('Moderators');

		$this->seeInDatabase('auth_groups_users', [
			'group_id' => 2,
			'user_id' => 1
		]);
	}

	public function testRemoveFromGroupNumeric()
	{
		$this->hasInDatabase('auth_groups_users', [
			'group_id' => 2,
			'user_id' => 1
		]);

		$user = new User([
			'id' => 1
		]);

		$user->removeFromGroup(2);

		$this->dontSeeInDatabase('auth_groups_users', [
			'group_id' => 2,
			'user_id' => 1
		]);
	}

	public function testRemoveFromGroupNamed()
	{
		$this->hasInDatabase('auth_groups_users', [
			'group_id' => 2,
			'user_id' => 1
		]);

		$user = new User([
			'id' => 1
		]);

		$user->removeFromGroup('moderators');

		$this->dontSeeInDatabase('auth_groups_users', [
			'group_id' => 2,
			'user_id' => 1
		]);
	}

	public function testRemoveFromGroupNamedMixedCase()
	{
		$this->hasInDatabase('auth_groups_users', [
			'group_id' => 2,
			'user_id' => 1
		]);

		$user = new User([
			'id' => 1
		]);

		$user->removeFromGroup('Moderators');

		$this->dontSeeInDatabase('auth_groups_users', [
			'group_id' => 2,
			'user_id' => 1
		]);
	}

	public function testAddPermissionNumeric()
	{
		$user = new User([
			'id' => 1
		]);

		$user->addPermission(12);

		$this->seeInDatabase('auth_users_permissions', [
			'user_id' => 1,
			'permission_id' => 12
		]);
	}

	public function testAddPermissionNumericExists()
	{
		$this->hasInDatabase('auth_users_permissions', [
			'user_id' => 1,
			'permission_id' => 12
		]);

		$user = new User([
			'id' => 1
		]);

		$user->addPermission(12);

		$this->seeInDatabase('auth_users_permissions', [
			'user_id' => 1,
			'permission_id' => 12
		]);
	}

	public function testAddPermissionNamed()
	{
		$this->hasInDatabase('auth_permissions', [
			'name' => 'manageUsers'
		]);

		$user = new User([
			'id' => 1
		]);

		$user->addPermission('manageUsers');

		$this->seeInDatabase('auth_users_permissions', [
			'user_id' => 1,
			'permission_id' => 1
		]);
	}

	public function testRemovePermissionNumeric()
	{
		$this->hasInDatabase('auth_users_permissions', [
			'user_id' => 1,
			'permission_id' => 12
		]);

		$user = new User([
			'id' => 1
		]);

		$user->removePermission(12);

		$this->dontSeeInDatabase('auth_users_permissions', [
			'user_id' => 1,
			'permission_id' => 12
		]);
	}

	public function testRemovePermissionNamed()
	{
		$this->hasInDatabase('auth_users_permissions', [
			'user_id' => 1,
			'permission_id' => 11
		]);
		$this->hasInDatabase('auth_permissions', [
			'id' => 1,
			'name' => 'manageUsers',
			'description' => 'Manage users'
		]);

		$user = new User([
			'id' => 1
		]);

		$user->removePermission('manageUsers');

		$this->dontSeeInDatabase('auth_users_permissions', [
			'user_id' => 1,
			'permission_id' => 1
		]);
	}

	public function testHasUserPermissionNamedTrue()
	{
		$this->hasInDatabase('auth_permissions', [
			'id' => 1,
			'name' => 'manageUsers',
			'description' => 'Manage users'
		]);
		$this->hasInDatabase('auth_users_permissions', [
			'user_id' => 1,
			'permission_id' => 1
		]);

		$user = new User([
			'id' => 1
		]);

		$this->assertTrue($user->hasPermission('manageUsers'));
	}

	public function testHasUserPermissionNamedFalse()
	{
		$this->hasInDatabase('auth_permissions', [
			'id' => 1,
			'name' => 'manageUsers',
			'description' => 'Manage users'
		]);
		$this->hasInDatabase('auth_users_permissions', [
			'user_id' => 1,
			'permission_id' => 2
		]);

		$user = new User([
			'id' => 1
		]);

		$this->assertFalse($user->hasPermission('manageUsers'));
	}

	public function testHasGroupPermissionNamedTrue()
	{
		$this->hasInDatabase('auth_permissions', [
			'id' => 1,
			'name' => 'manageUsers',
			'description' => 'Manage users'
		]);
		$this->hasInDatabase('auth_groups_users', [
			'group_id' => 3,
			'user_id' => 1
		]);
		$this->hasInDatabase('auth_groups_permissions', [
			'group_id' => 3,
			'permission_id' => 1
		]);

		$user = new User([
			'id' => 1
		]);

		$this->assertTrue($user->hasPermission('manageUsers'));
	}
}
