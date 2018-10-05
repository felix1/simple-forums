<?php namespace App\Controllers;

use App\Domains\Forums\Thread;
use Config\Forums;
use App\Domains\Posts\PostManager;
use App\Domains\Forums\ThreadManager;

class PostController extends BaseController
{


	/**
	 * Show the new post form
	 *
	 * @param int $forumID
	 */
	public function newPost(int $forumID)
	{
		$config = new Forums();
		$type = $this->request->getGetPost('type');

		$typeObject = ! empty($type)
			? $typeObject = $config->postFactory($type)
			: null;

		$this->render('posts/form', [
			'type' => $type,
			'types' => $config->postTypes,
			'typeObject' => $typeObject,
			'newThread' => true,
		]);
	}

	/**
	 * Saves a new or existing Post.
	 *
	 * @param int      $forumId
	 * @param int|null $postId
	 *
	 * @return \CodeIgniter\HTTP\RedirectResponse
	 */
	public function savePost(int $forumId, int $postId = null)
	{
		$posts = new PostManager();

		$type = $this->request->getVar('postType');

		if (is_null($postId))
		{
			$post = new $type($this->request->getPost());
			$post->forum_id = $forumId;
			$post->user_id = $this->authenticate->id();
			$post->thread_id = 0;
		}
		else
		{
			$post = $posts->find($postId);
		}

		$post->type = $type;
		$post->markup = 'markdown';
		$post->html = $post->body;

		if (!$post = $posts->save($post))
		{
			return redirect()->back()->withInput()->with('errors', $posts->errors());
		}

		$post = $posts->find($post);

		// Is this a new thread?
		if ($this->request->getVar('newThread'))
		{
			$threads = new ThreadManager();

			$thread = new Thread([
				'user_id' => $this->authenticate->id(),
				'title' => $post->title,
				'first_post' => $post->id,
				'forum_id' => $forumId,
				'view_count' => 0,
				'post_count' => 0,
			]);

			if (! $threads->save($thread))
			{
				return redirect()->back()->with('errors', $threads->errors());
			}

			$post->thread_id = $thread->id;
			$posts->save($post);
		}

		return redirect()->to("/topic/{$thread->id}");
	}
}
