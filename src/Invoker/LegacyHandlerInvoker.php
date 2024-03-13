<?php

namespace ApheleiaCli\Invoker;

use ApheleiaCli\Input\InputInterface;
use ApheleiaCli\Output\ConsoleOutputInterface;
use ApheleiaCli\Command;

class LegacyHandlerInvoker implements HandlerInvokerInterface
{
    public function invoke(callable $handler, InputInterface $input, ConsoleOutputInterface $output, Command $command)
    {
        return $handler($input->getArguments(), array_merge($input->getOptions(), $input->getFlags()));
    }
}
