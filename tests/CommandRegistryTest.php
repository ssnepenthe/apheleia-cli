<?php

declare(strict_types=1);

namespace ToyWpCli\Tests;

use Closure;
use PHPUnit\Framework\TestCase;
use ToyWpCli\Argument;
use ToyWpCli\Command;
use ToyWpCli\CommandRegistry;
use ToyWpCli\Flag;
use ToyWpCli\NamespaceIdentifier;
use ToyWpCli\Option;
use ToyWpCli\WpCliAdapterInterface;

class CommandRegistryTest extends TestCase
{
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
        $wpCliAdapterMock = $this->createMock(WpCliAdapterInterface::class);
        $wpCliAdapterMock
            ->expects($this->once())
            ->method('addCommand')
            ->with('namespace', NamespaceIdentifier::class, [
                'shortdesc' => 'description',
            ]);

        $registry = new CommandRegistry(null, null, $wpCliAdapterMock);
        $registry->allowChildlessNamespaces();

        $registry->namespace('namespace', 'description', function () {
        });

        $registry->initializeImmediately();
    }

    public function testNamespaceWithNoCommandsAndChildlessNamespacesForbidden()
    {
        $wpCliAdapterMock = $this->createMock(WpCliAdapterInterface::class);
        $wpCliAdapterMock
            ->expects($this->never())
            ->method('addCommand');

        $registry = new CommandRegistry(null, null, $wpCliAdapterMock);

        $registry->namespace('namespace', 'description', function () {
        });

        $registry->initializeImmediately();
    }
}
