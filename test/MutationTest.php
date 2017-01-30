<?php
/**
 * @copyright 2014-2017 Hostnet B.V.
 */
namespace Hostnet\Component\EntityMutation;

/**
 * @author Iltar van der Berg <ivanderberg@hostnet.nl>
 * @covers Hostnet\Component\EntityMutation\Mutation
 */
class MutationTest extends \PHPUnit_Framework_TestCase
{
    public function testGetStrategy()
    {
        $mutation = new Mutation();
        $this->assertEquals(Mutation::STRATEGY_COPY_PREVIOUS, $mutation->getStrategy());
        $mutation->strategy = Mutation::STRATEGY_COPY_CURRENT;
        $this->assertEquals(Mutation::STRATEGY_COPY_CURRENT, $mutation->getStrategy());
    }

    /**
     * @expectedException RuntimeException
     * @dataProvider getStrategyExceptionProvider
     * @param string $strategy
     */
    public function testGetStrategyException($strategy)
    {
        $mutation           = new Mutation();
        $mutation->strategy = $strategy;
        $mutation->getStrategy();
    }

    public function getStrategyExceptionProvider()
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
            [-1000]
        ];
    }
}
