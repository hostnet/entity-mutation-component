<?php
/**
 * @copyright 2014-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Component\EntityMutation;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Hostnet\Component\EntityMutation\Mutation
 */
class MutationTest extends TestCase
{
    public function testGetStrategy()
    {
        $mutation = new Mutation();
        $this->assertEquals(Mutation::STRATEGY_COPY_PREVIOUS, $mutation->getStrategy());
        $mutation->strategy = Mutation::STRATEGY_COPY_CURRENT;
        $this->assertEquals(Mutation::STRATEGY_COPY_CURRENT, $mutation->getStrategy());
    }

    /**
     * @dataProvider getStrategyExceptionProvider
     * @param mixed $strategy
     */
    public function testGetStrategyException($strategy): void
    {
        $mutation           = new Mutation();
        $mutation->strategy = $strategy;
        $this->expectException(\RuntimeException::class);
        $mutation->getStrategy();
    }

    public function getStrategyExceptionProvider(): iterable
    {
        return [
            [null],
            [''],
            ['test'],
            ['last'],
            ['before'],
            [true],
            [false],
            [0],
            [-1],
            [1],
            [1000],
            [-1000],
        ];
    }
}
