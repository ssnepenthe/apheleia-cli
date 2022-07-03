<?php

declare(strict_types=1);

namespace ApheleiaCli;

interface InvocationStrategyInterface
{
    /**
     * @return mixed
     */
    public function call(callable $callback);

    /**
     * @return mixed
     */
    public function callCommandHandler(Command $command);

    /**
     * @return static
     */
    public function withContext(array $context);
}
