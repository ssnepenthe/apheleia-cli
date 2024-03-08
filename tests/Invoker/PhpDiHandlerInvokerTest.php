<?php

declare(strict_types=1);

namespace ApheleiaCli\Tests\Invoker;

use ApheleiaCli\Argument;
use ApheleiaCli\Command;
use ApheleiaCli\Flag;
use ApheleiaCli\Invoker\PhpDiHandlerInvoker;
use ApheleiaCli\Option;
use PHPUnit\Framework\TestCase;

// @todo InvokerInterface mocks?
class PhpDiHandlerInvokerTest extends TestCase
{
    use CreatesPhpDiInvoker;

    public function testInvoke()
    {
        $count = 0;
        $callback = function () use (&$count) {
            $count++;
        };
        $command = (new Command())
            ->setName('irrelevant')
            ->setHandler($callback);

        (new PhpDiHandlerInvoker($this->createPhpDiInvoker()))->invoke($command->getHandler(), [
            'args' => [],
            'assocArgs' => [],
            'command' => $command,
        ]);

        $this->assertSame(1, $count);
    }

    public function testInvokeWithArguments()
    {
        $count = 0;
        $receivedArgs = null;

        $callback = function (
            $args,
            $assocArgs,
            $arguments,
            $options,
            $argOne,
            $argTwo,
            $flagOne,
            $optOne,
            $arbitraryOptions,
            $command
        ) use (&$count, &$receivedArgs) {
            $count++;
            $receivedArgs = compact(
                'args',
                'assocArgs',
                'arguments',
                'options',
                'argOne',
                'argTwo',
                'flagOne',
                'optOne',
                'arbitraryOptions',
                'command'
            );
        };

        $command = (new Command())
            ->setName('irrelevant')
            ->addArgument(new Argument('arg-one'))
            ->addArgument(
                (new Argument('arg-two'))
                    ->setRepeating(true)
            )
            ->addFlag(new Flag('flag-one'))
            ->addOption(new Option('opt-one'))
            ->setAcceptArbitraryOptions(true)
            ->setHandler($callback);

        $args = ['apple', 'banana', 'cherry'];
        $assocArgs = [
            'flag-one' => true,
            'opt-one' => 'zebra',
        ];
        $arbitraryOptions = [
            'this' => 'goes',
            'to' => 'arbitrary-options',
        ];

        (new PhpDiHandlerInvoker($this->createPhpDiInvoker()))
            ->invoke($command->getHandler(), $context = [
                'args' => $args,
                'assocArgs' => array_merge($assocArgs, $arbitraryOptions),
                'command' => $command,
            ]);

        $this->assertSame(1, $count);

        $this->assertSame([
            'args' => $args,
            'assocArgs' => array_merge($assocArgs, $arbitraryOptions),
            'arguments' => $args,
            'options' => array_merge($assocArgs, $arbitraryOptions),
            'argOne' => $args[0],

            // If the last argument is repeating, all remaining arguments are bundled together
            'argTwo' => [$args[1], $args[2]],
            'flagOne' => true,

            'optOne' => $assocArgs['opt-one'],

            // If command accepts arbitrary options, remaining options are bundled together
            'arbitraryOptions' => $arbitraryOptions,

            // Command instance is also available.
            'command' => $command,
        ], $receivedArgs);
    }
}
