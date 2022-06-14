<?php

declare(strict_types=1);

namespace ApheleiaCli\Tests;

use ApheleiaCli\AbstractInvocationStrategy;
use ApheleiaCli\Command;
use PHPUnit\Framework\TestCase;

class AbstractInvocationStrategyTest extends TestCase
{
    public function testWithContext()
    {
        $strategy = new class () extends AbstractInvocationStrategy {
            public function call($callback)
            {
                //
            }

            public function callCommandHandler(Command $command)
            {
                //
            }

            public function getContext(): array
            {
                return $this->context;
            }
        };

        $context = ['one' => 'two', 'three' => 'four'];
        $newStrategy = $strategy->withContext($context);

        $this->assertInstanceOf(AbstractInvocationStrategy::class, $newStrategy);
        $this->assertNotSame($strategy, $newStrategy);
        $this->assertSame([], $strategy->getContext());
        $this->assertSame($context, $newStrategy->getContext());
    }
}
