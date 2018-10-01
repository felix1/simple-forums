<?php namespace App\Domains\Views;

class Theme
{
	/**
	 * The name of the theme to use.
	 *
	 * @var string
	 */
	protected static $theme = 'default';

	/**
	 * The layout to use for this page.
	 *
	 * @var string
	 */
	protected static $layout = 'master';

	/**
	 * Attempts to set the current them in use.
	 *
	 * @param string $theme
	 */
	public static function setTheme(string $theme)
	{
		if (! is_dir(ROOTPATH."themes/{$theme}"))
		{
			throw new \RuntimeException('Invalid theme: '. $theme);
		}

		static::$theme = $theme;
	}

	/**
	 * Returns the name of the current theme.
	 *
	 * @return string
	 */
	public static function name()
	{
		return static::$theme;
	}

	/**
	 * Returns the path to the current theme directory.
	 */
	public static function path()
	{
		return ROOTPATH."themes/". static::$theme .'/';
	}

	/**
	 * Sets the current layout.
	 *
	 * @param string $layout
	 */
	public static function setLayout(string $layout)
	{
		if (! file_exists(static::path()."layouts/{$layout}.php"))
		{
			throw new \RuntimeException('Invalid layout: '. $layout);
		}

		static::$layout = $layout;
	}

	/**
	 * Returns the name of the current layout.
	 *
	 * @return string
	 */
	public static function layout()
	{
		return static::$layout;
	}

	/**
	 * @param string $path
	 * @param array  $data
	 *
	 * @return mixed
	 */
	public static function view(string $path, array $data = [])
	{
		return service('renderer')->render($path, $data);
	}
}
