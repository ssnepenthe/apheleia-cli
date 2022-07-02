<?php

declare(strict_types=1);

namespace ApheleiaCli;

interface InvocationStrategyInterface
{
    public function call(callable $callback);
    public function callCommandHandler(Command $command);
    public function withContext(array $context);
}
