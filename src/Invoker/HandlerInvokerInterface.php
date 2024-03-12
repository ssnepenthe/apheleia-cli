<?php

declare(strict_types=1);

namespace ApheleiaCli\Invoker;

use ApheleiaCli\Command;
use ApheleiaCli\Input\InputInterface;
use ApheleiaCli\Output\ConsoleOutputInterface;

interface HandlerInvokerInterface
{
    public function invoke(callable $handler, InputInterface $input, ConsoleOutputInterface $output, Command $command);
}
