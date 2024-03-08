<?php

declare(strict_types=1);

namespace ApheleiaCli\Invoker;

class DefaultGenericInvoker implements GenericInvokerInterface
{
    public function invoke(callable $callback, array $arguments = [])
    {
        return $callback($arguments);
    }
}
