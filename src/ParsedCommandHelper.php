<?php

declare(strict_types=1);

namespace ApheleiaCli;

use InvalidArgumentException;

class ParsedCommandHelper
{
    protected $command;
    protected $parameterNameMappers;

    public function __construct(Command $command, array $parameterNameMappers = [])
    {
        $this->command = $command;
        $this->setParameterNameMappers(...($parameterNameMappers ?: [
            fn (string $string): string => $string,
            [Support::class, 'kebabCase'],
            [Support::class, 'snakeCase'],
            [Support::class, 'camelCase'],
            [Support::class, 'pascalCase'],
        ]));
    }

    public function after(callable $callback): self
    {
        $this->command->setAfterInvokeCallback($callback);

        return $this;
    }

    public function before(callable $callback): self
    {
        $this->command->setBeforeInvokeCallback($callback);

        return $this;
    }

    public function defaults(array $defaults): self
    {
        $registeredParameters = array_merge(
            $this->command->getArguments(),
            $this->command->getOptions()
        );

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

        $registeredParameters = array_merge(
            $this->command->getArguments(),
            $this->command->getOptions()
        );

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

    public function handler($handler): self
    {
        $this->command->setHandler($handler);

        $this->setHandlerDefaultsViaReflection();

        return $this;
    }

    public function options(array $options): self
    {
        $registeredParameters = array_merge(
            $this->command->getArguments(),
            $this->command->getOptions()
        );

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

    protected function setHandlerDefaultsViaReflection(): void
    {
        $handler = $this->command->getHandler();

        if (! is_callable($handler)) {
            return;
        }

        $registeredParameters = array_merge(
            $this->command->getArguments(),
            $this->command->getOptions()
        );

        $reflector = Support::callableReflector($handler);

        foreach ($reflector->getParameters() as $parameter) {
            if (! $parameter->isDefaultValueAvailable()) {
                continue;
            }

            $name = $parameter->getName();

            foreach ($this->parameterNameMappers as $mapper) {
                $realName = $mapper($name);

                if (! \array_key_exists($realName, $registeredParameters)) {
                    continue;
                }

                $found = $registeredParameters[$realName];

                if (! $found instanceof Flag) {
                    $found->setDefault($parameter->getDefaultValue());
                }

                break;
            }
        }
    }

    protected function setParameterNameMappers(callable ...$parameterNameMappers): void
    {
        $this->parameterNameMappers = $parameterNameMappers;
    }
}
