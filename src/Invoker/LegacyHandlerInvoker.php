<?php

declare(strict_types=1);

namespace ApheleiaCli\Invoker;

use ApheleiaCli\Command;
use ApheleiaCli\Input\InputInterface;
use ApheleiaCli\Input\WpCliInput;
use ApheleiaCli\Output\ConsoleOutputInterface;
use RuntimeException;

class LegacyHandlerInvoker implements HandlerInvokerInterface
{
    public function invoke(callable $handler, InputInterface $input, ConsoleOutputInterface $output, Command $command)
    {
        if (! $input instanceof WpCliInput) {
            throw new RuntimeException('LegacyHandlerInvoker requires $input to be of type WpCliInput');
        }

        return $handler($input->getRawArgs(), $input->getRawAssocArgs());
    }
}
