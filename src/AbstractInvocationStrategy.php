<?php

declare(strict_types=1);

namespace ApheleiaCli;

abstract class AbstractInvocationStrategy implements InvocationStrategyInterface
{
    /**
     * @var array
     */
    protected $context = [];

    /**
     * @return mixed
     */
    abstract public function call(callable $callback);

    /**
     * @return mixed
     */
    abstract public function callCommandHandler(Command $command);

    /**
     * @return static
     */
    public function withContext(array $context)
    {
        $strategy = clone $this;
        $strategy->context = $context;

        return $strategy;
    }
}
