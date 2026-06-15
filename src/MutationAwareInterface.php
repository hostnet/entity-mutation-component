<?php
/**
 * @copyright 2014-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Component\EntityMutation;

/**
 * TODO: add typehints on next BC break, removing doctrine/annotations
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
