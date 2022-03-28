<?php

declare(strict_types=1);

namespace ToyWpCli;

use RuntimeException;

class Option
{
    protected $default;
    protected $description;
    protected $name;
    protected $optional = true;
    protected $options = [];
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

    public function getOptional(): bool
    {
        return $this->optional;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getSynopsis(): array
    {
        $synopsis = [
            'type' => 'assoc',
            'name' => $this->name,
            'optional' => $this->optional,
            'repeating' => false,
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

    public function getValueIsOptional(): bool
    {
        return $this->valueIsOptional;
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
        if ($this->valueIsOptional && ! $optional) {
            throw new RuntimeException(
                'Cannot set "optional" to false when "valueIsOptional" is true'
            );
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
        if (! $this->optional && $valueIsOptional) {
            throw new RuntimeException(
                'Cannot set "valueIsOptional" to true when "optional" is false'
            );
        }

        $this->valueIsOptional = $valueIsOptional;

        return $this;
    }
}
