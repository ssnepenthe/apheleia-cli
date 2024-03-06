<?php

declare(strict_types=1);

namespace ApheleiaCli;

use Invoker\InvokerInterface;
use LogicException;
use ReflectionClass;
use ReflectionMethod;
use RuntimeException;

class InvocationStrategyFactory implements InvocationStrategyFactoryInterface
{
    /**
     * @var array<class-string<InvocationStrategyInterface>, callable():InvocationStrategyInterface>
     */
    protected $customCreators = [];
    protected $invoker;

    public function __construct(?InvokerInterface $invoker = null)
    {
        $this->invoker = $invoker;
    }

    /**
     * @param class-string<InvocationStrategyInterface> $strategy
     */
    public function create(string $strategy): InvocationStrategyInterface
    {
        if ($this->hasCustomCreator($strategy)) {
            return $this->callCustomCreator($strategy);
        }

        switch ($strategy) {
            case DefaultInvocationStrategy::class:
                return new DefaultInvocationStrategy();
            case InvokerBackedInvocationStrategy::class:
                return new InvokerBackedInvocationStrategy($this->invoker);
        }

        if (! class_exists($strategy)) {
            throw new RuntimeException("Cannot create instance of non-existent class {$strategy}");
        }

        $reflection = new ReflectionClass($strategy);
        $constructor = $reflection->getConstructor();

        if ($constructor instanceof ReflectionMethod && $constructor->getNumberOfRequiredParameters() > 0) {
            throw new RuntimeException("Cannot create instance of class {$strategy} because no custom creator has been registered");
        }

        return $reflection->newInstance();
    }

    /**
     * @param class-string<InvocationStrategyInterface> $strategy
     * @param callable():InvocationStrategyInterface $creator
     */
    public function registerCustomCreator(string $strategy, callable $creator): self
    {
        $this->customCreators[$strategy] = $creator;

        return $this;
    }

    protected function callCustomCreator(string $strategy): InvocationStrategyInterface
    {
        if (! array_key_exists($strategy, $this->customCreators)) {
            throw new LogicException(
                "Attempting to call unregistered custom creator for {$strategy} - make sure to check \$this->hasCustomCreator() before using \$this->callCustomCreator()"
            );
        }

        return ($this->customCreators[$strategy])();
    }

    protected function hasCustomCreator(string $strategy): bool
    {
        return array_key_exists($strategy, $this->customCreators);
    }
}
