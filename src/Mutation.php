<?php
/**
 * @copyright 2016-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Component\EntityMutation;

use Hostnet\Component\EntityTracker\Annotation\Tracked;

/**
 * @Annotation
 * @Target({"CLASS"})
 *
 * @deprecated Please use the attribute instead.
 */
class Mutation extends Tracked
{
    /**
     * The Previous values will be stored in the mutation table. This is the
     * default strategy.
     */
    public const string STRATEGY_COPY_PREVIOUS = 'previous';

    /**
     * The Previous values will be stored in the mutation table. And the
     * mutation will also be added on creation of the entity.
     */
    public const string STRATEGY_COPY_CURRENT = 'current';

    /**
     * @deprecated Not supported by the attribute
     */
    public $class = '';

    /**
     * @Enum({"previous", "current"})
     */
    public $strategy = self::STRATEGY_COPY_PREVIOUS;

    /**
     * Get the strategy for storing the mutation data.
     *
     * @return Mutation::STRATEGY_COPY_PREVIOUS|Mutation::STRATEGY_COPY_CURRENT
     *
     * @deprecated Please use the attribute instead.
     */
    public function getStrategy()
    {
        if (!in_array($this->strategy, [self::STRATEGY_COPY_PREVIOUS, self::STRATEGY_COPY_CURRENT], true)) {
            throw new \RuntimeException(
                sprintf("Unknown strategy '%s' for class %s.", $this->strategy, get_class($this))
            );
        }

        return $this->strategy;
    }
}
