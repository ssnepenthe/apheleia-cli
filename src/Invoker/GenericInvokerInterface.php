<?php

declare(strict_types=1);

namespace ApheleiaCli\Invoker;

interface GenericInvokerInterface
{
    public function invoke(callable $callback, array $arguments = []);
}
