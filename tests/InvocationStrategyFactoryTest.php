<?php

declare(strict_types=1);

namespace ApheleiaCli\Tests;

use ApheleiaCli\Command;
use ApheleiaCli\DefaultInvocationStrategy;
use ApheleiaCli\InvocationStrategyFactory;
use ApheleiaCli\InvocationStrategyInterface;
use ApheleiaCli\InvokerBackedInvocationStrategy;
use Invoker\Invoker;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class InvocationStrategyFactoryTest extends TestCase
{
    public function testCreateHandlesAllBundledInvocationStrategies()
    {
        $factory = new InvocationStrategyFactory();
        $factory->registerCustomCreator(
            InvocationStrategyForTestsWithConstructor::class,
            fn () => new InvocationStrategyForTestsWithConstructor('irrelevant')
        );

        // Supported via custom creator.
        $this->assertInstanceOf(
            InvocationStrategyForTestsWithConstructor::class,
            $factory->create(InvocationStrategyForTestsWithConstructor::class)
        );

        // Built-in support.
        $this->assertInstanceOf(
            DefaultInvocationStrategy::class,
            $factory->create(DefaultInvocationStrategy::class)
        );
        $this->assertInstanceOf(
            InvokerBackedInvocationStrategy::class,
            $factory->create(InvokerBackedInvocationStrategy::class)
        );

        // Falls back to new $strategy() for unknown strategies.
        $this->assertInstanceOf(
            InvocationStrategyForTests::class,
            $factory->create(InvocationStrategyForTests::class)
        );
    }

    public function testCreateInvokerBackedStrategyWithCustomInvoker()
    {
        $invoker = new Invoker();
        $factory = new InvocationStrategyFactory($invoker);

        $strategy = $factory->create(InvokerBackedInvocationStrategy::class);

        $this->assertSame($invoker, $strategy->getInvoker());
    }

    public function testCreateFailsForNonExistentStrategies()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('non-existent class');

        $factory = new InvocationStrategyFactory();
        $factory->create(NonExistentInvocationStrategy::class);
    }

    public function testCreateFailsForUnknownStrategiesWithConstructorArguments()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('no custom creator has been registered');

        $factory = new InvocationStrategyFactory();
        $factory->create(InvocationStrategyForTestsWithConstructor::class);
    }
}

class InvocationStrategyForTests implements InvocationStrategyInterface
{
    public function call(callable $callback)
    {
    }
    public function callCommandHandler(Command $command)
    {
    }
    public function withContext(array $context)
    {
    }
}

class InvocationStrategyForTestsWithConstructor implements InvocationStrategyInterface
{
    public function __construct($irrelevant)
    {
    }
    public function call(callable $callback)
    {
    }
    public function callCommandHandler(Command $command)
    {
    }
    public function withContext(array $context)
    {
    }
}
