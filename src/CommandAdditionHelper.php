<?php

namespace ToyWpCli;

use InvalidArgumentException;

class CommandAdditionHelper
{
    protected $command;

    public function __construct(Command $command)
    {
        $this->command = $command;
    }

    public function after($callback)
    {
        $this->command->setAfterInvokeCallback($callback);

        return $this;
    }

    public function before($callback)
    {
        $this->command->setBeforeInvokeCallback($callback);

        return $this;
    }

    public function defaults(array $defaults)
    {
        // @todo Throw nfor not-found params?
        foreach ($defaults as $param => $default) {
            if ($this->describesOption($param)) {
                if ($option = $this->findOption($param)) {
                    $option->setDefault($default);
                }
            } else {
                if ($argument = $this->findArgument($param)) {
                    $argument->setDefault($default);
                }
            }
        }

        return $this;
    }

    public function descriptions(string $commandDescription, array $paramDescriptions = [])
    {
        // @todo Throw for not-found params?
        $this->command->setDescription($commandDescription);

        foreach ($paramDescriptions as $param => $description) {
            if ($this->describesOption($param)) {
                if ($option = $this->findOption($param)) {
                    $option->setDescription($description);
                }
            } else {
                if ($argument = $this->findArgument($param)) {
                    $argument->setDescription($description);
                }
            }
        }

        return $this;
    }

    public function options(array $options)
    {
        // @todo Throw for not-found params?
        foreach ($options as $param => $paramOptions) {
            if (! is_array($paramOptions)) {
                throw new InvalidArgumentException(
                    'Parameter options must be specified as an array of string'
                );
            }

            if ($this->describesOption($param)) {
                if ($option = $this->findOption($param)) {
                    if ($option instanceof Flag) {
                        throw new InvalidArgumentException('Cannot set options for Flag params');
                    }

                    $option->setOptions(...$paramOptions);
                }
            } else {
                if ($argument = $this->findArgument($param)) {
                    $argument->setOptions(...$paramOptions);
                }
            }
        }

        return $this;
    }

    public function usage(string $usage)
    {
        $this->command->setUsage($usage);

        return $this;
    }

    public function when(string $when)
    {
        $this->command->setWhen($when);

        return $this;
    }

    protected function describesOption(string $name): bool
    {
        return '--' === substr($name, 0, 2);
    }

    protected function findArgument(string $name)
    {
        if (array_key_exists($name, $this->command->getArguments())) {
            return $this->command->getArguments()[$name];
        }
    }

    protected function findOption(string $name)
    {
        $name = substr($name, 2);

        if (array_key_exists($name, $this->command->getOptions())) {
            return $this->command->getOptions()[$name];
        }
    }
}
