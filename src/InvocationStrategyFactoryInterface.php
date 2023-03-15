<?php

declare(strict_types=1);

namespace ApheleiaCli;

interface InvocationStrategyFactoryInterface
{
    /**
     * @param class-string<InvocationStrategyInterface> $strategy
     */
    public function create(string $strategy): InvocationStrategyInterface;

    public function createForCommand(Command $command): InvocationStrategyInterface;
}
