<?php

declare(strict_types=1);

namespace ApheleiaCli\Tests\Invoker;

use ApheleiaCli\Command;
use ApheleiaCli\Invoker\DefaultHandlerInvoker;
use PHPUnit\Framework\TestCase;

class DefaultHandlerInvokerTest extends TestCase
{
    public function testInvoke()
    {
        $count = 0;
        $receivedArgs = $receivedAssocArgs = null;

        $callback = function ($args, $assocArgs) use (&$count, &$receivedArgs, &$receivedAssocArgs) {
            $count++;
            $receivedArgs = $args;
            $receivedAssocArgs = $assocArgs;
        };

        $command = (new Command())
            ->setName('irrelevant')
            ->setHandler($callback);

        (new DefaultHandlerInvoker())->invoke($command->getHandler());

        $this->assertSame(1, $count);
        $this->assertSame([], $receivedArgs);
        $this->assertSame([], $receivedAssocArgs);
    }

    public function testInvokeWithArguments()
    {
        $count = 0;
        $receivedArgs = $receivedAssocArgs = null;

        $callback = function ($args, $assocArgs) use (&$count, &$receivedArgs, &$receivedAssocArgs) {
            $count++;
            $receivedArgs = $args;
            $receivedAssocArgs = $assocArgs;
        };

        $command = (new Command())
            ->setName('irrelevant')
            ->setHandler($callback);

        $args = ['args'];
        $assocArgs = ['assoc' => 'args'];
        $arguments = compact('args', 'assocArgs');

        (new DefaultHandlerInvoker())
            ->invoke($command->getHandler(), $arguments);

        $this->assertSame(1, $count);
        $this->assertSame($args, $receivedArgs);
        $this->assertSame($assocArgs, $receivedAssocArgs);
    }
}
