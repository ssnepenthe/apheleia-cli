<?php

declare(strict_types=1);

namespace ApheleiaCli\Invoker;

interface HandlerInvokerInterface
{
    /**
     * @todo verify typing on $assocArgs is correct
     * @param array{args: string[], assocArgs: array<string, bool|string>, command: Command, ...} $arguments
     */
    public function invoke(callable $handler, array $arguments = []);
}
