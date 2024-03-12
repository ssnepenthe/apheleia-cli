<?php

declare(strict_types=1);

namespace ApheleiaCli\Tests\Invoker;

use ApheleiaCli\Command;
use ApheleiaCli\Input\InputInterface;
use ApheleiaCli\Invoker\DefaultGenericInvoker;
use ApheleiaCli\Invoker\DefaultHandlerInvoker;
use ApheleiaCli\Invoker\GenericInvokerInterface;
use ApheleiaCli\Invoker\HandlerInvokerInterface;
use ApheleiaCli\Invoker\InvokerFactory;
use ApheleiaCli\Invoker\PhpDiGenericInvoker;
use ApheleiaCli\Invoker\PhpDiHandlerInvoker;
use ApheleiaCli\Output\ConsoleOutputInterface;
use ApheleiaCli\Output\WpCliLoggerStandIn;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class InvokerFactoryTest extends TestCase
{
    public function testCreateGenericInvokerBuiltInFactory()
    {
        // @todo Test with custom php-di invoker?
        $factory = new InvokerFactory();

        $this->assertInstanceOf(
            DefaultGenericInvoker::class,
            $factory->createGenericInvoker(DefaultGenericInvoker::class)
        );
        $this->assertInstanceOf(
            PhpDiGenericInvoker::class,
            $factory->createGenericInvoker(PhpDiGenericInvoker::class)
        );
    }
    public function testCreateGenericInvokerCustomFactory()
    {
        $factory = new InvokerFactory();

        $factory->registerGenericInvokerFactory(
            GenericInvokerWithConstructorForTests::class,
            fn () => new GenericInvokerWithConstructorForTests('irrelevant')
        );

        $this->assertInstanceOf(
            GenericInvokerWithConstructorForTests::class,
            $factory->createGenericInvoker(GenericInvokerWithConstructorForTests::class)
        );
    }

    public function testCreateGenericInvokerFallbackFactory()
    {
        $factory = new InvokerFactory();

        $this->assertFalse($factory->hasGenericInvokerFactory(GenericInvokerForTests::class));
        $this->assertInstanceOf(
            GenericInvokerForTests::class,
            $factory->createGenericInvoker(GenericInvokerForTests::class)
        );
    }

    public function testCreateGenericInvokerFallbackFactoryThrowsWhenClassDoesNotExist()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('non-existent class');

        $factory = new InvokerFactory();

        $factory->createGenericInvoker(NonExistentClass::class);
    }

    public function testCreateGenericInvokerFallbackFactoryThrowsWhenClassHasRequiredConstructorParameters()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('no custom factory has been registered');

        $factory = new InvokerFactory();

        $factory->createGenericInvoker(GenericInvokerWithConstructorForTests::class);
    }

    public function testCreateGenericInvokerOverrideFactory()
    {
        $factory = new InvokerFactory();
        $invoker = new DefaultGenericInvoker();

        $factory->registerGenericInvokerFactory(DefaultGenericInvoker::class, fn () => $invoker);

        $this->assertSame($invoker, $factory->createGenericInvoker(DefaultGenericInvoker::class));
    }

    public function testCreateHandlerInvokerBuiltInFactory()
    {
        // @todo Test with custom php-di invoker?
        $factory = new InvokerFactory();

        $this->assertInstanceOf(
            DefaultHandlerInvoker::class,
            $factory->createHandlerInvoker(DefaultHandlerInvoker::class)
        );
        $this->assertInstanceOf(
            PhpDiHandlerInvoker::class,
            $factory->createHandlerInvoker(PhpDiHandlerInvoker::class)
        );
    }

    public function testCreateHandlerInvokerCustomFactory()
    {
        $factory = new InvokerFactory();

        $factory->registerHandlerInvokerFactory(
            HandlerInvokerWithConstructorForTests::class,
            fn () => new HandlerInvokerWithConstructorForTests('irrelevant')
        );

        $this->assertInstanceOf(
            HandlerInvokerWithConstructorForTests::class,
            $factory->createHandlerInvoker(HandlerInvokerWithConstructorForTests::class)
        );
    }

    public function testCreateHandlerInvokerFallbackFactory()
    {
        $factory = new InvokerFactory();

        $this->assertFalse($factory->hasHandlerInvokerFactory(HandlerInvokerForTests::class));
        $this->assertInstanceOf(
            HandlerInvokerForTests::class,
            $factory->createHandlerInvoker(HandlerInvokerForTests::class)
        );
    }

    public function testCreateHandlerInvokerOverrideFactory()
    {
        $factory = new InvokerFactory();
        $invoker = new DefaultHandlerInvoker();

        $factory->registerHandlerInvokerFactory(DefaultHandlerInvoker::class, fn () => $invoker);

        $this->assertSame($invoker, $factory->createHandlerInvoker(DefaultHandlerInvoker::class));
    }

    public function testCreatePhpDiHandlerInvokerFallbackFactoryThrowsWhenClassDoesNotExist()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('non-existent class');

        $factory = new InvokerFactory();

        $factory->createHandlerInvoker(NonExistentClass::class);
    }

    public function testCreatePhpDiHandlerInvokerFallbackFactoryThrowsWhenClassHasRequiredConstructorParameters()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('no custom factory has been registered');

        $factory = new InvokerFactory();

        $factory->createHandlerInvoker(HandlerInvokerWithConstructorForTests::class);
    }
}

class GenericInvokerForTests implements GenericInvokerInterface
{
    public function invoke(callable $callback, array $arguments = [])
    {

    }
}

class HandlerInvokerForTests implements HandlerInvokerInterface
{
    public function invoke(callable $handler, InputInterface $input, ConsoleOutputInterface $output, Command $command)
    {

    }
}

class GenericInvokerWithConstructorForTests implements GenericInvokerInterface
{
    public function __construct($irrelevant)
    {

    }

    public function invoke(callable $callback, array $arguments = [])
    {

    }
}

class HandlerInvokerWithConstructorForTests implements HandlerInvokerInterface
{
    public function __construct($irrelevant)
    {

    }

    public function invoke(callable $handler, InputInterface $input, ConsoleOutputInterface $output, Command $command)
    {

    }
}
