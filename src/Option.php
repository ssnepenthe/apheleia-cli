<?php

declare(strict_types=1);

namespace ApheleiaCli;

use InvalidArgumentException;
use RuntimeException;

class Option
{
    public const NAME_PATTERN = 'a-z\-_0-9';
    public const VALUE_PATTERN = 'a-zA-Z\-_0-9';

    /**
     * @var ?string
     */
    protected $default;

    /**
     * @var ?string
     */
    protected $description;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var bool
     */
    protected $optional = true;

    /**
     * @var string[]
     */
    protected $options = [];

    /**
     * @var bool
     */
    protected $valueIsOptional = false;

    public function __construct(string $name)
    {
        if (! static::isValidName($name)) {
            throw new InvalidArgumentException(
                'Invalid option name - must only contain lowercase letters, numbers, -, and _'
            );
        }

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

    /**
     * @return string[]
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @return array{type: 'assoc', name: string, optional: bool, repeating: bool, description?: string, default?: string, options?: string[], value?: array{optional: true, name: string}}
     */
    public function getSynopsis(): array
    {
        $this->validate();

        $synopsis = [
            'type' => 'assoc',
            'name' => $this->name,
            'optional' => $this->optional,
            'repeating' => false,
        ];

        if (is_string($this->description)) {
            $synopsis['description'] = $this->description;
        }

        if (is_string($this->default)) {
            $synopsis['default'] = $this->default;
        }

        if (! empty($this->options)) {
            $synopsis['options'] = $this->options;
        }

        if ($this->valueIsOptional) {
            $synopsis['value'] = [
                'optional' => true,
                'name' => $this->name,
            ];
        }

        return $synopsis;
    }

    public function getValueIsOptional(): bool
    {
        return $this->valueIsOptional;
    }

    public static function isValidName(string $name): bool
    {
        $pattern = sprintf('/[^%s]/', static::NAME_PATTERN);

        return preg_replace($pattern, '', $name) === $name;
    }

    public function setDefault(string $default): self
    {
        $this->default = $default;

        return $this;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function setOptional(bool $optional): self
    {
        $this->optional = $optional;

        return $this;
    }

    public function setOptions(string ...$options): self
    {
        $this->options = array_values($options);

        return $this;
    }

    public function setValueIsOptional(bool $valueIsOptional): self
    {
        $this->valueIsOptional = $valueIsOptional;

        return $this;
    }

    protected function validate(): void
    {
        if (! $this->optional && $this->valueIsOptional) {
            throw new RuntimeException(
                "Required option '{$this->name}' cannot have an optional value"
            );
        }
    }
}
