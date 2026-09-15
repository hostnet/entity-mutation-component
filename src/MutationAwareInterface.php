<?php
/**
 * @copyright 2014-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Component\EntityMutation;

interface MutationAwareInterface
{
    public function addMutation(object $mutation): void;

    /**
     * @return iterable<object>
     */
    public function getMutations(): iterable;

    public function getPreviousMutation(): ?object;
}
