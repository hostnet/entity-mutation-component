<?php
/**
 * @copyright 2016-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Component\EntityMutation\Listener;

use Hostnet\Component\EntityMutation\Attributes\Mutation;
use Hostnet\Component\EntityMutation\MutationAwareInterface;
use Hostnet\Component\EntityMutation\Resolver\MutationResolverInterface;
use Hostnet\Component\EntityTracker\Event\EntityChangedEvent;

class MutationListener
{
    /**
     * @var MutationResolverInterface
     */
    private $resolver;

    /**
     * Caches the class names to prevent iterating over attribute and annotations again on the next entity.
     */
    private array $is_mutation_cache = [];

    /**
     * @param MutationResolverInterface $resolver
     */
    public function __construct(MutationResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * @param EntityChangedEvent $event
     */
    public function entityChanged(EntityChangedEvent $event): void
    {
        $em     = $event->getEntityManager();
        $entity = $event->getCurrentEntity();

        if (false === $strategy = $this->getMutationStrategy($em, $entity)) {
            return;
        }

        $fields = array_intersect($event->getMutatedFields(), $this->resolver->getMutatableFields($em, $entity));

        if (empty($fields)) {
            return;
        }

        if ($strategy === Mutation::STRATEGY_COPY_PREVIOUS && null === $event->getOriginalEntity()) {
            return;
        }

        switch ($strategy) {
            case Mutation::STRATEGY_COPY_CURRENT:
                $mutation_source = $entity;
                break;
            case Mutation::STRATEGY_COPY_PREVIOUS:
                $mutation_source = $event->getOriginalEntity();
                break;
            default:
                throw new \RuntimeException(sprintf("Unknown strategy '%s'.", $strategy));
        }

        $mutation = $em
            ->getClassMetadata($this->resolver->getMutationClassName($em, $entity))
            ->getReflectionClass()
            ->newInstance($entity, $mutation_source);

        $em->persist($mutation);

        if ($entity instanceof MutationAwareInterface) {
            $entity->addMutation($mutation);
        }
    }

    private function getMutationStrategy($em, $entity): false|string
    {
        $class = get_class($entity);
        if (array_key_exists($class, $this->is_mutation_cache)) {
            return $this->is_mutation_cache[$class];
        }

        if (null !== $annotation = $this->resolver->getMutationAnnotation($em, $entity)) {
            $this->is_mutation_cache[$class] = $annotation->getStrategy();

            return $this->is_mutation_cache[$class];
        }

        if (null !== $strategy = $this->getMutationAttributeStrategy($entity)) {
            $this->is_mutation_cache[$class] = $strategy;

            return $this->is_mutation_cache[$class];
        }

        $this->is_mutation_cache[$class] = false;

        return false;
    }

    private function getMutationAttributeStrategy($entity): ?string
    {
        $reflection = new \ReflectionClass($entity);
        $attributes = $reflection->getAttributes(Mutation::class);

        if (empty($attributes)) {
            return null;
        }

        /** @var Mutation $attribute */
        $attribute = $attributes[0]->newInstance();
        return $attribute->getStrategy();
    }
}
