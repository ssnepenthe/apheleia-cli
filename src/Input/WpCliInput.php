<?php

declare(strict_types=1);

namespace ApheleiaCli\Input;

use ApheleiaCli\Command;
use ApheleiaCli\Option;
use RuntimeException;

class WpCliInput extends ArrayInput
{
    protected $rawArgs;

    protected $rawAssocArgs;

    public function __construct(array $args, array $assocArgs, Command $command)
    {
        $this->rawArgs = $args;
        $this->rawAssocArgs = $assocArgs;

        $arguments = $this->processArgs($args, $command);
        [$options, $flags] = $this->processAssocArgs($assocArgs, $command);

        parent::__construct($arguments, $options, $flags);
    }

    public function getRawArgs(): array
    {
        return $this->rawArgs;
    }

    public function getRawAssocArgs(): array
    {
        return $this->rawAssocArgs;
    }

    private function processArgs(array $args, Command $command): array
    {
        $arguments = [];
        $registeredArguments = $command->getArguments();

        while (count($args)) {
            $currentArgument = array_shift($registeredArguments);

            if (null === $currentArgument) {
                throw new RuntimeException("Too many args provided for command {$command->getName()}");
            }

            if ($currentArgument->getRepeating()) {
                $arguments[$currentArgument->getName()] = $args;
                $args = [];
            } else {
                $arguments[$currentArgument->getName()] = array_shift($args);
            }
        }

        foreach ($registeredArguments as $currentArgument) {
            if (! $currentArgument->getOptional()) {
                throw new RuntimeException(
                    "Missing required argument {$currentArgument->getName()} for command {$command->getName()}"
                );
            }
        }

        return $arguments;
    }

    private function processAssocArgs(array $assocArgs, Command $command): array
    {
        $options = $flags = [];

        foreach ($command->getOptions() as $option) {
            $name = $option->getName();

            if (! array_key_exists($name, $assocArgs)) {
                if ($option instanceof Option && ! $option->getOptional()) {
                    throw new RuntimeException(
                        "Missing required option {$option->getName()} for command {$command->getName()}"
                    );
                }

                continue;
            }

            if ($option instanceof Option) {
                $options[$name] = $assocArgs[$name];
            } else {
                $flags[$name] = $assocArgs[$name];
            }

            unset($assocArgs[$name]);
        }

        if (! empty($assocArgs)) {
            if ($command->getAcceptArbitraryOptions()) {
                $options['arbitraryOptions'] = $assocArgs;
                $assocArgs = [];
            } else {
                throw new RuntimeException("Too many options provided for command {$command->getName()}");
            }
        }

        return [$options, $flags];
    }
}
