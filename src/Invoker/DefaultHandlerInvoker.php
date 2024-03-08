<?php

declare(strict_types=1);

namespace ApheleiaCli\Invoker;

class DefaultHandlerInvoker implements HandlerInvokerInterface
{
    public function invoke(callable $handler, array $arguments = [])
    {
        return $handler($arguments['args'] ?? [], $arguments['assocArgs'] ?? []);
    }
}
