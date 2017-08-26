<?php namespace Myth\ORM;

use CodeIgniter\Model;
use Myth\ORM\Relationships\Relationship;

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
	 * @param array       $options
	 */
	public function hasOne(string $className, string $foreignKey=null, string $localKey=null, string $alias=null, array $options=[])
	{
		$this->defineRelationship(static::ONE_TO_ONE, $className, $foreignKey, $localKey, $alias, $options);
	}

	/**
	 * Defines a 1-n relationship.
	 *
	 * @param string      $className
	 * @param string|null $foreignKey
	 * @param string|null $localKey
	 * @param string|null $alias
	 * @param array|null  $options
	 */
	public function hasMany(string $className, string $foreignKey=null, string $localKey=null, string $alias=null, array $options=[])
	{
		$this->defineRelationship(static::ONE_TO_MANY, $className, $foreignKey, $localKey, $alias, $options);
	}

	/**
	 * Defines an n-1 relationship.
	 *
	 * @param string      $className
	 * @param string|null $foreignKey
	 * @param string|null $localKey
	 * @param string|null $alias
	 */
	public function belongsTo(string $className, string $foreignKey = null, string $localKey=null, string $alias=null, array $options=[])
	{
		$this->defineRelationship(static::BELONGS_TO, $className, $foreignKey, $localKey, $alias, $options);
	}

	public function hasAndBelongsToMany()
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
	protected function defineRelationship(int $type, string $className, string $foreignKey=null, string $localKey=null, string $alias=null, array $options=[])
	{
		$alias = $this->determineAlias($alias, $className, $type);

		$this->relationships[$alias] = (new Relationship())
			->setModelName($className)
			->setJoinColumn($this->determineForeignKey($foreignKey, $type))
			->setLocalColumn($this->determineLocalKey($localKey, $type, $className))
			->setType($type)
			->setRelation($alias)
			->setOptions($options);
	}

	/**
	 * Tries to create a good default alias for a relationship based on the classname
	 * of the Manager/Model.
	 *
	 * @param string|null $alias
	 * @param string      $className
	 * @param int         $type
	 *
	 * @return mixed|string
	 */
	protected function determineAlias(string $alias = null, string $className, int $type)
	{
		if (! empty($alias)) return $alias;

		$alias = $this->convertClassNameToKey($className, true);

		$alias = $type !== static::BELONGS_TO
			? plural($alias)
			: singular($alias);

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
	protected function determineForeignKey(string $foreignKey = null, int $type): string
	{
		if (! empty($foreignKey)) return $foreignKey;

		if ($type === static::BELONGS_TO)
		{
			return 'id';
		}

		return $this->convertClassNameToKey(get_class($this)).'_id';
	}

	//--------------------------------------------------------------------

	/**
	 * Attempts to automatically determine the local key (the column in this
	 * row that points to the other record) based on the classname.
	 *
	 * @param string|null $key
	 * @param int         $type
	 * @param string      $className
	 *
	 * @return mixed|string
	 */
	protected function determineLocalKey(string $key=null, int $type, string $className)
	{
		if (! empty($key)) return $key;

		// BelongsTo relationships will base their key off of the
		// related class.
		if ($type === static::BELONGS_TO)
		{
			return $this->convertClassNameToKey($className).'_id';
		}

		// All others should use our primary key.
		return $this->primaryKey;
	}

	//--------------------------------------------------------------------

	/**
	 * Given a class name, will simplify it and convert it into something
	 * that we expect a key might look like.
	 *
	 * @param string $class
	 * @param bool   $maintainTense
	 *
	 * @return mixed|string
	 */
	protected function convertClassNameToKey(string $class, bool $maintainTense = false)
	{
		$key = trim(substr($class, strrpos($class, '\\')), '\\ ');

		// Remove some common class qualifiers
		$key = str_replace('Manager', '', $key);
		$key = str_replace('Model', '', $key);

		$key = strtolower($key);

		return $maintainTense
			? $key
			: singular($key);
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
		if (empty($data['data'])) return $data;

		$entities = $data['data'];
		if (is_object($entities))
		{
			$entities = [$entities];
		}

		// Ensure a relationship collection exists
		// on each entity for the related types
		// to allow lazyl oading, pagination, etc.
		foreach ($entities as $entity)
		{
			$copy = [];
			// Ensure we have fresh copies, not references
			foreach ($this->relationships as $alias => &$relationship)
			{
				$copy[$alias] = clone $relationship;
			}

			$entity->relatives = $copy;
			unset($copy);
		}

		// Now handle any eager-loading of relationships
		// for better performance.
		$eagerRelatives = $this->eagerLoad ?? [];
		$this->eagerLoad = null;

		foreach ($entities[0]->relatives as $alias => $relationship)
		{
			if (! in_array($alias, $eagerRelatives)) continue;

			switch ($relationship->getType())
			{
				case static::ONE_TO_ONE:
					$data['data'] = $this->fillOneToOne($data['data'], $relationship);
					break;
				case static::ONE_TO_MANY:
					$data['data'] = $this->fillOneToMany($data['data'], $relationship);
					break;
				case static::BELONGS_TO:
					$data['data'] = $this->fillManyToOne($data['data'], $relationship);
					break;
				case static::MANY_TO_MANY:
					$data['data'] = $this->fillManyToMany($data['data'], $relationship);
					break;
			}

		}

		return $data;
	}

	//--------------------------------------------------------------------

	/**
	 * Finds any entities in a one-to-many relationship with the passed in entities.
	 *
	 * @param array                                $entities
	 * @param \Myth\ORM\Relationships\Relationship $relationship
	 *
	 * @return array
	 */
	public function fillOneToMany($entities, Relationship $relationship)
	{
		if (empty($entities)) return $entities;

		$class = $relationship->getModelName();
		$model = new $class();
		$wasSingle = is_object($entities);

		if ($wasSingle)
		{
			$entities = [$entities];
		}

		// Rebuild the array so that the keys are the category id for easier assignment.
		$newEntities = [];
		foreach ($entities as $entity)
		{
			$newEntities[$entity->{$relationship->getLocalColumn()}] = $entity;
		}
		$entities = $newEntities;
		unset($newEntities);

		// Get a list of ids
		$entityIDs = [];
		foreach ($entities as $entity)
		{
			$entityIDs[] = $entity->{$relationship->getLocalColumn()};
		}

		// Make sure the class we're eager-loading
		// has a chance to load it's own...
		if (! empty($relationship->getOption('with')) && $model instanceof EntityManager)
		{
			$with = $relationship->getOption('with');
			$with = is_array($with)
				? $with
				: [$with];
			$model = $model->with(...$with);
		}

		// Get the related entities
		$relatives = $model->whereIn($relationship->getJoinColumn(), $entityIDs)->findAll();

		foreach ($relatives as $relative)
		{
			$relID = $relative->{$relationship->getJoinColumn()};
			$entities[$relID]->relatives[$relationship->getRelation()]->add($relative);
		}

		return $wasSingle ? array_shift($entities) : $entities;
	}

	/**
	 * Finds any entities in a Many to One (or BelongsTo) relation.
	 *
	 * @param                                      $entities
	 * @param \Myth\ORM\Relationships\Relationship $relationship
	 *
	 * @return array
	 */
	public function fillManyToOne($entities, Relationship $relationship)
	{
		if (empty($entities)) return $entities;

		$class = $relationship->getModelName();
		$model = new $class();
		$wasSingle = is_object($entities);

		if ($wasSingle)
		{
			$entities = [$entities];
		}

		// Collect the unique entity id's to retrieve
		$foreignIDs = [];
		foreach ($entities as $entity)
		{
			$foreignIDs[] = $entity->{$relationship->getLocalColumn()} ?? null;
		}
		$foreignIDs = array_unique($foreignIDs);

		// Make sure the class we're eager-loading
		// has a chance to load it's own...
		if (! empty($relationship->getOption('with')) && $model instanceof EntityManager)
		{
			$with = $relationship->getOption('with');
			$with = is_array($with)
				? $with
				: [$with];
			$model = $model->with(...$with);
		}

		// Fetch all of the related entities
		$relatives = $model->find($foreignIDs);

		// Key the entity array so it's easier to fill
		$newRelatives = [];
		foreach ($relatives as $relative)
		{
			$id = $relative->{$relationship->getJoinColumn()};
			$newRelatives[$id] = $relative;
		}
		unset($relatives);

		// Stitch the related entities back into the correct parents.
		foreach ($entities as $entity)
		{
			$relatedID = $entity->{$relationship->getLocalColumn()};
			$entity->{$relationship->getRelation()} = $newRelatives[$relatedID];
		}

		return $wasSingle ? array_shift($entities) : $entities;
	}

}
