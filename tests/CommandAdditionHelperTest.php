<?php

declare(strict_types=1);

namespace ApheleiaCli\Tests;

use ApheleiaCli\Argument;
use ApheleiaCli\Command;
use ApheleiaCli\CommandAdditionHelper;
use ApheleiaCli\Flag;
use ApheleiaCli\Option;
use Closure;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class CommandAdditionHelperTest extends TestCase
{
    public function testAfter()
    {
        $command = $this->createCommand();
        $helper = new CommandAdditionHelper($command);

        $helper->after(function () {
        });

        $this->assertInstanceOf(Closure::class, $command->getAfterInvokeCallback());
    }

    public function testBefore()
    {
        $command = $this->createCommand();
        $helper = new CommandAdditionHelper($command);

        $helper->before(function () {
        });

        $this->assertInstanceOf(Closure::class, $command->getBeforeInvokeCallback());
    }

    public function testDefaults()
    {
        $command = $this->createCommand()
            ->addArgument(new Argument('irrelevant-arg'))
            ->addOption(new Option('irrelevant-opt'));

        $helper = new CommandAdditionHelper($command);

        $helper->defaults([
            'irrelevant-arg' => 'arg-default',
            '--irrelevant-opt' => 'opt-default',
        ]);

        $this->assertSame(
            'arg-default',
            $this->findCommandArgument($command, 'argument', 'irrelevant-arg')->getDefault()
        );
        $this->assertSame(
            'opt-default',
            $this->findCommandArgument($command, 'option', 'irrelevant-opt')->getDefault()
        );
    }

    public function testDefaultsWithFlag()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot set default for flag \'--irrelevant-flag\'');

        $command = $this->createCommand()
            ->addFlag(new Flag('irrelevant-flag'));

        $helper = new CommandAdditionHelper($command);

        $helper->defaults([
            '--irrelevant-flag' => 'irrelevant-default',
        ]);
    }

    public function testDefaultsWithInvalidArgument()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Cannot set default for unregistered parameter \'unregistered-arg\''
        );

        $command = $this->createCommand();

        $helper = new CommandAdditionHelper($command);

        $helper->defaults([
            'unregistered-arg' => 'arg-default',
        ]);
    }

    public function testDefaultsWithInvalidOption()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Cannot set default for unregistered parameter \'--unregistered-opt\''
        );

        $command = $this->createCommand();

        $helper = new CommandAdditionHelper($command);

        $helper->defaults([
            '--unregistered-opt' => 'opt-default',
        ]);
    }

    public function testDescriptions()
    {
        $command = $this->createCommand()
            ->addArgument(new Argument('irrelevant-arg'))
            ->addOption(new Option('irrelevant-opt'))
            ->addFlag(new Flag('irrelevant-flag'));

        $helper = new CommandAdditionHelper($command);

        $helper->descriptions('Command description', [
            'irrelevant-arg' => 'Argument description',
            '--irrelevant-opt' => 'Option description',
            '--irrelevant-flag' => 'Flag description',
        ]);

        $this->assertSame('Command description', $command->getDescription());

        $this->assertSame(
            'Argument description',
            $this->findCommandArgument($command, 'argument', 'irrelevant-arg')->getDescription()
        );
        $this->assertSame(
            'Option description',
            $this->findCommandArgument($command, 'option', 'irrelevant-opt')->getDescription()
        );
        $this->assertSame(
            'Flag description',
            $this->findCommandArgument($command, 'flag', 'irrelevant-flag')->getDescription()
        );
    }

    public function testDescriptionsWithInvalidArgument()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Cannot set description for unregistered parameter \'unregistered-arg\''
        );

        $command = $this->createCommand();

        $helper = new CommandAdditionHelper($command);

        $helper->descriptions('Irrelevant command description', [
            'unregistered-arg' => 'Irrelevant argument description',
        ]);
    }

    public function testDescriptionsWithInvalidFlag()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Cannot set description for unregistered parameter \'--unregistered-flag\''
        );

        $command = $this->createCommand();

        $helper = new CommandAdditionHelper($command);

        $helper->descriptions('Irrelevant command description', [
            '--unregistered-flag' => 'Irrelevant flag description',
        ]);
    }

    public function testDescriptionsWithInvalidOption()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Cannot set description for unregistered parameter \'--unregistered-opt\''
        );

        $command = $this->createCommand();

        $helper = new CommandAdditionHelper($command);

        $helper->descriptions('Irrelevant command description', [
            '--unregistered-opt' => 'Irrelevant option description',
        ]);
    }

    public function testOptions()
    {
        $command = $this->createCommand()
            ->addArgument(new Argument('irrelevant-arg'))
            ->addOption(new Option('irrelevant-opt'));

        $helper = new CommandAdditionHelper($command);

        $helper->options([
            'irrelevant-arg' => ['arg-one', 'arg-two'],
            '--irrelevant-opt' => ['opt-one', 'opt-two'],
        ]);

        $this->assertSame(
            ['arg-one', 'arg-two'],
            $this->findCommandArgument($command, 'argument', 'irrelevant-arg')->getOptions()
        );
        $this->assertSame(
            ['opt-one', 'opt-two'],
            $this->findCommandArgument($command, 'option', 'irrelevant-opt')->getOptions()
        );
    }

    public function testOptionsWithFlag()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot set options for flag \'--irrelevant-flag\'');

        $command = $this->createCommand()
            ->addFlag(new Flag('irrelevant-flag'));

        $helper = new CommandAdditionHelper($command);

        $helper->options([
            '--irrelevant-flag' => [],
        ]);
    }

    public function testOptionsWithInvalidArgument()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Cannot set options for unregistered parameter \'unregistered-arg\''
        );

        $command = $this->createCommand();

        $helper = new CommandAdditionHelper($command);

        $helper->options([
            'unregistered-arg' => ['arg-one', 'arg-two'],
        ]);
    }

    public function testOptionsWithInvalidOption()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Cannot set options for unregistered parameter \'--unregistered-opt\''
        );

        $command = $this->createCommand();

        $helper = new CommandAdditionHelper($command);

        $helper->options([
            '--unregistered-opt' => ['opt-one', 'opt-two'],
        ]);
    }

    public function testOptionsWithInvalidType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Parameter options must be specified as an array of strings');

        $command = $this->createCommand();

        $helper = new CommandAdditionHelper($command);

        $helper->options([
            'irrelevant-arg' => 'this-should-be-an-array',
        ]);
    }

    public function testUsage()
    {
        $command = $this->createCommand();

        $helper = new CommandAdditionHelper($command);

        $helper->usage('Irrelevant usage string');

        $this->assertSame('Irrelevant usage string', $command->getUsage());
    }

    public function testWhen()
    {
        $command = $this->createCommand();

        $helper = new CommandAdditionHelper($command);

        $helper->when('irrelevant-when-string');

        $this->assertSame('irrelevant-when-string', $command->getWhen());
    }

    private function createCommand()
    {
        return (new Command())->setName('irrelevant');
    }

    private function findCommandArgument($command, $argType, $argName)
    {
        if ('argument' === $argType) {
            $method = 'getArguments';
        } else {
            $method = 'getOptions';
        }

        $arguments = $command->{$method}();

        if (! array_key_exists($argName, $arguments)) {
            throw new RuntimeException(
                "Unable to find argument of type {$argType} with name {$argName}"
            );
        }

        return $arguments[$argName];
    }
}
