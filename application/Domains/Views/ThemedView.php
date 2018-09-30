<?php namespace App\Domains\Views;

use CodeIgniter\View\View;

class ThemedView extends View
{
	/**
	 * Holds the sections and their data.
	 *
	 * @var array
	 */
	protected $sections = [];

	/**
	 * Name of the current section that's being rendered.
	 *
	 * @var string
	 */
	protected $currentSection;

	/**
	 * Starts rendering data for a section.
	 *
	 * @param string $sectionName
	 */
	public function section(string $sectionName)
	{
		ob_start();

		$this->currentSection = $sectionName;
	}

	/**
	 * Closes a section
	 */
	public function endsection()
	{
		$contents = ob_get_clean();

		if (empty($this->currentSection))
		{
			throw new \RuntimeException('View themes, no current section.');
		}

		// Ensure an array exists so we can store multiple entries for this.
		if (! array_key_exists($this->currentSection, $this->sections))
		{
			$this->sections[$this->currentSection] = [];
		}

		$this->sections[$this->currentSection][] = $contents;

		$this->currentSection = null;
	}

	/**
	 * Renders a section's contents.
	 *
	 * @param string $sectionName
	 */
	public function renderSection(string $sectionName)
	{
		if (! isset($this->sections[$sectionName]))
		{
			echo '';
			return;
		}

		foreach ($this->sections[$sectionName] as $contents)
		{
			echo $contents;
		}
	}
}
