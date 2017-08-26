<?php namespace Myth\ORM\Relationships;

use ArrayAccess;
use Myth\ORM\EntityManager;

/**
 * Provides helper methods for accessing a collection of related entities,
 * including the ability to lazy-load and paginate entities.
 *
 * This collection is intended to be placed as the relationship items
 * in an actual entity class, not in the Manager class.
 *
 * @package Myth\ORM
 */
class Relationship implements ArrayAccess
{
	/**
	 * The alias of the relation
	 *
	 * @var string
	 */
	protected $relation;

	/**
	 * The name of the Manager or Model class
	 * for the related entities.
	 *
	 * @var string
	 */
	protected $modelName;

	/**
	 * Type of relationship.
	 *
	 * @see constants in EntityManager.
	 * @var int
	 */
	protected $type;

	/**
	 * The name of the column in the
	 * related entity we join on.
	 *
	 * @var string
	 */
	protected $joinColumn;

	/**
	 * The name of the column in this
	 * Entity that matches against joinColumn.
	 *
	 * @var string
	 */
	protected $localColumn;

	/**
	 * An array of possible options that
	 * were set when the relationship
	 * was defined.
	 *
	 * @var array
	 */
	protected $options = [];

	/**
	 * Local cache for related records.
	 *
	 * @var array
	 */
	protected $records = [];

	//--------------------------------------------------------------------
	// Core ORM
	//--------------------------------------------------------------------

	/**
	 * Adds a new record to the collection.
	 *
	 * @param $record
	 *
	 * @return $this
	 */
	public function add($record)
	{
		$this->records[$record->id] = $record;

		return $this;
	}

	public function fetch()
	{
		return $this->records;
	}


	//--------------------------------------------------------------------
	// Getters and Setters
	//--------------------------------------------------------------------

	/**
	 * @return string
	 */
	public function getRelation(): string
	{
		return $this->relation;
	}

	/**
	 * @param string $relation
	 *
	 * @return Relationship
	 */
	public function setRelation(string $relation): Relationship
	{
		$this->relation = $relation;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getModelName(): string
	{
		return $this->modelName;
	}

	/**
	 * @param string $modelName
	 *
	 * @return Relationship
	 */
	public function setModelName(string $modelName): Relationship
	{
		$this->modelName = $modelName;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getType(): int
	{
		return $this->type;
	}

	/**
	 * @param int $type
	 *
	 * @return Relationship
	 */
	public function setType(int $type): Relationship
	{
		$this->type = $type;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getJoinColumn(): string
	{
		return $this->joinColumn;
	}

	/**
	 * @param string $joinColumn
	 *
	 * @return Relationship
	 */
	public function setJoinColumn(string $joinColumn): Relationship
	{
		$this->joinColumn = $joinColumn;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getLocalColumn(): string
	{
		return $this->localColumn;
	}

	/**
	 * @param string $localColumn
	 *
	 * @return Relationship
	 */
	public function setLocalColumn(string $localColumn): Relationship
	{
		$this->localColumn = $localColumn;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getOptions(): array
	{
		return $this->options;
	}

	/**
	 * @param string $key
	 *
	 * @return mixed|null
	 */
	public function getOption(string $key)
	{
		if (array_key_exists($key, $this->options))
		{
			return $this->options[$key];
		}

		return null;
	}

	/**
	 * @param array $options
	 *
	 * @return Relationship
	 */
	public function setOptions(array $options): Relationship
	{
		$this->options = $options;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getRecords(): array
	{
		return $this->records;
	}

	/**
	 * @param array $records
	 *
	 * @return Relationship
	 */
	public function setRecords(array $records): Relationship
	{
		$this->records = $records;

		return $this;
	}

	//--------------------------------------------------------------------
	// Array Access
	//--------------------------------------------------------------------

	/**
	 * @param mixed $offset
	 * @param mixed $value
	 */
	public function offsetSet($offset, $value)
	{
		if (is_null($offset))
		{
			$this->records[] = $value;
		}
		else
		{
			$this->records[$offset] = $value;
		}
	}

	/**
	 * @param mixed $offset
	 *
	 * @return bool
	 */
	public function offsetExists($offset)
	{
		return isset($this->records[$offset]);
	}

	/**
	 * @param mixed $offset
	 */
	public function offsetUnset($offset)
	{
		unset($this->records[$offset]);
	}

	/**
	 * @param mixed $offset
	 *
	 * @return mixed|null
	 */
	public function offsetGet($offset)
	{
		return isset($this->records[$offset])
			? $this->records[$offset]
			: null;
	}
}
