<?php

namespace ApheleiaCli;

use Invoker\InvokerInterface;
use LogicException;

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
            default:
                return new $strategy();
        }
    }

    public function createForCommand(Command $command): InvocationStrategyInterface
    {
        return $this->create($command->getRequiredInvocationStrategy());
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
                'Attempting to call unregistered custom creator for {$strategy} - make sure to check $this->hasCustomCreator() before using $this->callCustomCreator()'
            );
        }

        return ($this->customCreators[$strategy])();
    }

    protected function hasCustomCreator(string $strategy): bool
    {
        return array_key_exists($strategy, $this->customCreators);
    }
}
