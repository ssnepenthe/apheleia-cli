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
        $registeredParameters = [
            ...$this->command->getArguments(),
            ...$this->command->getOptions(),
        ];

        foreach ($defaults as $param => $default) {
            if ('--' === substr($param, 0, 2)) {
                $param = substr($param, 2);
            }

            if (! array_key_exists($param, $registeredParameters)) {
                throw new InvalidArgumentException(
                    "Cannot set default for unregistered parameter '{$param}'"
                );
            }

            $found = $registeredParameters[$param];

            if ($found instanceof Flag) {
                throw new InvalidArgumentException(
                    "Cannot set default for flag '{$param}' - flags always default to false"
                );
            }

            $found->setDefault($default);
        }

        return $this;
    }

    public function descriptions(string $commandDescription, array $paramDescriptions = []): self
    {
        $this->command->setDescription($commandDescription);

        $registeredParameters = [
            ...$this->command->getArguments(),
            ...$this->command->getOptions(),
        ];

        foreach ($paramDescriptions as $param => $description) {
            if ('--' === substr($param, 0, 2)) {
                $param = substr($param, 2);
            }

            if (! array_key_exists($param, $registeredParameters)) {
                throw new InvalidArgumentException(
                    "Cannot set description for unregistered parameter '{$param}'"
                );
            }

            $registeredParameters[$param]->setDescription($description);
        }

        return $this;
    }

    public function getCommand(): Command
    {
        return $this->command;
    }

    public function options(array $options): self
    {
        $registeredParameters = [
            ...$this->command->getArguments(),
            ...$this->command->getOptions(),
        ];

        foreach ($options as $param => $paramOptions) {
            if (! is_array($paramOptions)) {
                throw new InvalidArgumentException(
                    'Parameter options must be specified as an array of strings'
                );
            }

            if ('--' === substr($param, 0, 2)) {
                $param = substr($param, 2);
            }

            if (! array_key_exists($param, $registeredParameters)) {
                throw new InvalidArgumentException(
                    "Cannot set options for unregistered parameter '{$param}'"
                );
            }

            $found = $registeredParameters[$param];

            if ($found instanceof Flag) {
                throw new InvalidArgumentException(
                    "Cannot set options for flag '{$param}' - flags can only be true or false"
                );
            }

            $found->setOptions(...$paramOptions);
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
}
