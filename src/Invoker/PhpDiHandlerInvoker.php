<?php

declare(strict_types=1);

namespace ApheleiaCli\Invoker;

use Invoker\InvokerInterface;

class PhpDiHandlerInvoker implements HandlerInvokerInterface
{
    protected InvokerInterface $invoker;

    public function __construct(InvokerInterface $invoker)
    {
        $this->invoker = $invoker;
    }

    public function invoke(callable $handler, array $arguments = [])
    {
        $parameters = [
            'args' => $arguments['args'],
            'arguments' => $arguments['args'],

            'assocArgs' => $arguments['assocArgs'],
            'options' => $arguments['assocArgs'],

            'command' => $arguments['command'],
        ];

        $command = $arguments['command'];
        $registeredArgs = $command->getArguments();

        while (count($arguments['args'])) {
            $current = array_shift($registeredArgs);

            $name = $current->getName();

            if ($current->getRepeating()) {
                $parameters[$name] = $arguments['args'];

                $arguments['args'] = [];
            } else {
                $arg = array_shift($arguments['args']);

                $parameters[$name] = $arg;
            }
        }

        foreach ($command->getOptions() as $option) {
            $name = $option->getName();

            if (array_key_exists($name, $arguments['assocArgs'])) {
                $parameters[$name] = $arguments['assocArgs'][$name];

                unset($arguments['assocArgs'][$name]);
            }
        }

        if ($command->getAcceptArbitraryOptions() && ! empty($arguments['assocArgs'])) {
            $parameters['arbitraryOptions'] = $arguments['assocArgs'];
        }

        return $this->invoker->call($handler, $parameters);
    }
}
