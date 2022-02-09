<?php

namespace ToyWpCli;

use RuntimeException;

class Option
{
	protected $default;
	protected $description;
	protected $name;
	protected $options = [];
	protected $optional = true;
	protected $valueIsOptional = false;

	public function __construct(string $name)
	{
		$this->name = $name;
	}

	public function getDefault(): ?string
	{
		return $this->default;
	}

	public function getDescription(): ?string
	{
		return $this->description;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public function getOptions(): array
	{
		return $this->options;
	}

	public function getOptional(): bool
	{
		return $this->optional;
	}

	public function getValueIsOptional(): bool
	{
		return $this->valueIsOptional;
	}

	public function getSynopsis(): array
	{
		$synopsis = [
			'name' => $this->name,
			'optional' => $this->optional,
			'repeating' => false,
			'type' => 'assoc',
		];

		if ($this->description) {
			$synopsis['description'] = $this->description;
		}

		if (is_string($this->default)) {
			$synopsis['default'] = $this->default;
		}

		if (! empty($this->options)) {
			$synopsis['options'] = $this->options;
		}

		// @todo This doesn't currently work.
		// Revisit if/when https://github.com/wp-cli/wp-cli/pull/5618 is merged.
		if ($this->valueIsOptional) {
			$synopsis['value'] = [
				'optional' => true,
				'name' => $this->name,
			];
		}

		return $synopsis;
	}

	public function setDefault(string $default)
	{
		$this->default = $default;

		return $this;
	}

	public function setDescription(string $description)
	{
		$this->description = $description;

		return $this;
	}

	public function setOptional(bool $optional)
	{
		if ($this->valueIsOptional) {
			throw new RuntimeException('@todo');
		}

		$this->optional = $optional;

		return $this;
	}

	public function setOptions(string ...$options)
	{
		$this->options = $options;

		return $this;
	}

	public function setValueIsOptional(bool $valueIsOptional)
	{
		if (! $this->optional) {
			throw new RuntimeException('@todo');
		}

		$this->valueIsOptional = $valueIsOptional;

		return $this;
	}
}
