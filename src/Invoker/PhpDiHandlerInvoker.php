<?php

declare(strict_types=1);

namespace ApheleiaCli\Invoker;

use ApheleiaCli\Command;
use ApheleiaCli\Input\InputInterface;
use ApheleiaCli\Input\WpCliInput;
use ApheleiaCli\Output\ConsoleOutputInterface;
use ApheleiaCli\Output\OutputInterface;
use Invoker\InvokerInterface;

class PhpDiHandlerInvoker implements HandlerInvokerInterface
{
    protected InvokerInterface $invoker;

    public function __construct(InvokerInterface $invoker)
    {
        $this->invoker = $invoker;
    }

    public function invoke(callable $handler, InputInterface $input, ConsoleOutputInterface $output, Command $command)
    {
        // @todo Should we mark these as reserved variable names somehow so user can't overwrite them?
        $parameters = [
            'arguments' => $input->getArguments(),
            'options' => $input->getOptions(),
            'flags' => $input->getFlags(),

            'args' => $input->getWpCliArguments(),
            'assocArgs' => $input->getWpCliAssociativeArguments(),

            'command' => $command,
            Command::class => $command,

            'input' => $input,
            InputInterface::class => $input,

            'output' => $output,
            ConsoleOutputInterface::class => $output,
            OutputInterface::class => $output,
        ];

        $parameters = array_merge($parameters, $input->getArguments(), $input->getOptions(), $input->getFlags());

        return $this->invoker->call($handler, $parameters);
    }
}
