<?php

use App\Domains\Forums\Post;
use App\Domains\Forums\Forum;
use App\Domains\Forums\Thread;
use CodeIgniter\Database\Seeder;
use App\Domains\Forums\PostModel;
use App\Domains\Forums\ForumManager;
use App\Domains\Forums\ThreadModel;

class DevSeeder extends Seeder
{
	protected $forumModel;
	protected $postModel;
	protected $threadModel;
	protected $faker;

	public function run()
	{
		$this->forumModel  = new ForumManager();
		$this->postModel   = new PostModel();
		$this->threadModel = new ThreadModel();
		$this->faker       = \Faker\Factory::create();

		// WE don't want to validate our data for this...
		$this->forumModel->skipValidation(true);
		$this->postModel->skipValidation(true);
		$this->threadModel->skipValidation(true);

		$forums = $this->fillForums(3, 5);
		$this->fillThreads($forums, 3, 5);
	}

	protected function fillForums(int $catCount, int $forumCount)
	{
		$forums     = [];
		$categories = [];

		// Create our categories
		for ($i = 0; $i < $catCount; $i++)
		{
			$cat = new Forum();
			$cat->fill([
				'name'         => $this->faker->sentence(),
				'description'  => $this->faker->paragraph(),
				'is_category'  => 1,
				'thread_count' => 0,
				'post_count'   => 0,
				'last_post'    => 0,
			]);
			$cat->id = $this->forumModel->insert($cat);

			if ($cat->id === false)
			{
				dd($this->db->error());
			}

			$categories[] = $cat;
		}

		// Create forums for each category
		foreach ($categories as $category)
		{
			for ($i = 0; $i < $forumCount; $i++)
			{
				$forum = new Forum();
				$forum->fill([
					'name'         => $this->faker->sentence(),
					'description'  => $this->faker->paragraph(),
					'is_category'  => 0,
					'forum_id'     => $category->id,
					'thread_count' => 0,
					'post_count'   => 0,
					'last_post'    => 0,
				]);
				$forum->id = $this->forumModel->insert($forum);
				$forums[]  = $forum;

				if ($forum->id === false)
				{
					dd($this->db->error());
				}
			}
		}

		return $forums;
	}

	public function fillThreads(array $forums, int $threadCount, int $postCount)
	{
		foreach ($forums as $forum)
		{
			$threads = [];

			// Create threads...
			for ($i = 0; $i < $threadCount; $i++)
			{
				$thread = new Thread();
				$thread->fill([
					'user_id'   => 1,
					'forum_id' => $forum->id,
					'title' => $this->faker->sentence(),
					'first_post' => 0,
					'view_count' => 0,
					'post_count' => 0
				]);
				$thread->id = $this->threadModel->insert($thread);
				$threads[] = $thread;
				$forum->thread_count++;

				if ($thread->id === false)
				{
					d((string)$this->db->getLastQuery());
					dd($this->db->error());
				}
			}

			$isFirst = true;
			$lastPostID = 0;

			// Fill the threads with posts...
			foreach ($threads as $thread)
			{
				$post = new Post();
				$post->fill([
					'forum_id' => $forum->id,
					'thread_id' => $thread->id,
					'user_id' => 1,
					'title' => $this->faker->sentence(),
					'body' => $this->faker->paragraphs(3, true)
				]);
				$post->id = $this->postModel->insert($post);
				$lastPostID = $post->id;
				$forum->post_count++;

				if ($post->id === false)
				{
					d((string)$this->db->getLastQuery());
					dd($this->db->error());
				}

				if ($isFirst)
				{
					$isFirst = false;
					$thread->first_post = $post->id;
					$this->threadModel->save($thread);
				}
			}

			$forum->last_post = $lastPostID;
			$this->forumModel->save($forum);
		}

	}

}
