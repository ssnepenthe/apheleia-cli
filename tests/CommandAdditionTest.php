<?php

declare(strict_types=1);

namespace ApheleiaCli\Tests;

use ApheleiaCli\Argument;
use ApheleiaCli\Command;
use ApheleiaCli\CommandAddition;
use ApheleiaCli\Invoker\InvokerFactory;
use ApheleiaCli\NamespaceCommand;
use ApheleiaCli\NamespaceIdentifier;
use ApheleiaCli\NullWpCliAdapter;
use ApheleiaCli\Option;
use ApheleiaCli\WpCli\TestConfig;
use ApheleiaCli\WpCliAdapterInterface;
use Closure;
use PHPUnit\Framework\TestCase;

class CommandAdditionTest extends TestCase
{
    public function testGetArgs()
    {
        $command = new Command();
        $addition = new CommandAddition($command, new InvokerFactory(), new NullWpCliAdapter(), new TestConfig());

        $this->assertSame([], $addition->getArgs());

        $command = (new Command())
            ->setDescription('irrelevant description')
            ->addArgument(new Argument('irrelevant-arg'))
            ->addOption(new Option('irrelevant-opt'))
            ->setUsage('irrelevant usage')
            ->setBeforeInvokeCallback(fn () => 'irrelevant')
            ->setAfterInvokeCallback(fn () => 'irrelevant')
            ->setWhen('irrelevant-when');
        $addition = new CommandAddition($command, new InvokerFactory(), new NullWpCliAdapter(), new TestConfig());

        $args = $addition->getArgs();
        $basicArgs = array_intersect_key($args, [
            'shortdesc' => true,
            'synopsis' => true,
            'longdesc' => true,
            'when' => true,
        ]);

        $this->assertSame([
            'shortdesc' => 'irrelevant description',
            'synopsis' => [
                [
                    'type' => 'positional',
                    'name' => 'irrelevant-arg',
                    'optional' => false,
                    'repeating' => false,
                ],
                [
                    'type' => 'assoc',
                    'name' => 'irrelevant-opt',
                    'optional' => true,
                    'repeating' => false,
                ],
            ],
            'longdesc' => 'irrelevant usage',
            'when' => 'irrelevant-when',
        ], $basicArgs);

        $this->assertArrayHasKey('before_invoke', $args);
        $this->assertInstanceOf(Closure::class, $args['before_invoke']);
        $this->assertArrayHasKey('after_invoke', $args);
        $this->assertInstanceOf(Closure::class, $args['after_invoke']);
    }

    public function testGetHandler()
    {
        $command = new NamespaceCommand('name', 'description');
        $addition = new CommandAddition($command, new InvokerFactory(), new NullWpCliAdapter(), new TestConfig());

        $this->assertSame(NamespaceIdentifier::class, $addition->getHandler());

        $handler = fn () => 'irrelevant';
        $command = (new Command())->setHandler($handler);
        $addition = new CommandAddition($command, new InvokerFactory(), new NullWpCliAdapter(), new TestConfig());

        $this->assertInstanceOf(Closure::class, $addition->getHandler());
        $this->assertNotSame($handler, $addition->getHandler());
    }

    public function testGetName()
    {
        $command = (new Command())->setName('irrelevant');
        $addition = new CommandAddition($command, new InvokerFactory(), new NullWpCliAdapter(), new TestConfig());

        $this->assertSame('irrelevant', $addition->getName());
    }

    public function testHandle()
    {
        $command = (new Command())->setHandler(fn () => 0);

        $wpCliAdapter = $this->createMock(WpCliAdapterInterface::class);
        $wpCliAdapter->expects($this->once())
            ->method('halt')
            ->with(0);

        $addition = new CommandAddition($command, new InvokerFactory(), $wpCliAdapter, new TestConfig());

        // This is the default but let's be explicit...
        $addition->setAutoExit(true);

        ($addition->getHandler())([], []);
    }

    public function testHandleWithAutoExitDisabled()
    {
        $wpCliAdapter = $this->createMock(WpCliAdapterInterface::class);
        $wpCliAdapter->expects($this->never())
            ->method('halt');

        $command = (new Command())->setHandler(fn () => 0);
        $addition = new CommandAddition($command, new InvokerFactory(), $wpCliAdapter, new TestConfig());
        $addition->setAutoExit(false);

        $return = ($addition->getHandler())([], []);

        $this->assertSame(0, $return);
    }

    public function testHandleWithNonIntReturnValue()
    {
        $command = (new Command())->setHandler(fn () => 'stringval');

        $addition = new CommandAddition($command, new InvokerFactory(), new NullWpCliAdapter(), new TestConfig());
        $addition->setAutoExit(false);

        $return = ($addition->getHandler())([], []);

        // Assume zero exit status when handler return non-int value.
        $this->assertSame(0, $return);
    }

    public function testHandleWithReturnValueOverMax()
    {
        $command = (new Command())->setHandler(fn () => 256);

        $addition = new CommandAddition($command, new InvokerFactory(), new NullWpCliAdapter(), new TestConfig());
        $addition->setAutoExit(false);

        $return = ($addition->getHandler())([], []);

        // Exit status allowed max is 255.
        $this->assertSame(255, $return);
    }

    public function testHandleWithReturnValueUnderMin()
    {
        $command = (new Command())->setHandler(fn () => 'irrelevant');

        $addition = new CommandAddition($command, new InvokerFactory(), new NullWpCliAdapter(), new TestConfig());
        $addition->setAutoExit(false);

        $return = ($addition->getHandler())([], []);

        // Exit status allowed min is 0.
        $this->assertSame(0, $return);
    }
}
