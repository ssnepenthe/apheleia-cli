<?php

declare(strict_types=1);

namespace ApheleiaCli\Invoker;

use Invoker\InvokerInterface;

class PhpDiGenericInvoker implements GenericInvokerInterface
{
    protected InvokerInterface $invoker;

    public function __construct(InvokerInterface $invoker)
    {
        $this->invoker = $invoker;
    }

    public function invoke(callable $callback, array $arguments = [])
    {
        return $this->invoker->call($callback, $arguments);
    }
}
