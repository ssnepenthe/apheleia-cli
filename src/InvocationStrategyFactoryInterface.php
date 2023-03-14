<?php

namespace ApheleiaCli;

interface InvocationStrategyFactoryInterface
{
    /**
     * @param class-string<InvocationStrategyInterface> $strategy
     */
    public function create(string $strategy): InvocationStrategyInterface;

    public function createForCommand(Command $command): InvocationStrategyInterface;
}
