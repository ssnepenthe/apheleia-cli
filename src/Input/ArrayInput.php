<?php

declare(strict_types=1);

namespace ApheleiaCli\Input;

class ArrayInput implements InputInterface
{
    protected $arguments;

    protected $flags;

    protected $options;

    public function __construct($arguments, $options, $flags)
    {
        $this->arguments = $arguments;
        $this->options = $options;
        $this->flags = $flags;
    }

    public function get(string $name, $default = null)
    {
        if (array_key_exists($name, $this->arguments)) {
            return $this->arguments[$name];
        }

        if (array_key_exists($name, $this->options)) {
            return $this->options[$name];
        }

        if (array_key_exists($name, $this->flags)) {
            return $this->flags[$name];
        }

        return $default;
    }

    public function getArgument(string $name, $default = null)
    {
        return $this->arguments[$name] ?? $default;
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function getFlag(string $name, $default = null)
    {
        return $this->flags[$name] ?? $default;
    }

    public function getFlags(): array
    {
        return $this->flags;
    }

    public function getOption(string $name, $default = null)
    {
        return $this->options[$name] ?? $default;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function hasArgument(string $name): bool
    {
        return array_key_exists($name, $this->arguments);
    }

    public function hasFlag(string $name): bool
    {
        return array_key_exists($name, $this->flags);
    }

    public function hasOption(string $name): bool
    {
        return array_key_exists($name, $this->options);
    }
}
