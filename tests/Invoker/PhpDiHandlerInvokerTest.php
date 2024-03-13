<?php

declare(strict_types=1);

namespace ApheleiaCli\Tests\Invoker;

use ApheleiaCli\Argument;
use ApheleiaCli\Command;
use ApheleiaCli\Flag;
use ApheleiaCli\Input\ArrayInput;
use ApheleiaCli\Input\InputInterface;
use ApheleiaCli\Input\WpCliInput;
use ApheleiaCli\Invoker\PhpDiHandlerInvoker;
use ApheleiaCli\Option;
use ApheleiaCli\Output\ConsoleOutputInterface;
use ApheleiaCli\Output\OutputInterface;
use ApheleiaCli\Output\TestConsoleOutput;
use PHPUnit\Framework\TestCase;

class PhpDiHandlerInvokerTest extends TestCase
{
    use CreatesPhpDiInvoker;

    public function testInvokeArgsAndAssocArgs()
    {
        $receivedArgs = $receivedAssocArgs = [];

        $handler = function ($args, $assocArgs) use (&$receivedArgs, &$receivedAssocArgs) {
            $receivedArgs = $args;
            $receivedAssocArgs = $assocArgs;
        };
        $command = (new Command())
            ->setName('irrelevant')
            ->addArgument(new Argument('arg-one'))
            ->addOption(new Option('opt-one'))
            ->addFlag(new Flag('flag-one'))
            ->setHandler($handler);

        $args = ['apple'];
        $assocArgs = ['opt-one' => 'banana', 'flag-one' => true];

        $this->createHandlerInvoker()
            ->invoke(
                $command->getHandler(),
                new WpCliInput($args, $assocArgs, $command),
                new TestConsoleOutput(),
                $command
            );

        $this->assertSame($args, $receivedArgs);
        $this->assertSame($assocArgs, $receivedAssocArgs);
    }

    public function testInvokeArgumentsOptionsAndFlags()
    {
        $receivedArguments = $receivedOptions = $receivedFlags = [];

        $handler = function ($arguments, $options, $flags) use (&$receivedArguments, &$receivedOptions, &$receivedFlags) {
            $receivedArguments = $arguments;
            $receivedOptions = $options;
            $receivedFlags = $flags;
        };
        $command = (new Command())
            ->setName('irrelevant')
            ->setHandler($handler);

        $arguments = ['apple' => 'banana'];
        $options = ['cherry' => 'date'];
        $flags = ['elderberry' => true];

        $this->createHandlerInvoker()
            ->invoke(
                $command->getHandler(),
                new ArrayInput($arguments, $options, $flags),
                new TestConsoleOutput(),
                $command
            );

        $this->assertSame($arguments, $receivedArguments);
        $this->assertSame($options, $receivedOptions);
        $this->assertSame($flags, $receivedFlags);
    }

    public function testInvokeCommand()
    {
        $receivedCommand = $receivedTypedCommand = null;

        $handler = function ($command, Command $typedCommand) use (&$receivedCommand, &$receivedTypedCommand) {
            $receivedCommand = $command;
            $receivedTypedCommand = $typedCommand;
        };
        $command = (new Command())
            ->setName('irrelevant')
            ->setHandler($handler);

        $this->createHandlerInvoker()
            ->invoke(
                $command->getHandler(),
                new ArrayInput([], [], []),
                new TestConsoleOutput(),
                $command
            );

        $this->assertSame($command, $receivedCommand);
        $this->assertSame($command, $receivedTypedCommand);
    }

    public function testInvokeInput()
    {
        $receivedInput = $receivedTypedInput = $receivedTypedWpCliInput = null;

        $handler = function ($input, InputInterface $typedInput, WpCliInput $typedWpCliInput) use (&$receivedInput, &$receivedTypedInput, &$receivedTypedWpCliInput) {
            $receivedInput = $input;
            $receivedTypedInput = $typedInput;
            $receivedTypedWpCliInput = $typedWpCliInput;
        };
        $command = (new Command())
            ->setName('irrelevant')
            ->setHandler($handler);
        $input = new WpCliInput([], [], $command);

        $this->createHandlerInvoker()
            ->invoke($command->getHandler(), $input, new TestConsoleOutput(), $command);

        $this->assertSame($input, $receivedInput);
        $this->assertSame($input, $receivedTypedInput);
        $this->assertSame($input, $receivedTypedWpCliInput);
    }

    public function testInvokeOutput()
    {
        $receivedOutput = $receivedTypedOutput = $receivedTypedConsoleOutput = null;

        $handler = function ($output, OutputInterface $typedOutput, ConsoleOutputInterface $typedConsoleOutput) use (&$receivedOutput, &$receivedTypedOutput, &$receivedTypedConsoleOutput) {
            $receivedOutput = $output;
            $receivedTypedOutput = $typedOutput;
            $receivedTypedConsoleOutput = $typedConsoleOutput;
        };
        $command = (new Command())
            ->setName('irrelevant')
            ->setHandler($handler);
        $output = new TestConsoleOutput();

        $this->createHandlerInvoker()
            ->invoke($command->getHandler(), new ArrayInput([], [], []), $output, $command);

        $this->assertSame($output, $receivedOutput);
        $this->assertSame($output, $receivedTypedOutput);
        $this->assertSame($output, $receivedTypedConsoleOutput);
    }

    private function createCommand($handler): Command
    {
        return (new Command())
            ->setName('irrelevant')
            ->addArgument(new Argument('arg-one'))
            ->addArgument(
                (new Argument('arg-two'))
                    ->setRepeating(true)
            )
            ->addFlag(new Flag('flag-one'))
            ->addOption(new Option('opt-one'))
            ->setAcceptArbitraryOptions(true)
            ->setHandler($handler);
    }

    private function createHandlerInvoker(): PhpDiHandlerInvoker
    {
        return new PhpDiHandlerInvoker($this->createPhpDiInvoker());
    }
}
