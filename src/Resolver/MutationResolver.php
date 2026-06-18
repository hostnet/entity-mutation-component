<?php
/**
 * @copyright 2016-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Component\EntityMutation\Resolver;

use Doctrine\ORM\EntityManagerInterface;
use Hostnet\Component\EntityMutation\Attributes\Mutation;
use Hostnet\Component\EntityMutation\Mutation as MutationAnnotation;
use Hostnet\Component\EntityTracker\Provider\EntityMetadataProvider;

class MutationResolver implements MutationResolverInterface
{
    /**
     * @var string
     */
    private $annotation = MutationAnnotation::class;

    public function __construct(private EntityMetadataProvider $provider)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getMutationAnnotation(EntityManagerInterface $em, $entity): ?MutationAnnotation
    {
        return $this->provider->getAnnotationFromEntity($em, $entity, $this->annotation);
    }

    public function getMutationAttribute(EntityManagerInterface $em, $entity): ?Mutation
    {
        return $this->provider->getAttributeFromEntity(Mutation::class, $em, $entity);
    }

    /**
     * {@inheritdoc}
     */
    public function getMutationClassName(EntityManagerInterface $em, $entity): string
    {
        $annotation = $this->getMutationAnnotation($em, $entity);
        // If $annotation is null, we must be using the attribute, otherwise this code would not get hit.
        if (null === $annotation) {
            return get_class($entity) . 'Mutation';
        }

        return !empty($annotation->class) ? $annotation->class : get_class($entity) . 'Mutation';
    }

    /**
     * {@inheritdoc}
     */
    public function getMutatableFields(EntityManagerInterface $em, $entity): array
    {
        $mutation_class = $this->getMutationClassName($em, $entity);
        $metadata       = $em->getClassMetadata(get_class($entity));
        $mutation_meta  = $em->getClassMetadata($mutation_class);

        return array_merge(
            array_values(array_intersect(
                $metadata->getFieldNames(),
                $mutation_meta->getFieldNames()
            )),
            array_values(array_intersect(
                $metadata->getAssociationNames(),
                $mutation_meta->getAssociationNames()
            ))
        );
    }
}
