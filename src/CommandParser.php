<?php

declare(strict_types=1);

namespace ApheleiaCli;

use InvalidArgumentException;
use RuntimeException;
use WP_CLI\SynopsisParser;

class CommandParser implements CommandParserInterface
{
    public function parse(string $command): Command
    {
        if (! class_exists(SynopsisParser::class)) {
            throw new RuntimeException(
                'Cannot use CommandParser because WP-CLI dependency is not available'
            );
        }

        $tokens = array_filter(preg_split('/\s+/', $command));

        if (empty($tokens)) {
            throw new InvalidArgumentException('Cannot parse empty command string');
        }

        $name = [];
        $token = array_shift($tokens);

        while (is_string($token) && $this->isName($token)) {
            $name[] = $token;

            $token = array_shift($tokens);
        }

        if (empty($name)) {
            throw new InvalidArgumentException('Command string must start with the command name');
        }

        $command = new Command();
        $command->setName(implode(' ', $name));

        while (is_string($token)) {
            if ($this->isArgument($token)) {
                $command->addArgument($this->argumentFromSignature($token));
            } elseif ($this->isFlag($token)) {
                $command->addFlag($this->flagFromSignature($token));
            } elseif ($this->isOption($token)) {
                $command->addOption($this->optionFromSignature($token));
            } elseif ($this->isGeneric($token)) {
                $command->setAcceptArbitraryOptions(true);
            } else {
                throw new InvalidArgumentException("Unrecognized token '{$token}'");
            }

            $token = array_shift($tokens);
        }

        return $command;
    }

    protected function argumentFromSignature(string $signature): Argument
    {
        $synopsis = SynopsisParser::parse($signature)[0] ?? [];

        if (! array_key_exists('type', $synopsis) || 'positional' !== $synopsis['type']) {
            throw new InvalidArgumentException(
                'Attempting to create Argument from non-positional parameter signature'
            );
        }

        if (! array_key_exists('name', $synopsis)) {
            throw new InvalidArgumentException('Unable to extract name from argument signature');
        }

        $argument = new Argument($synopsis['name']);

        if (! empty($synopsis['optional'])) {
            $argument->setOptional(true);
        }

        if (! empty($synopsis['repeating'])) {
            $argument->setRepeating(true);
        }

        return $argument;
    }

    protected function flagFromSignature(string $signature): Flag
    {
        $synopsis = SynopsisParser::parse($signature)[0] ?? [];

        if (! array_key_exists('type', $synopsis) || 'flag' !== $synopsis['type']) {
            throw new InvalidArgumentException(
                'Attempting to create Flag from non-flag parameter signature'
            );
        }

        if (! array_key_exists('name', $synopsis)) {
            throw new InvalidArgumentException('Unable to extract name from flag signature');
        }

        return new Flag($synopsis['name']);
    }

    protected function isArgument(string $token): bool
    {
        // SynopsisParser uses "/^<(([a-zA-Z-_|,0-9]+))>$/"
        $pattern = sprintf('/^(\[)?<[%s]+>(?:\.{3})?(?(1)\])$/', Argument::NAME_PATTERN);

        return 1 === preg_match($pattern, $token);
    }

    protected function isFlag(string $token): bool
    {
        // SynopsisParser uses "/^--(?:\\[no-\\])?([a-z-_0-9]+)/"
        $pattern = sprintf('/^\[--[%s]+\]$/', Flag::NAME_PATTERN);

        return 1 === preg_match($pattern, $token);
    }

    protected function isGeneric(string $token): bool
    {
        return 1 === preg_match('/^\[--<field>=<value>\]$/', $token);
    }

    protected function isName(string $token): bool
    {
        return 1 === preg_match('/^[^<\[\-\s].*[^>\]\.}]$/', $token);
    }

    protected function isOption(string $token): bool
    {
        // SynopsisParser uses "/^--(?:\\[no-\\])?([a-z-_0-9]+)/" for the option name and
        // "/^=<([a-zA-Z-_|,0-9]+)>$/" for the option value.
        $pattern = sprintf(
            '/^(\[)?--[%s]+(\[)?=<[%s]+>(?(2)\])(?(1)\])$/',
            Option::NAME_PATTERN,
            Option::VALUE_PATTERN
        );

        return 1 === preg_match($pattern, $token);
    }

    protected function optionFromSignature(string $signature): Option
    {
        $synopsis = SynopsisParser::parse($signature)[0] ?? [];

        if (! array_key_exists('type', $synopsis) || 'assoc' !== $synopsis['type']) {
            throw new InvalidArgumentException(
                'Attempting to create Option from non-associative parameter signature'
            );
        }

        if (! array_key_exists('name', $synopsis)) {
            throw new InvalidArgumentException('Unable to extract name from option signature');
        }

        $option = new Option($synopsis['name']);

        if (array_key_exists('optional', $synopsis) && ! $synopsis['optional']) {
            $option->setOptional(false);
        }

        if (! empty($synopsis['value']['optional'])) {
            $option->setValueIsOptional(true);
        }

        return $option;
    }
}
