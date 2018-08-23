<?php namespace Myth\ORM;

/**
 * Extends CodeIgniter's Entity class to provide
 * ORM capabilities, like lazy loading and paginating
 * related entity sets.
 *
 * @package Myth\ORM
 */
class Entity extends \CodeIgniter\Entity
{
	/**
	 * Array of ORM\EntityCollection instances
	 * for each related class. The key is
	 * the relationship alias.
	 *
	 * @var array
	 */
	protected $relatives = [];

	/**
	 * Overrides the toArray class method and adds
	 * in any relationships that have been loaded
	 * so they are available within the array.
	 *
	 * @return array
	 */
	public function toArray(): array
	{
		$return = parent::toArray();

		foreach ($this->relatives as $name => $relatives)
		{
			$objects = $this->{$name};

			if (! count($objects))
			{
				$return[$name] = null;
			}

			$temp = [];

			foreach ($objects as $object)
			{
				$temp[] = $object->toArray();
			}

			$return[$name] = $temp;
		}

		return $return;
	}

	//--------------------------------------------------------------------

	/**
	 * Override the core getter to add relationship detection features.
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function __get(string $key)
	{
		$key = $this->mapProperty($key);

		// Convert to CamelCase for the method
		$method = 'get' . str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $key)));

		// if a set* method exists for this key, 
		// use that method to insert this value. 
		if (method_exists($this, $method))
		{
			$result = $this->$method();
		}

		// Feturn the protected property
		// if it exists.
		else if (property_exists($this, $key))
		{
			$result = $this->$key;
		}

		// Is this a relationship?
		else if (array_key_exists($key, $this->relatives))
		{
			return $this->relatives[$key]->fetch();
		}

		// Do we need to mutate this into a date?
		if (in_array($key, $this->_options['dates']))
		{
			$result = $this->mutateDate($result);
		}
		// Or cast it as something?
		else if (array_key_exists($key, $this->_options['casts']))
		{
			$result = $this->castAs($result, $this->_options['casts'][$key]);
		}

		return $result;
	}

	//--------------------------------------------------------------------
}
