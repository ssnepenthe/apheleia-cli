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
            $parameter = $this->findArgument($param);

            if (null === $parameter) {
                $parameter = $this->findOption($param);
            }

            if ($parameter instanceof Flag) {
                throw new InvalidArgumentException(
                    "Cannot set default for flag '{$param}' - flags always default to false"
                );
            }

            if (null === $parameter) {
                throw new InvalidArgumentException(
                    "Cannot set default for unregistered parameter '{$param}'"
                );
            }

            $parameter->setDefault($default);
        }

        return $this;
    }

    public function descriptions(string $commandDescription, array $paramDescriptions = []): self
    {
        $this->command->setDescription($commandDescription);

        foreach ($paramDescriptions as $param => $description) {
            $parameter = $this->findArgument($param);

            if (null === $parameter) {
                $parameter = $this->findOption($param);
            }

            if (null === $parameter) {
                throw new InvalidArgumentException(
                    "Cannot set description for unregistered parameter '{$param}'"
                );
            }

            $parameter->setDescription($description);
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

            $parameter = $this->findArgument($param);

            if (null === $parameter) {
                $parameter = $this->findOption($param);
            }

            if ($parameter instanceof Flag) {
                throw new InvalidArgumentException(
                    "Cannot set options for flag '{$param}' - flags can only be true or false"
                );
            }

            if (null === $parameter) {
                throw new InvalidArgumentException(
                    "Cannot set options for unregistered parameter '{$param}'"
                );
            }

            $parameter->setOptions(...$paramOptions);
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
        if ('--' === substr($name, 0, 2)) {
            $name = substr($name, 2);
        }

        if (array_key_exists($name, $this->command->getOptions())) {
            return $this->command->getOptions()[$name];
        }

        return null;
    }
}
