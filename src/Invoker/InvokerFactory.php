<?php

declare(strict_types=1);

namespace ApheleiaCli\Invoker;

use Invoker\Invoker;
use Invoker\InvokerInterface;
use Invoker\ParameterResolver\AssociativeArrayResolver;
use Invoker\ParameterResolver\DefaultValueResolver;
use Invoker\ParameterResolver\NumericArrayResolver;
use Invoker\ParameterResolver\ResolverChain;
use ReflectionClass;
use ReflectionMethod;
use RuntimeException;

class InvokerFactory implements InvokerFactoryInterface
{
    /**
     * @var array<class-string<GenericInvokerInterface>, callable():GenericInvokerInterface>
     */
    protected array $genericInvokerFactories = [];

    /**
     * @var array<class-string<HandlerInvokerInterface>, callable():HandlerInvokerInterface>
     */
    protected array $handlerInvokerFactories = [];

    protected ?InvokerInterface $invoker;

    public function __construct(?InvokerInterface $invoker = null)
    {
        $this->invoker = $invoker;
    }

    public function createGenericInvoker(string $className): GenericInvokerInterface
    {
        if ($this->hasGenericInvokerFactory($className)) {
            return ($this->genericInvokerFactories[$className])();
        }

        switch ($className) {
            case DefaultGenericInvoker::class:
                return new DefaultGenericInvoker();
            case PhpDiGenericInvoker::class:
                return new PhpDiGenericInvoker($this->invoker ?: $this->createPhpDiInvoker());
        }

        $this->ensureValidFallback($className);

        return new $className();
    }

    public function createHandlerInvoker(string $className): HandlerInvokerInterface
    {
        if ($this->hasHandlerInvokerFactory($className)) {
            return ($this->handlerInvokerFactories[$className])();
        }

        switch ($className) {
            case DefaultHandlerInvoker::class:
                return new DefaultHandlerInvoker();
            case PhpDiHandlerInvoker::class:
                return new PhpDiHandlerInvoker($this->invoker ?: $this->createPhpDiInvoker());
        }

        $this->ensureValidFallback($className);

        return new $className();
    }

    /**
     * @param class-string<GenericInvokerInterface> $className
     */
    public function hasGenericInvokerFactory(string $className): bool
    {
        return array_key_exists($className, $this->genericInvokerFactories);
    }

    /**
     * @param class-string<HandlerInvokerInterface> $className
     */
    public function hasHandlerInvokerFactory(string $className): bool
    {
        return array_key_exists($className, $this->handlerInvokerFactories);
    }

    /**
     * @todo Throw if already registered?
     * @param class-string<GenericInvokerInterface> $className
     * @param callable():GenericInvokerInterface $factory
     */
    public function registerGenericInvokerFactory(string $className, callable $factory): self
    {
        $this->genericInvokerFactories[$className] = $factory;

        return $this;
    }

    /**
     * @todo Throw if already registered?
     * @param class-string<HandlerInvokerInterface> $className
     * @param callable():HandlerInvokerInterface $factory
     */
    public function registerHandlerInvokerFactory(string $className, callable $factory): self
    {
        $this->handlerInvokerFactories[$className] = $factory;

        return $this;
    }

    protected function createPhpDiInvoker(): InvokerInterface
    {
        return new Invoker(
            new ResolverChain([
                new NumericArrayResolver(),
                new AssociativeArrayResolver(),
                new TransformingAssociativeArrayParameterResolver(),
                new DefaultValueResolver(),
            ])
        );
    }

    protected function ensureValidFallback(string $className)
    {
        if (! class_exists($className)) {
            throw new RuntimeException("Cannot create instance of non-existent class {$className}");
        }

        $reflection = new ReflectionClass($className);
        $constructor = $reflection->getConstructor();

        if ($constructor instanceof ReflectionMethod && $constructor->getNumberOfRequiredParameters() > 0) {
            throw new RuntimeException(
                "Cannot create instance of class {$className} because no custom factory has been registered"
            );
        }
    }
}
