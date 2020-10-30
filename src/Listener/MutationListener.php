<?php
/**
 * @copyright 2016-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Component\EntityMutation\Listener;

use Hostnet\Component\EntityMutation\Mutation;
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
     * @param MutationResolverInterface $resolver
     */
    public function __construct(MutationResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * @param EntityChangedEvent $event
     */
    public function entityChanged(EntityChangedEvent $event)
    {
        $em     = $event->getEntityManager();
        $entity = $event->getCurrentEntity();

        if (null === ($annotation = $this->resolver->getMutationAnnotation($em, $entity))) {
            return;
        }

        $fields = array_intersect($event->getMutatedFields(), $this->resolver->getMutatableFields($em, $entity));

        if (empty($fields)) {
            return;
        }

        $strategy = $annotation->getStrategy();

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
}
