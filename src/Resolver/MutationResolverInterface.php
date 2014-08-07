<?php
namespace Hostnet\Component\EntityMutation\Resolver;

use Doctrine\ORM\EntityManagerInterface;
use Hostnet\Component\EntityMutation\Mutation;

/**
 * @author Iltar van der Berg <ivanderberg@hostnet.nl>
 * @author Yannick de Lange <ydelange@hostnet.nl>
 */
interface MutationResolverInterface
{
    /**
     * Return the mutation annotation
     *
     * @return Mutation
     */
    public function getMutationAnnotation(EntityManagerInterface $em, $entity);

    /**
     * Return the mutation class name
     *
     * @return string
     */
    public function getMutationClassName(EntityManagerInterface $em, $entity);

    /**
     * Return list of mutatable fields
     *
     * @return string[]
     */
    public function getMutatableFields(EntityManagerInterface $em, $entity);
}
