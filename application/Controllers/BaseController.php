<?php namespace App\Controllers;

use CodeIgniter\Controller;
use Config\Services;
use Myth\Auth\AuthTrait;

class BaseController extends Controller
{
	use AuthTrait;

	/**
	 * Stores view data.
	 *
	 * @var array
	 */
	protected $data = [];

	protected $theme = 'default';

	protected $layout = 'master';

	/**
	 * @var \CodeIgniter\View\View
	 */
	protected $renderer;

	/**
	 * Stores current status message.
	 *
	 * @var
	 */
	protected $message;

	//--------------------------------------------------------------------

	public function __construct(...$params)
	{
		parent::__construct(...$params);

		$this->renderer = Services::renderer(ROOTPATH."themes/{$this->theme}/");

		$this->setupAuthClasses();
	}

	public function setData(array $data)
	{
		$this->data = $data;

		return $this;
	}

	/**
	 * A simple method to allow the use of layouts and views.
	 *
	 * @param string $view
	 * @param array  $data
	 */
	public function render(string $view, array $data = [])
	{
		$data = array_merge($data, $this->data);

		// Build our notices from the theme's view file.
		$data['notice'] = $this->renderView('layouts/_notice', ["notice" => $this->message()]);

		// Pass along our auth classes
		$data['authenticate'] = $this->authenticate;
		$data['authorize']    = $this->authorize;
		$data['current_user'] = $this->authenticate->user();

		$content = $this->renderView($view, $data, ['saveData' => true]);

		$layout = $this->renderView('layouts/'.$this->layout, $data, ['saveData' => true]);
		$layout = str_replace('{content}', $content, $layout);

		echo $layout;
	}

	//--------------------------------------------------------------------

	/**
	 * Same as the global view() helper, but uses our instance of the
	 * renderer so we can render themes.
	 *
	 * @param string $name
	 * @param array  $data
	 * @param array  $options
	 *
	 * @return string
	 */
	protected function renderView(string $name, array $data = [], array $options = [])
	{
		$saveData = null;
		if (array_key_exists('saveData', $options) && $options['saveData'] === true)
		{
			$saveData = (bool) $options['saveData'];
			unset($options['saveData']);
		}

		return $this->renderer->setData($data, 'raw')
		                      ->render($name, $options, $saveData);
	}

	//--------------------------------------------------------------------
	// Status Messages
	//--------------------------------------------------------------------

	/**
	 * Sets a status message (for displaying small success/error messages).
	 * This is used in place of the session->flashdata functions since you
	 * don't always want to have to refresh the page to show the message.
	 *
	 * @param string $message The message to save.
	 * @param string $type    The string to be included as the CSS class of the containing div.
	 */
	public function setMessage($message = '', $type = 'info')
	{
		if (! empty($message))
		{
			if (isset($_SESSION))
			{
				session()->setFlashdata('message', $type.'::'.$message);
			}

			$this->message = [
				'type'    => $type,
				'message' => $message,
			];
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Retrieves the status message to display (if any).
	 *
	 * @param  string $message [description]
	 * @param  string $type    [description]
	 *
	 * @return array
	 */
	public function message(string $message = '', string $type = 'info'): array
	{
		$return = [
			'message' => $message,
			'type'    => $type,
		];

		// Does session data exist?
		if (empty($message) && isset($_SESSION))
		{
			$message = session()->getFlashdata('message');

			if (! empty($message))
			{
				// Split out our message parts
				$tempMessage       = explode('::', $message);
				$return['type']    = $tempMessage[0];
				$return['message'] = $tempMessage[1];

				unset($tempMessage);
			}
		}

		// If message is empty, we need to check our own storage.
		if (empty($message))
		{
			if (empty($this->message['message']))
			{
				return [];
			}

			$return = $this->message;
		}

		// Clear our session data so we don't get extra messages on rare occasions.
		if (isset($_SESSION))
		{
			session()->setFlashdata('message', '');
		}

		return $return;
	}

	//--------------------------------------------------------------------
}
