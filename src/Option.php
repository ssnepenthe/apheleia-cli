<?php

declare(strict_types=1);

namespace ApheleiaCli;

use RuntimeException;

class Option
{
    /**
     * @var string|null
     */
    protected $default;

    /**
     * @var string|null
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
     * @var list<string>
     */
    protected $options = [];

    /**
     * @var bool
     */
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

    /**
     * @return list<string>
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @return array{type: 'assoc', name: string, optional: bool, repeating: bool, description?: string, default?: string, options?: list<string>, value?: array{optional: true, name: string}}
     */
    public function getSynopsis(): array
    {
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
        if ($this->valueIsOptional && ! $optional) {
            throw new RuntimeException(
                'Cannot set "optional" to false when "valueIsOptional" is true'
            );
        }

        $this->optional = $optional;

        return $this;
    }

    public function setOptions(string ...$options): self
    {
        $this->options = $options;

        return $this;
    }

    public function setValueIsOptional(bool $valueIsOptional): self
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
