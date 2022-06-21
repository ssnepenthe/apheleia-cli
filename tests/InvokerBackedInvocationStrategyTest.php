<?php

declare(strict_types=1);

namespace ApheleiaCli\Tests;

use ApheleiaCli\Argument;
use ApheleiaCli\Command;
use ApheleiaCli\Flag;
use ApheleiaCli\InvokerBackedInvocationStrategy;
use ApheleiaCli\Option;
use PHPUnit\Framework\TestCase;

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
        $callback = function () use (&$count) {
            $count++;
        };
        $command = (new Command())
            ->setName('irrelevant')
            ->setHandler($callback);

        (new InvokerBackedInvocationStrategy())->callCommandHandler($command);

        $this->assertSame(1, $count);
    }

    public function testCallCommandHandlerWithContext()
    {
        $count = 0;
        $receivedArgs = [];

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
            $context
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
                'context',
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

        (new InvokerBackedInvocationStrategy())
            ->withContext($context = [
                'args' => $args,
                'assocArgs' => array_merge($assocArgs, $arbitraryOptions),
            ])
            ->callCommandHandler($command);

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

            // Full context array is also available
            'context' => $context,
        ], $receivedArgs);
    }

    public function testCallWithContext()
    {
        $count = 0;
        $receivedKey = '';
        $receivedContext = [];

        $callback = function ($key, $context) use (&$count, &$receivedKey, &$receivedContext) {
            $count++;
            $receivedKey = $key;
            $receivedContext = $context;
        };

        $context = ['key' => 'value'];

        (new InvokerBackedInvocationStrategy())
            ->withContext($context)
            ->call($callback);

        $this->assertSame(1, $count);
        $this->assertSame('value', $receivedKey);
        $this->assertSame($context, $receivedContext);
    }
}
