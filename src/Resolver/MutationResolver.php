<?php
namespace Hostnet\Component\EntityMutation\Resolver;

use Doctrine\ORM\EntityManagerInterface;
use Hostnet\Component\EntityMutation\Mutation;
use Hostnet\Component\EntityTracker\Provider\EntityAnnotationMetadataProvider;

/**
 * @author Iltar van der Berg <ivanderberg@hostnet.nl>
 * @author Yannick de Lange <ydelange@hostnet.nl>
 */
class MutationResolver implements MutationResolverInterface
{
    /**
     * @var string
     */
    private $annotation = Mutation::class;

    /**
     * @var EntityAnnotationMetadataProvider
     */
    private $provider;

    /**
     * @param EntityAnnotationMetadataProvider $provider
     */
    public function __construct(EntityAnnotationMetadataProvider $provider)
    {
        $this->provider = $provider;
    }

    /**
     * {@inheritdoc}
     */
    public function getMutationAnnotation(EntityManagerInterface $em, $entity)
    {
        return $this->provider->getAnnotationFromEntity($em, $entity, $this->annotation);
    }

    /**
     * {@inheritdoc}
     */
    public function getMutationClassName(EntityManagerInterface $em, $entity)
    {
        if (null === ($annotation = $this->getMutationAnnotation($em, $entity))) {
            return null;
        }

        return !empty($annotation->class) ? $annotation->class : get_class($entity) . 'Mutation';
    }

    /**
     * {@inheritdoc}
     */
    public function getMutatableFields(EntityManagerInterface $em, $entity)
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
