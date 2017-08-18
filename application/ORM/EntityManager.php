<?php namespace App\ORM;

use CodeIgniter\Model;

class EntityManager extends Model
{
	const ONE_TO_ONE = 1;
	const ONE_TO_MANY = 2;
	const BELONGS_TO = 3;
	const MANY_TO_MANY = 4;

	/**
	 * Stores the relationships for this model.
	 * 
	 * @var array
	 */
	protected $relationships = [];

	protected $eagerLoad;

	public function __construct(...$params)
	{
		parent::__construct(...$params);

		// Will need this for determining keys and aliases for relations
		helper('inflector');

		// Hook into the model after finding so we can fill relations, if needed
		$this->afterFind[] = 'fillRelations';

		$this->initialize();
	}

	/**
	 * Used by class that extends this class
	 * to setup the relationships.
	 *
	 * @return mixed
	 */
	protected function initialize()
	{

	}

	//--------------------------------------------------------------------

	//--------------------------------------------------------------------
	// Defining Relationships
	//--------------------------------------------------------------------

	/**
	 * Specifies that one or more relationships should be eager loaded
	 * whenever a new object of this type is created.
	 *
	 * Example:
	 *  $forums = $forumManager
	 *             ->with('threads', 'forums')
	 *             ->find(10);
	 *
	 * @param array ...$relations
	 *
	 * @return $this
	 */
	public function with(...$relations)
	{
		$this->eagerLoad = $relations;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Defines a 1-1 relationship
	 *
	 * @param string      $className
	 * @param string|null $foreignKey
	 * @param string|null $localKey
	 * @param string|null $alias
	 */
	public function hasOne(string $className, string $foreignKey=null, string $localKey=null, string $alias=null)
	{
		$this->defineRelationship(static::ONE_TO_ONE, $className, $foreignKey, $localKey, $alias);
	}

	/**
	 * Defines a 1-n relationship.
	 *
	 * @param string      $className
	 * @param string|null $foreignKey
	 * @param string|null $localKey
	 * @param string|null $alias
	 */
	public function hasMany(string $className, string $foreignKey=null, string $localKey=null, string $alias=null)
	{
		$this->defineRelationship(static::ONE_TO_MANY, $className, $foreignKey, $localKey, $alias);
	}

	/**
	 * Defines an n-1 relationship.
	 *
	 * @param string      $className
	 * @param string|null $foreignKey
	 * @param string|null $localKey
	 * @param string|null $alias
	 */
	public function belongsTo(string $className, string $foreignKey = null, string $localKey=null, string $alias=null)
	{
		$this->defineRelationship(static::BELONGS_TO, $className, $foreignKey, $localKey, $alias);
	}

	public function hasManyToMany()
	{
		throw new \BadMethodCallException('Many to Many relationship are not implemented yet.');
	}

	/**
	 * Given credentials, will store the relationship details locally.
	 *
	 * @param int         $type
	 * @param string      $className
	 * @param string|null $foreignKey
	 * @param string|null $localKey
	 * @param string|null $alias
	 */
	protected function defineRelationship(int $type, string $className, string $foreignKey=null, string $localKey=null, string $alias=null)
	{
		$alias = $this->determineAlias($alias, $className);

		$this->relationships[$alias] = [
			'class'     => $className,
			'foreign'   => $this->determineForeignKey($foreignKey),
			'local'     => $localKey ?? $this->primaryKey,
			'type'      => $type
		];
	}

	/**
	 * Tries to create a good default alias for a relationship based on the classname
	 * of the Manager/Model.
	 *
	 * @param string|null $alias
	 * @param string      $className
	 *
	 * @return mixed|string
	 */
	protected function determineAlias(string $alias = null, string $className)
	{
		if (! empty($alias)) return $alias;

		$alias = trim(substr($className, strrpos($className, '\\')), '\\ ');

		// Remove some common class qualifiers
		$alias = str_replace('Manager', '', $alias);
		$alias = str_replace('Model', '', $alias);

		$alias = strtolower($alias);

		$alias = plural($alias);

		return $alias;
	}

	//--------------------------------------------------------------------

	/**
	 * Attempts to automatically determine the foreign key (that would point
	 * back to this class) based on the class name.
	 *
	 * @param string|null $foreignKey
	 *
	 * @return string
	 */
	protected function determineForeignKey(string $foreignKey = null): string
	{
		if (! empty($foreignKey)) return $foreignKey;

		$className = get_class($this);

		$key = trim(substr($className, strrpos($className, '\\')), '\\ ');

		// Remove some common class qualifiers
		$key = str_replace('Manager', '', $key);
		$key = str_replace('Model', '', $key);

		$key = singular(strtolower($key)).'_id';

		return $key;
	}

	//--------------------------------------------------------------------

	/**
	 * Used as a Hook into the model's afterFind events, this
	 * will read the relationships that have been defined to be
	 * eager-loaded and load them into the Entity.
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	protected function fillRelations(array $data)
	{
		if (empty($this->eagerLoad)) return $data;

		// Don't let it get into a loop...
		$relations = $this->eagerLoad;
		$this->eagerLoad = null;

		if (is_array($data['data']) && count($data['data']))
		{
			foreach ($relations as $relation)
			{
				if (! array_key_exists($relation, $this->relationships))
				{
					throw new \BadMethodCallException($relation .' has not been defined and cannot be eager-loaded.');
				}

				switch ($this->relationships[$relation]['type'])
				{
					case static::ONE_TO_ONE:
						$data['data'] = $this->fillOneToOne($data['data'], $this->relationships[$relation]);
						break;
					case static::ONE_TO_MANY:
						$data['data'] = $this->fillOneToMany($data['data'], $this->relationships[$relation], $relation);
						break;
					case static::BELONGS_TO:
						$data['data'] = $this->fillManyToOne($data['data'], $this->relationships[$relation]);
						break;
					case static::MANY_TO_MANY:
						$data['data'] = $this->fillManyToMany($data['data'], $this->relationships[$relation]);
						break;
				}
			}
		}

		return $data;
	}

	//--------------------------------------------------------------------

	/**
	 * Finds any entities in a one-to-many relationship with the passed in entities.
	 *
	 * @param array  $entities
	 * @param array  $info
	 * @param string $relation
	 *
	 * @return array
	 */
	public function fillOneToMany(array $entities, array $info, string $relation)
	{
		if (empty($entities)) return $entities;

		// Rebuild the array so that the keys are the category id for easier assignment.
		$newEntities = [];
		foreach ($entities as $entity)
		{
			$newEntities[$entity->{$info['local']}] = $entity;
		}
		$entities = $newEntities;
		unset($newEntities);

		// Get a list of ids
		$entityIDs = [];
		foreach ($entities as $entity)
		{
			$entityIDs[] = $entity->{$info['local']};
		}

		// Get the related entities
		$relatives = $this->whereIn($info['foreign'], $entityIDs)->findAll();

		foreach ($relatives as $relative)
		{
			$entities[$relative->{$info['foreign']}]->{$relation}[$relative->{$info['local']}] = $relative;
		}

		return $entities;
	}

}
