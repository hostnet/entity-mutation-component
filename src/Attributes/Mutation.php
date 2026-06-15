<?php
/**
 * @copyright 2026-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Component\EntityMutation\Attributes;

use Hostnet\Component\EntityTracker\Attributes\Tracked;

#[\Attribute(\Attribute::TARGET_CLASS)]
class Mutation extends Tracked
{
    public function __construct(
        public string $strategy = self::STRATEGY_COPY_PREVIOUS,
    ) {
    }
        /**
         * The Previous values will be stored in the mutation table. This is the
         * default strategy.
         */
    public const STRATEGY_COPY_PREVIOUS = 'previous';

    /**
     * The current values will be stored in the mutation table. And the
     * mutation will also be added on creation of the entity.
     */
    public const STRATEGY_COPY_CURRENT = 'current';

    /**
     * Get the strategy for storing the mutation data.
     *
     * @return Mutation::STRATEGY_COPY_PREVIOUS|Mutation::STRATEGY_COPY_CURRENT
     */
    public function getStrategy(): string
    {
        if (!in_array($this->strategy, [self::STRATEGY_COPY_PREVIOUS, self::STRATEGY_COPY_CURRENT], true)) {
            throw new \RuntimeException(
                sprintf("Unknown strategy '%s' for class %s.", $this->strategy, get_class($this))
            );
        }

        return $this->strategy;
    }
}
