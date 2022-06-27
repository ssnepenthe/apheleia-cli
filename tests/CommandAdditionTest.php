<?php

declare(strict_types=1);

namespace ApheleiaCli\Tests;

use ApheleiaCli\Argument;
use ApheleiaCli\Command;
use ApheleiaCli\CommandAddition;
use ApheleiaCli\InvocationStrategyInterface;
use ApheleiaCli\NamespaceIdentifier;
use ApheleiaCli\Option;
use ApheleiaCli\WpCliAdapterInterface;
use Closure;
use PHPUnit\Framework\Constraint\IsType;
use PHPUnit\Framework\TestCase;

class CommandAdditionTest extends TestCase
{
    protected $invocationStrategy;
    protected $wpCliAdapter;

    protected function setUp(): void
    {
        $this->invocationStrategy = $this->createStub(InvocationStrategyInterface::class);
        $this->invocationStrategy->method('withContext')->willReturn($this->invocationStrategy);

        $this->wpCliAdapter = $this->createStub(WpCliAdapterInterface::class);
    }

    protected function tearDown(): void
    {
        $this->invocationStrategy = null;
        $this->wpCliAdapter = null;
    }

    public function testGetArgs()
    {
        $command = new Command();
        $addition = $this->makeCommandAddition($command);

        $this->assertSame([], $addition->getArgs());

        $command = (new Command())
            ->setDescription('irrelevant description')
            ->addArgument(new Argument('irrelevant-arg'))
            ->addOption(new Option('irrelevant-opt'))
            ->setUsage('irrelevant usage')
            ->setBeforeInvokeCallback(fn () => 'irrelevant')
            ->setAfterInvokeCallback(fn () => 'irrelevant')
            ->setWhen('irrelevant-when');
        $addition = $this->makeCommandAddition($command);

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
        $command = (new Command())->setHandler(NamespaceIdentifier::class);
        $addition = $this->makeCommandAddition($command);

        $this->assertSame(NamespaceIdentifier::class, $addition->getHandler());

        $handler = fn () => 'irrelevant';
        $command = (new Command())->setHandler($handler);
        $addition = $this->makeCommandAddition($command);

        $this->assertInstanceOf(Closure::class, $addition->getHandler());
        $this->assertNotSame($handler, $addition->getHandler());
    }

    public function testGetName()
    {
        $command = (new Command())->setName('irrelevant');
        $addition = $this->makeCommandAddition($command);

        $this->assertSame('irrelevant', $addition->getName());
    }

    public function testHandle()
    {
        $command = (new Command())->setHandler(fn () => 'irrelevant');
        $args = ['one'];
        $assocArgs = ['two' => 'three'];

        $invocationStrategyClone = $this->createMock(InvocationStrategyInterface::class);
        $invocationStrategyClone->expects($this->once())
            ->method('callCommandHandler')
            ->with($this->identicalTo($command))
            ->willReturn(0);

        $invocationStrategy = $this->createMock(InvocationStrategyInterface::class);
        $invocationStrategy->expects($this->once())
            ->method('withContext')
            ->with($this->identicalTo(compact('args', 'assocArgs')))
            ->willReturn($invocationStrategyClone);

        $wpCliAdapter = $this->createMock(WpCliAdapterInterface::class);
        $wpCliAdapter->expects($this->once())
            ->method('halt')
            ->with($this->isType(IsType::TYPE_INT));

        $addition = new CommandAddition($command, $invocationStrategy, $wpCliAdapter);

        ($addition->getHandler())($args, $assocArgs);
    }

    public function testHandleWithAutoExitDisabled()
    {
        $this->invocationStrategy->method('callCommandHandler')->willReturn(0);

        $wpCliAdapter = $this->createMock(WpCliAdapterInterface::class);
        $wpCliAdapter->expects($this->never())
            ->method('halt');

        $command = (new Command())->setHandler(fn () => 'irrelevant');
        $addition = new CommandAddition($command, $this->invocationStrategy, $wpCliAdapter);
        $addition->setAutoExit(false);

        $return = ($addition->getHandler())([], []);

        $this->assertSame(0, $return);
    }

    public function testHandleWithNonIntReturnValue()
    {
        $this->invocationStrategy->method('callCommandHandler')->willReturn(null);

        $command = (new Command())->setHandler(fn () => 'irrelevant');
        $addition = $this->makeCommandAddition($command);
        $addition->setAutoExit(false);

        $return = ($addition->getHandler())([], []);

        $this->assertSame(0, $return);
    }

    public function testHandleWithReturnValueOverMax()
    {
        $this->invocationStrategy->method('callCommandHandler')->willReturn(256);

        $command = (new Command())->setHandler(fn () => 'irrelevant');
        $addition = $this->makeCommandAddition($command);
        $addition->setAutoExit(false);

        $return = ($addition->getHandler())([], []);

        $this->assertSame(255, $return);
    }

    public function testHandleWithReturnValueUnderMin()
    {
        $this->invocationStrategy->method('callCommandHandler')->willReturn(-1);

        $command = (new Command())->setHandler(fn () => 'irrelevant');
        $addition = $this->makeCommandAddition($command);
        $addition->setAutoExit(false);

        $return = ($addition->getHandler())([], []);

        $this->assertSame(0, $return);
    }

    protected function makeCommandAddition(Command $command): CommandAddition
    {
        return new CommandAddition($command, $this->invocationStrategy, $this->wpCliAdapter);
    }
}
