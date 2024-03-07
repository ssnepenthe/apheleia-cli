<?php

declare(strict_types=1);

namespace ApheleiaCli;

interface InvocationStrategyInterface
{
    /**
     * @return mixed
     */
    public function call(callable $callback, array $arguments = []);

    /**
     * @return mixed
     */
    public function callCommandHandler(Command $command, array $arguments = []);
}
