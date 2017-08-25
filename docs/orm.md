# The ORM

This ORM is not intended to be a full-fledged, solve-every-use-case, ORM. Instead, it's a test-bed for me to play
and experiment with pieces being built and refactored as needed. 

The entire ORM is based off of CodeIgniter Entity objects, so using any other data representation will not work. 

The ORM currently supports (or will support) reading all four major relationship types: 

- One to One
- One to Many
- Many to One (Belongs To)
- Many to Many (Has and Belongs To Many)

## Getting Started

To start using the ORM, you need only extend your custom models from `App\ORM\EntityManager` instead of `CodeIgniter\Model`. 
Don't panic, though, as the EntityManager extends the Model class so you still have access to all of the model's features.
Think of it as a glorified Model with some helper functions for working with related Entities. Because that's exactly
what it is. 

### Defining the Relationships

The first step, then, is to create your new extended model. By convention, we refer to these as Manager classes. They 
should be defined exactly as you would for a new Model.

```php
<?php 

use App\ORM\EntityManager;

class ThreadManager extends EntityManager 
{
    protected $table;
    
    protected $allowedFields = ['user_id', 'forum_id', 'title'];
}
```

The next step is to define the relationships. This is done within a new method named `initialize()`. Initialization
happens during class construction, and simply records information about the relationship for later reference, using 
the following four methods: 

- hasOne()
- hasMany()
- belongsTo()
- hasAndBelongsToMany()

Each method takes a first parameter that is the name of another Manager class or Model for the related Entity type
(Manager classes are preferred). 

```php
public function initialize()
{
    $this->belongsTo('App\UserManager');
    $this->hasMany('App\PostManager');
}
```

If you follow a simple naming convention with your tables and primary keys, then this is all that you need to
do. Basically, your foreign keys should be singular with '_id' appended to it.

This example says that there should be a single User that this thread belongs to. This could also be called the 
author. In order for the conventions to locate everything, the `users` table should have a primary key called `id`, 
and the `threads` table should have a column named `user_id`. Since this relationship is a `belongs to` relationship, 
the user could be accessed through it's singular name: `$thread->user`, once loaded in.

The Posts in this example would expect to find a column named `thread_id` in the posts table, and would expect
`tables` primary key to be called `id`. Since one thread can have many posts, these entities, once loaded, can 
be retrieved through `$thread->posts`. 

### Loading the related records

Because CodeIgniter entities are completely isolated from the persistence layer, we have to specify
which related objects to load when the Entity is first created. There is not currently any way to lazily load
the related records after instantiation. We tell the Manager class which relationships to load through the
`with()` method:

```php
$threads = $threadManager->with('user', 'posts')->findAll();
```
