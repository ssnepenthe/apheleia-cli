<?php

declare(strict_types=1);

namespace ToyWpCli\Tests;

use PHPUnit\Framework\TestCase;
use ToyWpCli\Argument;
use ToyWpCli\Command;
use ToyWpCli\Flag;
use ToyWpCli\InvokerBackedInvocationStrategy;
use ToyWpCli\Option;

class InvokerBackedInvocationStrategyTest extends TestCase
{
    public function testCall()
    {
        $count = 0;
        $callback = function () use (&$count) {
            $count++;
        };

        (new InvokerBackedInvocationStrategy())->call($callback);

        $this->assertSame(1, $count);
    }

    public function testCallCommandHandler()
    {
        $count = 0;
        $receivedArgs = [];

        $callback = function (
            $args,
            $arguments,
            $assocArgs,
            $assoc_args,
            $opts,
            $options,
            $argOne,
            $arg_one,
            $argTwo,
            $arg_two,
            $flagOne,
            $flag_one,
            $flagTwo,
            $flag_two,
            $optOne,
            $opt_one,
            $arbitraryOptions,
            $arbitrary_options
        ) use (&$count, &$receivedArgs) {
            $count++;
            $receivedArgs = compact(
                'args',
                'arguments',
                'assocArgs',
                'assoc_args',
                'opts',
                'options',
                'argOne',
                'arg_one',
                'argTwo',
                'arg_two',
                'flagOne',
                'flag_one',
                'flagTwo',
                'flag_two',
                'optOne',
                'opt_one',
                'arbitraryOptions',
                'arbitrary_options',
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
            ->addFlag(new Flag('flag-two'))
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

        (new InvokerBackedInvocationStrategy())
            ->callCommandHandler($command, $args, [...$assocArgs, ...$arbitraryOptions]);

        $this->assertSame(1, $count);

        $this->assertSame([
            'args' => $args,
            'arguments' => $args,
            'assocArgs' => [...$assocArgs, ...$arbitraryOptions],
            'assoc_args' => [...$assocArgs, ...$arbitraryOptions],
            'opts' => [...$assocArgs, ...$arbitraryOptions],
            'options' => [...$assocArgs, ...$arbitraryOptions],
            'argOne' => $args[0],
            'arg_one' => $args[0],

            // If the last argument is repeating, all remaining arguments are bundled together
            'argTwo' => [$args[1], $args[2]],
            'arg_two' => [$args[1], $args[2]],
            'flagOne' => true,
            'flag_one' => true,

            // Flags that aren't explicitly set default to false
            'flagTwo' => false,
            'flag_two' => false,
            'optOne' => $assocArgs['opt-one'],
            'opt_one' => $assocArgs['opt-one'],

            // If command accepts arbitrary options, remaining options are bundled together
            'arbitraryOptions' => $arbitraryOptions,
            'arbitrary_options' => $arbitraryOptions,
        ], $receivedArgs);
    }
}
