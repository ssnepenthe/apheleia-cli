<?php

namespace ToyWpCli;

use InvalidArgumentException;
use RuntimeException;
use WP_CLI\SynopsisParser;

// @todo Parser interface?
// @todo Check SynopsisParser::parse() for better parameter patterns.
class CommandParser
{
	public function parse(string $command): Command
	{
		$tokens = array_filter(preg_split('/\s+/', $command));

		if (empty($tokens)) {
			throw new RuntimeException('@todo empty command string');
		}

		$name = [];
		$token = array_shift($tokens);

		while (is_string($token) && $this->isName($token)) {
			$name[] = $token;

			$token = array_shift($tokens);
		}

		if (empty($name)) {
			throw new InvalidArgumentException('@todo nameless command');
		}

		$command = new Command();
		$command->setName(array_pop($name));

		if (! empty($name)) {
			$command->setNamespace(implode(' ', $name));
		}

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
				throw new InvalidArgumentException('@todo unrecognized token');
			}

			$token = array_shift($tokens);
		}

		return $command;
	}

	protected function argumentFromSignature(string $signature): Argument
	{
		$synopsis = SynopsisParser::parse($signature)[0] ?? [];

		if (! array_key_exists('type', $synopsis) || 'positional' !== $synopsis['type']) {
			throw new InvalidArgumentException('@todo no type or non-positional type');
		}

		if (! array_key_exists('name', $synopsis)) {
			// @todo Is this even possible?
			throw new InvalidArgumentException('@todo no argument name');
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

	protected function optionFromSignature(string $signature): Option
	{
		$synopsis = SynopsisParser::parse($signature)[0] ?? [];

		if (! array_key_exists('type', $synopsis) || 'assoc' !== $synopsis['type']) {
			throw new InvalidArgumentException('@todo no type or non-assoc type');
		}

		if (! array_key_exists('name', $synopsis)) {
			// @todo Is this even possible?
			throw new InvalidArgumentException('@todo no option name');
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

	protected function flagFromSignature(string $signature): Flag
	{
		$synopsis = SynopsisParser::parse($signature)[0] ?? [];

		if (! array_key_exists('type', $synopsis) || 'flag' !== $synopsis['type']) {
			throw new InvalidArgumentException('@todo no type of non-flag type');
		}

		if (! array_key_exists('name', $synopsis)) {
			// @todo Is this even possible?
			throw new InvalidArgumentException('@todo no flag name');
		}

		return new Flag($synopsis['name']);
	}

	protected function isName(string $token): bool
	{
		return 1 === preg_match('/^[^<\[\-\s].*[^>\]\.}]$/', $token);
	}

	protected function isArgument(string $token): bool
	{
		// SynopsisParser uses "/^<(([a-zA-Z-_|,0-9]+))>$/"
		return 1 === preg_match('/^(\[)?<[^>]+>(?:\.{3})?(?(1)\])$/', $token);
	}

	protected function isFlag(string $token): bool
	{
		// SynopsisParser uses "/^--(?:\\[no-\\])?([a-z-_0-9]+)/"
		return 1 === preg_match('/^\[--[^\[\]<>=]+\]$/', $token);
	}

	protected function isOption(string $token): bool
	{
		// SynopsisParser uses "/^--(?:\\[no-\\])?([a-z-_0-9]+)/"
		return 1 === preg_match('/^(\[)?--[^=\[<>]+(\[)?=<[^>]+>(?(2)\])(?(1)\])$/', $token);
	}

	protected function isGeneric(string $token): bool
	{
		return preg_match('/^(\[)?--<field>=<value>(?(1)\])$/', $token);
	}
}
