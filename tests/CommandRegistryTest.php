<?php

declare(strict_types=1);

namespace ApheleiaCli\Tests;

use ApheleiaCli\Argument;
use ApheleiaCli\Command;
use ApheleiaCli\CommandRegistry;
use ApheleiaCli\Flag;
use ApheleiaCli\NamespaceIdentifier;
use ApheleiaCli\Option;
use ApheleiaCli\WpCliAdapterInterface;
use Closure;
use PHPUnit\Framework\Constraint\IsType;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class CommandRegistryTest extends TestCase
{
    public function testAddDoesNotAllowMultipleCommandsWithSameName()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('command with this name already exists');

        $registry = new CommandRegistry();

        $registry->add((new Command())->setName('irrelevant'));
        $registry->add((new Command())->setName('irrelevant'));
    }

    public function testAddWithGroup()
    {
        $one = (new Command())->setName('one');
        $two = (new Command())->setName('two');
        $three = (new Command())->setName('three');
        $four = (new Command())->setName('four');

        $registry = new CommandRegistry();

        $registry->add($one);

        $registry->group(
            'first',
            'description',
            function (CommandRegistry $registry) use ($two, $three) {
                $registry->add($two);

                $registry->group(
                    'second',
                    'description',
                    function (CommandRegistry $registry) use ($three) {
                        $registry->add($three);
                    }
                );
            }
        );

        $this->assertSame('one', $one->getName());
        $this->assertSame('first two', $two->getName());
        $this->assertSame('first second three', $three->getName());
        $this->assertSame('four', $four->getName());
    }

    public function testCommand()
    {
        $registry = new CommandRegistry();
        $handler = function () {
        };

        $command = $registry->command('command', $handler)->getCommand();

        $this->assertSame('command', $command->getName());
        $this->assertSame($handler, $command->getHandler());
    }

    public function testCommandWithHandlerWithDefaults()
    {
        $registry = new CommandRegistry();

        $addition = $registry->command(
            'command [<arg>] [--opt=<opt>]',
            function ($arg = 'one', $opt = 'two') {
            }
        );
        $command = $addition->getCommand();

        $this->assertSame('one', $command->getArguments()['arg']->getDefault());
        $this->assertSame('two', $command->getOptions()['opt']->getDefault());
    }

    public function testCommandWithHandlerWithDefaultsAndCustomParameterNameMappers()
    {
        $registry = new CommandRegistry();
        $registry->setParameterNameMappers(fn ($name) => str_replace('one', '1', $name));

        $addition = $registry->command(
            'command [<1arg>] [--1opt=<1opt>]',
            function ($onearg = 'one', $oneopt = 'two') {
            }
        );
        $command = $addition->getCommand();

        $this->assertSame('one', $command->getArguments()['1arg']->getDefault());
        $this->assertSame('two', $command->getOptions()['1opt']->getDefault());
    }

    public function testGroupWithNoCommandsAndChildlessGroupsAllowed()
    {
        $registry = new CommandRegistry();
        $registry->allowChildlessGroups();

        $registry->group('group', 'description', function () {
        });

        $this->assertCount(1, $registry->getRegisteredCommands());
    }

    public function testGroupWithNoCommandsAndChildlessGroupsForbidden()
    {
        $registry = new CommandRegistry();

        $registry->group('group', 'description', function () {
        });

        $this->assertCount(0, $registry->getRegisteredCommands());
    }

    public function testInitialize()
    {
        $wpCliAdapterMock = $this->createMock(WpCliAdapterInterface::class);

        $wpCliAdapterMock
            ->expects($this->once())
            ->method('addWpHook')
            ->with('plugins_loaded', $this->isType(IsType::TYPE_CALLABLE));

        $registry = new CommandRegistry(null, null, $wpCliAdapterMock);

        $registry->initialize();
    }

    public function testInitializeImmediately()
    {
        $wpCliAdapterMock = $this->createMock(WpCliAdapterInterface::class);
        $wpCliAdapterMock
            ->expects($this->once())
            ->method('isWpCli')
            ->willReturn(true);
        $wpCliAdapterMock
            ->expects($this->once())
            ->method('addCommand')
            ->with('command', $this->isInstanceOf(Closure::class), [
                'synopsis' => [
                    [
                        'type' => 'positional',
                        'name' => 'arg',
                        'optional' => false,
                        'repeating' => false,
                    ],
                    [
                        'type' => 'assoc',
                        'name' => 'option',
                        'optional' => true,
                        'repeating' => false,
                    ],
                ],
            ]);

        $registry = new CommandRegistry(null, null, $wpCliAdapterMock);

        $registry->command('command <arg> [--option=<option>]', function () {
        });

        $registry->initializeImmediately();
    }

    public function testInitializeImmediatelyWithAllOptionsConfigured()
    {
        $wpCliAdapterMock = $this->createMock(WpCliAdapterInterface::class);
        $wpCliAdapterMock
            ->expects($this->once())
            ->method('isWpCli')
            ->willReturn(true);
        $wpCliAdapterMock
            ->expects($this->exactly(2))
            ->method('addCommand')
            ->withConsecutive(
                [
                    'group',
                    NamespaceIdentifier::class,
                    [
                        'shortdesc' => 'Group description',
                    ]
                ],
                [
                    'group command',
                    $this->isInstanceOf(Closure::class),
                    $this->callback(function ($subject) {
                        return is_array($subject)
                            && array_key_exists('shortdesc', $subject)
                            && 'A description string' === $subject['shortdesc']
                            && array_key_exists('synopsis', $subject)
                            && [
                                [
                                    'type' => 'positional',
                                    'name' => 'arg',
                                    'optional' => false,
                                    'repeating' => false,
                                ],
                                [
                                    'type' => 'flag',
                                    'name' => 'flag',
                                    'optional' => true,
                                    'repeating' => false,
                                ],
                                [
                                    'type' => 'assoc',
                                    'name' => 'opt',
                                    'optional' => true,
                                    'repeating' => false,
                                ],
                            ] === $subject['synopsis']
                            && array_key_exists('longdesc', $subject)
                            && 'A usage string' === $subject['longdesc']
                            && array_key_exists('before_invoke', $subject)
                            && $subject['before_invoke'] instanceof Closure
                            && array_key_exists('after_invoke', $subject)
                            && $subject['after_invoke'] instanceof Closure
                            && array_key_exists('when', $subject)
                            && 'when-string' === $subject['when'];
                    })
                ],
            );

        $registry = new CommandRegistry(null, null, $wpCliAdapterMock);

        $registry->group(
            'group',
            'Group description',
            function (CommandRegistry $registry) {
                $registry->add(
                    (new Command())
                        ->setName('command')
                        ->addArgument(new Argument('arg'))
                        ->addFlag(new Flag('flag'))
                        ->addOption(new Option('opt'))
                        ->setDescription('A description string')
                        ->setUsage('A usage string')
                        ->setBeforeInvokeCallback(function () {
                        })
                        ->setAfterInvokeCallback(function () {
                        })
                        ->setWhen('when-string')
                        ->setHandler(function () {
                        })
                );
            }
        );

        $registry->initializeImmediately();
    }

    public function testNamespace()
    {
        $registry = new CommandRegistry();

        $command = $registry->namespace('namespace', 'description');

        $this->assertCount(1, $registry->getRegisteredCommands());
        $this->assertSame('namespace', $command->getName());
        $this->assertSame('description', $command->getDescription());
    }

    public function testRemove()
    {
        $registry = new CommandRegistry();

        $registry->add($one = (new Command())->setName('one'));
        $registry->add($two = (new Command())->setName('two'));

        $registry->remove($two);

        $registeredCommands = $registry->getRegisteredCommands();

        $this->assertCount(1, $registeredCommands);
        $this->assertSame($one, current($registeredCommands));
    }

    public function testRemoveWhenCommandHasNotBeenRegistered()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('no command with this name has been registered');

        $registry = new CommandRegistry();

        $registry->remove((new Command())->setName('irrelevant'));
    }
}
