<?php namespace App\Domains\Users;

use Myth\ORM\EntityManager;

class ProfileManager extends EntityManager
{
	protected $table      = 'user_profile';
	protected $primaryKey = 'id';

	protected $returnType = 'App\Domains\Users\Profile';

	protected $useSoftDeletes = true;
	protected $useTimestamps = true;

	protected $allowedFields = [
		'user_id', 'phone', 'personal_url', 'business_url', 'facebook_url', 'twitter_url', 'gplus_url',
		'about', 'social_public', 'show_email', 'show_phone', 'views'
	];

	protected $dateFormat    = 'datetime';

	protected $validationRules    = [
	];
	protected $validationMessages = [];
	protected $skipValidation     = false;
}
