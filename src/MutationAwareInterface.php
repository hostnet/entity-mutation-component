<?php
/**
 * @copyright 2014-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Component\EntityMutation;

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
