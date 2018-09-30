<?php namespace Config;

use CodeIgniter\Config\BaseConfig;

class Forums extends BaseConfig
{
	/**
	 * The class names of the main class for Each PostType
	 *
	 * @var array
	 */
	public $postTypes = [
		'discussion' => \App\Domains\Posts\Types\DiscussionPost::class,
		'question' => \App\Domains\Posts\Types\QuestionPost::class,
		'media' => \App\Domains\Posts\Types\MediaPost::class,
		'poll' => \App\Domains\Posts\Types\PollPost::class,
	];

	/**
	 * Factory method to get a new instance of the right type.
	 *
	 * @param string $type
	 *
	 * @return mixed
	 */
	public function postFactory(string $type)
	{
		if (! isset($this->postTypes[$type]))
		{
			throw new \RuntimeException('Invalid post type: '. $type);
		}

		return new $this->postTypes[$type]();
	}
}
