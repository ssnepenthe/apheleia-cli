<?php

declare(strict_types=1);

namespace ApheleiaCli;

use InvalidArgumentException;
use RuntimeException;

class Argument
{
    public const NAME_PATTERN = 'a-zA-Z\-_0-9';

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
    protected $optional = false;

    /**
     * @var string[]
     */
    protected $options = [];

    /**
     * @var bool
     */
    protected $repeating = false;

    public function __construct(string $name)
    {
        if (! static::isValidName($name)) {
            throw new InvalidArgumentException(
                'Invalid argument name - must only contain letters, numbers, -, and _'
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

    public function getRepeating(): bool
    {
        return $this->repeating;
    }

    /**
     * @return array{type: 'positional', name: string, optional: bool, repeating: bool, description?: string, default?: string, options?: string[]}
     */
    public function getSynopsis(): array
    {
        $this->validate();

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

    public function setRepeating(bool $repeating): self
    {
        $this->repeating = $repeating;

        return $this;
    }

    protected function validate(): void
    {
        if (is_string($this->default) && ! $this->optional) {
            throw new RuntimeException(
                "Required argument '{$this->name}' cannot have a default value"
            );
        }
    }
}
