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

    public function testAddWithNamespace()
    {
        $one = (new Command())->setName('one');
        $two = (new Command())->setName('two');

        $registry = new CommandRegistry();

        $registry->namespace(
            'ns-one',
            'description',
            function (CommandRegistry $registry) use ($one, $two) {
                $registry->add($one);

                $registry->namespace(
                    'ns-two',
                    'description',
                    function (CommandRegistry $registry) use ($two) {
                        $registry->add($two);
                    }
                );
            }
        );

        $this->assertSame('ns-one one', $one->getName());
        $this->assertSame('ns-one ns-two two', $two->getName());
    }

    public function testCommand()
    {
        $registry = new CommandRegistry();
        $handler = function () {
        };

        $command = $registry->command('command', $handler)->getCommand();

        $this->assertSame($handler, $command->getHandler());
    }

    public function testInitialize()
    {
        $wpCliAdapterMock = $this->createMock(WpCliAdapterInterface::class);

        $wpCliAdapterMock
            ->expects($this->once())
            ->method('addWpHook')
            ->with('plugins_loaded', $this->isInstanceOf(Closure::class));

        $registry = new CommandRegistry(null, null, $wpCliAdapterMock);

        $registry->initialize();
    }

    public function testInitializeImmediately()
    {
        $wpCliAdapterMock = $this->createMock(WpCliAdapterInterface::class);
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
            ->expects($this->exactly(2))
            ->method('addCommand')
            ->withConsecutive(
                [
                    'namespace',
                    NamespaceIdentifier::class,
                    [
                        'shortdesc' => 'Namespace description',
                    ]
                ],
                [
                    'namespace command',
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

        $registry->namespace(
            'namespace',
            'Namespace description',
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

    public function testNamespaceWithNoCommandsAndChildlessNamespacesAllowed()
    {
        $registry = new CommandRegistry();
        $registry->allowChildlessNamespaces();

        $registry->namespace('namespace', 'description', function () {
        });

        $this->assertCount(1, $registry->getRegisteredCommands());
    }

    public function testNamespaceWithNoCommandsAndChildlessNamespacesForbidden()
    {
        $registry = new CommandRegistry();

        $registry->namespace('namespace', 'description', function () {
        });

        $this->assertCount(0, $registry->getRegisteredCommands());
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
