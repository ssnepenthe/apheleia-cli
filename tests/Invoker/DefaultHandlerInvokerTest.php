<?php

declare(strict_types=1);

namespace ApheleiaCli\Tests\Invoker;

use ApheleiaCli\Command;
use ApheleiaCli\Input\ArrayInput;
use ApheleiaCli\Invoker\DefaultHandlerInvoker;
use ApheleiaCli\Output\ConsoleOutput;
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

        (new DefaultHandlerInvoker())
            ->invoke($command->getHandler(), new ArrayInput([], [], []), new ConsoleOutput(), $command);

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

        $arguments = ['args'];
        $options = ['options' => 'values'];
        $flags = ['flags' => true];

        (new DefaultHandlerInvoker())->invoke(
            $command->getHandler(),
            new ArrayInput($arguments, $options, $flags),
            new ConsoleOutput(),
            $command
        );

        $this->assertSame(1, $count);
        $this->assertSame($arguments, $receivedArgs);
        $this->assertSame(array_merge($options, $flags), $receivedAssocArgs);
    }
}
