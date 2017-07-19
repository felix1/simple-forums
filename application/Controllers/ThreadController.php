<?php namespace App\Controllers;

use App\Domains\Forums\ThreadModel;
use App\Domains\Posts\PostModel;
use CodeIgniter\PageNotFoundException;

class ThreadController extends BaseController
{
	/**
	 * @var \App\Domains\Forums\ThreadModel
	 */
	protected $threadModel;

	public function __construct(...$params)
	{
		parent::__construct(...$params);

		$this->threadModel = new ThreadModel();
	}

	/**
	 * Displays a thread and it's posts.
	 *
	 * @param string $threadID
	 */
	public function show(string $threadID)
	{
		$threadID = (int)$threadID;

		if (! $threadID > 0)
		{
			throw new PageNotFoundException();
		}

		$postModel   = new PostModel();

		$thread = $this->threadModel->find($threadID);
		$thread->setPostModel($postModel);
		$thread->populatePosts();

		$this->render('threads/show', [
			'thread' => $thread,
			'pager' => $postModel->pager
		]);
	}

}
