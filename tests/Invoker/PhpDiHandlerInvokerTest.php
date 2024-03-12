<?php

declare(strict_types=1);

namespace ApheleiaCli\Tests\Invoker;

use ApheleiaCli\Argument;
use ApheleiaCli\Command;
use ApheleiaCli\Flag;
use ApheleiaCli\Input\ArrayInput;
use ApheleiaCli\Input\InputInterface;
use ApheleiaCli\Invoker\PhpDiHandlerInvoker;
use ApheleiaCli\Option;
use ApheleiaCli\Output\ConsoleOutput;
use ApheleiaCli\Output\ConsoleOutputInterface;
use ApheleiaCli\Output\OutputInterface;
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

        (new PhpDiHandlerInvoker($this->createPhpDiInvoker()))
            ->invoke($command->getHandler(), new ArrayInput([], [], []), new ConsoleOutput(), $command);

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
            $flags,
            $command,
            Command $typedCommand,
            $input,
            InputInterface $typedInput,
            $output,
            ConsoleOutputInterface $typedConsoleOutput,
            OutputInterface $typedOutput,
            $argOne,
            $argTwo,
            $flagOne,
            $optOne,
            $arbitraryOptions,
        ) use (&$count, &$receivedArgs) {
            $count++;
            $receivedArgs = compact(
                'args',
                'assocArgs',
                'arguments',
                'options',
                'flags',
                'command',
                'typedCommand',
                'input',
                'typedInput',
                'output',
                'typedConsoleOutput',
                'typedOutput',
                'argOne',
                'argTwo',
                'flagOne',
                'optOne',
                'arbitraryOptions',
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

        $arguments = ['apple', 'banana', 'cherry'];
        $options = ['opt-one' => 'zebra'];
        $flags = ['flag-one' => true];
        $arbitraryOptions = [
            'this' => 'goes',
            'to' => 'arbitrary-options',
        ];

        (new PhpDiHandlerInvoker($this->createPhpDiInvoker()))->invoke(
            $command->getHandler(),
            $input = new ArrayInput($arguments, array_merge($options, $arbitraryOptions), $flags),
            $output = new ConsoleOutput(),
            $command
        );

        $this->assertSame(1, $count);

        $this->assertSame([
            'args' => $arguments,
            'assocArgs' => array_merge($options, $arbitraryOptions, $flags),

            'arguments' => $arguments,
            'options' => array_merge($options, $arbitraryOptions),
            'flags' => $flags,

            'command' => $command,
            'typedCommand' => $command,

            'input' => $input,
            'typedInput' => $input,

            'output' => $output,
            'typedConsoleOutput' => $output,
            'typedOutput' => $output,

            'argOne' => $arguments[0],

            // If the last argument is repeating, all remaining arguments are bundled together
            'argTwo' => [$arguments[1], $arguments[2]],

            'flagOne' => true,

            'optOne' => $options['opt-one'],

            // If command accepts arbitrary options, remaining options are bundled together
            'arbitraryOptions' => $arbitraryOptions,
        ], $receivedArgs);
    }
}
