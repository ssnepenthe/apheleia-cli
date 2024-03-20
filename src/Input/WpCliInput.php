<?php

declare(strict_types=1);

namespace ApheleiaCli\Input;

use ApheleiaCli\Command;
use ApheleiaCli\Option;
use RuntimeException;

class WpCliInput extends ArrayInput
{
    /**
     * @var string[]
     */
    protected $rawArgs;

    /**
     * @var array<string, bool|string>
     */
    protected $rawAssocArgs;

    /**
     * @param string[] $args
     * @param array<string, bool|string> $assocArgs
     */
    public function __construct(array $args, array $assocArgs, Command $command)
    {
        $this->rawArgs = $args;
        $this->rawAssocArgs = $assocArgs;

        $arguments = $this->processArgs($args, $command);
        [$options, $flags] = $this->processAssocArgs($assocArgs, $command);

        parent::__construct($arguments, $options, $flags);
    }

    /**
     * @return string[]
     */
    public function getWpCliArguments(): array
    {
        return $this->rawArgs;
    }

    /**
     * @return array<string, bool|string>
     */
    public function getWpCliAssociativeArguments(): array
    {
        return $this->rawAssocArgs;
    }

    /**
     * @param string[] $args
     * @return array<string, string|string[]>
     */
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

    /**
     * @param array<string, bool|string> $assocArgs
     * @return array{0: array<string, array<string, string>|string>, 1: array<string, bool>}
     */
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
                /** @var string $value */
                $value = $assocArgs[$name];
                $options[$name] = $value;
            } else {
                /** @var bool $value */
                $value = $assocArgs[$name];
                $flags[$name] = $value;
            }

            unset($assocArgs[$name]);
        }

        if (! empty($assocArgs)) {
            if ($command->getAcceptArbitraryOptions()) {
                /** @var array<string, string> $value */
                $value = $assocArgs;
                $assocArgs = [];
                $options['arbitraryOptions'] = $value;
            } else {
                throw new RuntimeException("Too many options provided for command {$command->getName()}");
            }
        }

        return [$options, $flags];
    }
}
