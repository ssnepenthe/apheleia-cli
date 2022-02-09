<?php

namespace ToyWpCli;

class Argument
{
	protected $default;
	protected $description;
	protected $name;
	protected $optional = false;
	protected $options = [];
	protected $repeating = false;

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

	public function getOptional(): bool
	{
		return $this->optional;
	}

	public function getOptions(): array
	{
		return $this->options;
	}

	public function getRepeating(): bool
	{
		return $this->repeating;
	}

	public function getSynopsis(): array
	{
		$synopsis = [
			'type' => 'positional',
			'name' => $this->name,
			'optional' => $this->optional,
			'repeating' => $this->repeating,
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
		$this->optional = $optional;

		return $this;
	}

	public function setOptions(string ...$options)
	{
		$this->options = $options;

		return $this;
	}

	public function setRepeating(bool $repeating)
	{
		$this->repeating = $repeating;

		return $this;
	}
}
