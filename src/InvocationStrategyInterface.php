<?php

declare(strict_types=1);

namespace ApheleiaCli;

interface InvocationStrategyInterface
{
    public function call($callback);
    public function callCommandHandler(Command $command);
    public function withContext(array $context);
}
