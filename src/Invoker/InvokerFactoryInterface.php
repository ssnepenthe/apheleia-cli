<?php

declare(strict_types=1);

namespace ApheleiaCli\Invoker;

interface InvokerFactoryInterface
{
    /**
     * @param class-string<GenericInvokerInterface> $className
     */
    public function createGenericInvoker(string $className): GenericInvokerInterface;

    /**
     * @param class-string<HandlerInvokerInterface> $className
     */
    public function createHandlerInvoker(string $className): HandlerInvokerInterface;
}
