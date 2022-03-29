<?php

declare(strict_types=1);

namespace ApheleiaCli;

use InvalidArgumentException;

class CommandAdditionHelper
{
    protected $command;

    public function __construct(Command $command)
    {
        $this->command = $command;
    }

    public function after($callback): self
    {
        $this->command->setAfterInvokeCallback($callback);

        return $this;
    }

    public function before($callback): self
    {
        $this->command->setBeforeInvokeCallback($callback);

        return $this;
    }

    public function defaults(array $defaults): self
    {
        foreach ($defaults as $param => $default) {
            if ($this->describesOption($param)) {
                if (! $option = $this->findOption($param)) {
                    throw new InvalidArgumentException(
                        "Cannot set default for unregistered option '{$param}'"
                    );
                }

                if ($option instanceof Flag) {
                    throw new InvalidArgumentException(
                        "Cannot set default for flag '{$param}' - flags always default to false"
                    );
                }

                $option->setDefault($default);
            } else {
                if (! $argument = $this->findArgument($param)) {
                    throw new InvalidArgumentException(
                        "Cannot set default for unregistered argument '{$param}'"
                    );
                }

                $argument->setDefault($default);
            }
        }

        return $this;
    }

    public function descriptions(string $commandDescription, array $paramDescriptions = []): self
    {
        $this->command->setDescription($commandDescription);

        foreach ($paramDescriptions as $param => $description) {
            if ($this->describesOption($param)) {
                if (! $option = $this->findOption($param)) {
                    throw new InvalidArgumentException(
                        "Cannot set description for unregistered option '{$param}'"
                    );
                }

                $option->setDescription($description);
            } else {
                if (! $argument = $this->findArgument($param)) {
                    throw new InvalidArgumentException(
                        "Cannot set description for unregistered argument '{$param}'"
                    );
                }

                $argument->setDescription($description);
            }
        }

        return $this;
    }

    public function getCommand(): Command
    {
        return $this->command;
    }

    public function options(array $options): self
    {
        foreach ($options as $param => $paramOptions) {
            if (! is_array($paramOptions)) {
                throw new InvalidArgumentException(
                    'Parameter options must be specified as an array of strings'
                );
            }

            if ($this->describesOption($param)) {
                if (! $option = $this->findOption($param)) {
                    throw new InvalidArgumentException(
                        "Cannot set options for unregistered option '{$param}'"
                    );
                }

                if ($option instanceof Flag) {
                    throw new InvalidArgumentException(
                        "Cannot set options for flag '{$param}' - flags can only be true or false"
                    );
                }

                $option->setOptions(...$paramOptions);
            } else {
                if (! $argument = $this->findArgument($param)) {
                    throw new InvalidArgumentException(
                        "Cannot set options for unregistered argument '{$param}'"
                    );
                }

                $argument->setOptions(...$paramOptions);
            }
        }

        return $this;
    }

    public function usage(string $usage): self
    {
        $this->command->setUsage($usage);

        return $this;
    }

    public function when(string $when): self
    {
        $this->command->setWhen($when);

        return $this;
    }

    protected function describesOption(string $name): bool
    {
        return '--' === substr($name, 0, 2);
    }

    protected function findArgument(string $name): ?Argument
    {
        if (array_key_exists($name, $this->command->getArguments())) {
            return $this->command->getArguments()[$name];
        }

        return null;
    }

    /**
     * @return Flag|Option|null
     */
    protected function findOption(string $name)
    {
        $name = substr($name, 2);

        if (array_key_exists($name, $this->command->getOptions())) {
            return $this->command->getOptions()[$name];
        }

        return null;
    }
}
