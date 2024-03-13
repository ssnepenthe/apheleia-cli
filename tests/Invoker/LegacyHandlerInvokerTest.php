<?php

declare(strict_types=1);

namespace ApheleiaCli\Tests\Invoker;

use ApheleiaCli\Argument;
use ApheleiaCli\Command;
use ApheleiaCli\Flag;
use ApheleiaCli\Input\WpCliInput;
use ApheleiaCli\Invoker\LegacyHandlerInvoker;
use ApheleiaCli\Option;
use ApheleiaCli\Output\ConsoleOutput;
use PHPUnit\Framework\TestCase;

class LegacyHandlerInvokerTest extends TestCase
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

        (new LegacyHandlerInvoker())
            ->invoke($command->getHandler(), new WpCliInput([], [], $command), new ConsoleOutput(), $command);

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
            ->addArgument(new Argument('arg-one'))
            ->addOption(new Option('opt-one'))
            ->addFlag(new Flag('flag-one'))
            ->setHandler($callback);

        $args = ['apple'];
        $assocArgs = ['opt-one' => 'banana', 'flag-one' => true];

        (new LegacyHandlerInvoker())->invoke(
            $command->getHandler(),
            new WpCliInput($args, $assocArgs, $command),
            new ConsoleOutput(),
            $command
        );

        $this->assertSame(1, $count);
        $this->assertSame($args, $receivedArgs);
        $this->assertSame($assocArgs, $receivedAssocArgs);
    }
}
