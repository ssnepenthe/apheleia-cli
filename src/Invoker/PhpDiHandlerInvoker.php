<?php

declare(strict_types=1);

namespace ApheleiaCli\Invoker;

use ApheleiaCli\Command;
use ApheleiaCli\Input\InputInterface;
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

    public function invoke(callable $handler, InputInterface $input, ConsoleOutputInterface $output, Command $command) {
        $arguments = $input->getArguments();
        $assocArgs = array_merge($input->getOptions(), $input->getFlags());

        // @todo Should we mark these as reserved variable names somehow so user can't overwrite them?
        $parameters = [
            'args' => $arguments,
            'assocArgs' => $assocArgs,

            'arguments' => $arguments,
            'options' => $input->getOptions(),
            'flags' => $input->getFlags(),

            'command' => $command,
            Command::class => $command,

            'input' => $input,
            InputInterface::class => $input,

            'output' => $output,
            ConsoleOutputInterface::class => $output,
            OutputInterface::class => $output,
        ];

        $registeredArgs = $command->getArguments();

        while (count($arguments)) {
            $current = array_shift($registeredArgs);

            $name = $current->getName();

            if ($current->getRepeating()) {
                $parameters[$name] = $arguments;

                $arguments = [];
            } else {
                $arg = array_shift($arguments);

                $parameters[$name] = $arg;
            }
        }

        foreach ($command->getOptions() as $option) {
            $name = $option->getName();

            if (array_key_exists($name, $assocArgs)) {
                $parameters[$name] = $assocArgs[$name];

                unset($assocArgs[$name]);
            }
        }

        if ($command->getAcceptArbitraryOptions() && ! empty($assocArgs)) {
            $parameters['arbitraryOptions'] = $assocArgs;
        }

        return $this->invoker->call($handler, $parameters);
    }
}
