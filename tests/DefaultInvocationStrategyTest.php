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
        $receivedArgs = $receivedAssocArgs = $receivedContext = null;

        $callback = function ($args, $assocArgs, $context) use (&$count, &$receivedArgs, &$receivedAssocArgs, &$receivedContext) {
            $count++;
            $receivedArgs = $args;
            $receivedAssocArgs = $assocArgs;
            $receivedContext = $context;
        };

        $command = (new Command())
            ->setName('irrelevant')
            ->setHandler($callback);

        (new DefaultInvocationStrategy())->callCommandHandler($command);

        $this->assertSame(1, $count);
        $this->assertSame([], $receivedArgs);
        $this->assertSame([], $receivedAssocArgs);
        $this->assertSame([], $receivedContext);
    }

    public function testCallCommandHandlerWithContext()
    {
        $count = 0;
        $receivedArgs = $receivedAssocArgs = $receivedContext = null;

        $callback = function ($args, $assocArgs, $context) use (&$count, &$receivedArgs, &$receivedAssocArgs, &$receivedContext) {
            $count++;
            $receivedArgs = $args;
            $receivedAssocArgs = $assocArgs;
            $receivedContext = $context;
        };

        $command = (new Command())
            ->setName('irrelevant')
            ->setHandler($callback);

        $args = ['args'];
        $assocArgs = ['assoc' => 'args'];
        $context = compact('args', 'assocArgs');

        (new DefaultInvocationStrategy())
            ->callCommandHandler($command, $context);

        $this->assertSame(1, $count);
        $this->assertSame($args, $receivedArgs);
        $this->assertSame($assocArgs, $receivedAssocArgs);
        $this->assertSame($context, $receivedContext);
    }

    public function testCallWithContext()
    {
        $count = 0;
        $receivedContext = [];
        $callback = function ($context) use (&$count, &$receivedContext) {
            $count++;
            $receivedContext = $context;
        };

        (new DefaultInvocationStrategy())
            ->call($callback, $context = ['some' => 'context']);

        $this->assertSame(1, $count);
        $this->assertSame($context, $receivedContext);
    }
}
