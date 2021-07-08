README
======

 - [What is the Mutation Component?](#what-is-the-entity-mutation-component)
 - [Requirements](#requirements)
 - [Installation](#installation)

### Documentation
   - [How does it work?](#how-does-it-work)
   - [Setup](#setup)
     - [Registering the Events](#registering-the-events)
     - [Configuring the Entity](#configuring-the-entity)
     - [Creating the Mutation Entity](#creating-the-mutation-entity)
     - [What's Next?](#whats-next)

What is the Entity Mutation Component?
--------------------------------------
The Entity Mutation Component is a library that utilizes the [Entity Tracker Component](https://github.com/hostnet/entity-tracker-component/) and lets you hook in to the entityChanged event.

This component lets you automatically store mutations based on two different strategies: copy current and copy previous. The first copies the current state into the mutation Entity and the latter will copy the previous state into the mutation Entity.

Requirements
------------
The Entity Mutation Component requires a minimum of php 7.3 and runs on Doctrine2. For specific requirements, please check [composer.json](../master/composer.json).

Installation
------------

Installing is pretty easy, this package is available on [packagist](https://packagist.org/packages/hostnet/entity-mutation-component). You can register the package locked to a major as we follow [Semantic Versioning 2.0.0](http://semver.org/).

#### Example

```
$ composer require hostnet/entity-mutation-component
```

Documentation
=============

How does it work?
-----------------

It works by putting the `@Mutation` annotation on your Entity and registering the listener on the entityChanged event, assuming you have already configured the [Entity Tracker Component](https://github.com/hostnet/entity-tracker-component/#setup).

For a usage example, follow the setup below.

Setup
-----
 - You have to add `@Mutation` to your Entity
 - You have to create your Mutation Entity
 - Optionally you can add the `MutationAwareInterface` if your Entity knows about its own mutations


#### Registering the events

Here's an example of a very basic setup. Setting this up will be a lot easier if you use a framework that has a Dependency Injection Container.

It might look a bit complicated to set up, but it's pretty much setting up the tracker component for the most part. If you use it in a framework, it's recommended to create a framework specific configuration package for this to automate this away.

> Note: If you use Symfony, you can take a look at the [hostnet/entity-tracker-bundle](https://github.com/hostnet/entity-tracker-bundle). This bundle is designed to configure the services for you.

```php

use Hostnet\Component\EntityMutation\Resolver\MutationResolver;
use Hostnet\Component\EntityTracker\Listener\EntityChangedListener;
use Hostnet\Component\EntityTracker\Provider\EntityAnnotationMetadataProvider;
use Hostnet\Component\EntityTracker\Provider\EntityMutationMetadataProvider;

/* @var $em \Doctrine\ORM\EntityManager */
$event_manager = $em->getEventManager();

// default doctrine annotation reader
$annotation_reader = new AnnotationReader();

// setup required providers
$annotation_metadata_provider = new EntityAnnotationMetadataProvider($annotation_reader);
$mutation_metadata_provider   = new EntityMutationMetadataProvider($annotation_reader);

// pre flush event listener that uses the @Mutation annotation
$entity_changed_listener = new EntityChangedListener(
    $annotation_metadata_provider,
    $mutation_metadata_provider
);

// the resolver is used to find the correct annotation, which
// fields are considered to be tracked and stored as mutation
// and which entity represents your Mutation entity.
$mutation_resolver = new MutationResolver($annotation_metadata_provider);

// creating the mutation listener
$mutation_listener = new MutationListener($mutation_resolver);

// register the events
$event_manager->addEventListener('prePersist', $entity_changed_listener);
$event_manager->addEventListener('preFlush', $entity_changed_listener);
$event_manager->addEventListener('entityChanged', $mutation_listener);

```

#### Configuring the Entity
All we have to do now is put the `@Mutation` annotation on our Entity. The annotation has 2 options:
 - strategy; This will determine if the current state or the previous state is stored in the Mutation
 - class; the full namespace to your mutation class. By default it's the current class name suffixed with Mutation (i.e. `Acme\MyEntity` would be `Acme\MyEntityMutation` by default).

Additionally you can configure your entity to be MutationAware, this is optional however.

```php

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Hostnet\Component\EntityMutation\Mutation;
use Hostnet\Component\EntityMutation\MutationAwareInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="users")
 * @Mutation(
 *     class    = "MyUserEntityMutation",
 *     strategy = "previous"
 * )
 * The above values are equal to the defaults. They are only
 * here to show how you can use them outside of this example
 */
class MyUserEntity implements MutationAwareInterface
{
    ...
    private $id;

    /**
     * @ORM\...
     */
    private $city;

    /**
     * @ORM\...
     */
    private $name;

    /**
     * @ORM\OneToMany...
     * @var ArrayCollection
     */
    private $mutations;

    public function setName($name) { ... }
    public function getName() { ... }

    public function addMutation($element)
    {
        $this->mutations->add($element);
    }

    public function getMutations()
    {
        return $this->mutations;
    }

    /**
     * Used to get the last mutation stored, you might want to change
     * it to return the one before that if your strategy is current.
     */
    public function getPreviousMutation()
    {
        $criteria = (new Criteria())
            ->orderBy(['id' => Criteria::DESC])
            ->setMaxResults(1);

        return $this->mutations->matching($criteria)->current() ? : null;
    }
}

```

#### Creating the Mutation Entity
The Mutation is an Entity itself. In the current version, the MutationResolver will only return the mutated fields if they are shared between the Entity and the EntityMutation. This is easily done by adding a trait that contains the shared fields. In this example, the only property that will be used to store a mutation, is `$name`.

The constructor is one of the few actual conventions you should follow in order to use the mutations. The first parameter is the current & managed entity, where the original data is the previous state (as doctrine hydrated it the last time you retrieved it) and is unmanaged by doctrine.
 
This is done so you have full control over the what fields and how you want to store mutations. For instance, in some cases you might want to summarize or convert certain fields which would not be possible if this were done automatically without a complex system of data transformers.

> Note: The $original_data is an unmanaged entity and should only be used for reading properties. Use the first parameter for joins.

```php

use Doctrine\ORM\Mapping as ORM;

class MyUserEntityMutation
{
    ...

    /**
     * @ORM\ManyToOne(targetEntity="MyUserEntity", inversedBy="mutations")
     */
    private $user;

    /**
     * @ORM\...
     */
    private $name;

    public function setName($name) { ... }
    public function getName() { ... }

    public function __construct(MyUserEntity $user, MyUserEntity $original_data)
    {
        // link our user to the mutation
        $this->user = $user;

        // populate the mutation with data from the previous state
        $this->name = $original_data->getName();
    }
}


```

A fully [working example](test/Functional/Entity) can be found in our tests.

#### What's next?

```php

$my_user_entity->setName('Henk'); // was Hans before
$em->flush();
var_dump($my_user_entity->getPreviousMutation()); // shows the state it had with Hans

```
