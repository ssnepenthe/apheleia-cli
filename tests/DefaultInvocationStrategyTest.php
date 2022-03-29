<?php

declare(strict_types=1);

namespace ApheleiaCli\Tests;

use ApheleiaCli\Command;
use ApheleiaCli\DefaultInvocationStrategy;
use PHPUnit\Framework\TestCase;

class DefaultInvocationStrategyTest extends TestCase
{
    public function testCall()
    {
        $count = 0;
        $callback = function () use (&$count) {
            $count++;
        };

        (new DefaultInvocationStrategy())->call($callback);

        $this->assertSame(1, $count);
    }

    public function testCallCommandHandler()
    {
        $count = 0;
        $receivedArgs = [];
        $receivedAssocArgs = [];

        $callback = function ($args, $assocArgs) use (&$count, &$receivedArgs, &$receivedAssocArgs) {
            $count++;
            $receivedArgs = $args;
            $receivedAssocArgs = $assocArgs;
        };

        $command = (new Command())
            ->setName('irrelevant')
            ->setHandler($callback);

        (new DefaultInvocationStrategy())
            ->callCommandHandler($command, ['args'], ['assoc' => 'args']);

        $this->assertSame(1, $count);
        $this->assertSame(['args'], $receivedArgs);
        $this->assertSame(['assoc' => 'args'], $receivedAssocArgs);
    }
}
