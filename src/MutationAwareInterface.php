<?php
namespace Hostnet\Component\EntityMutation;

/**
 * @author Yannick de Lange <ydelange@hostnet.nl>
 */
interface MutationAwareInterface
{
    /**
     * @param mixed $mutation
     */
    public function addMutation($mutation);

    /**
     * @return array of mutations
     */
    public function getMutations();

    /**
     * @return mixed previous mutation object
     */
    public function getPreviousMutation();
}
