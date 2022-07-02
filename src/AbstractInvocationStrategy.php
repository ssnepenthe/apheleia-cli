<?php

declare(strict_types=1);

namespace ApheleiaCli;

abstract class AbstractInvocationStrategy implements InvocationStrategyInterface
{
    protected $context = [];

    abstract public function call(callable $callback);

    abstract public function callCommandHandler(Command $command);

    public function withContext(array $context)
    {
        $strategy = clone $this;
        $strategy->context = $context;

        return $strategy;
    }
}
