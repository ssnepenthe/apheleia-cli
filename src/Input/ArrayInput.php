<?php

declare(strict_types=1);

namespace ApheleiaCli\Input;

class ArrayInput implements InputInterface
{
    /**
     * @var array<string, string|string[]>
     */
    protected $arguments;

    /**
     * @var array<string, bool>
     */
    protected $flags;

    /**
     * @var array<string, array<string, string>|string>
     */
    protected $options;

    /**
     * @param array<string, string|string[]> $arguments
     * @param array<string, array<string, string>|string> $options
     * @param array<string, bool> $flags
     */
    public function __construct(array $arguments, array $options, array $flags)
    {
        $this->arguments = $arguments;
        $this->options = $options;
        $this->flags = $flags;
    }

    /**
     * @param array<string, string>|bool|string|string[]|null $default
     * @return array<string, string>|bool|string|string[]|null
     */
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

    /**
     * @param string|string[]|null $default
     * @return string|string[]|null
     */
    public function getArgument(string $name, $default = null)
    {
        return $this->arguments[$name] ?? $default;
    }

    /**
     * @return array<string, string|string[]>
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function getFlag(string $name, ?bool $default = null): ?bool
    {
        return $this->flags[$name] ?? $default;
    }

    /**
     * @return array<string, bool>
     */
    public function getFlags(): array
    {
        return $this->flags;
    }

    /**
     * @param array<string, string>|string|null $default
     * @return array<string, string>|string|null
     */
    public function getOption(string $name, $default = null)
    {
        return $this->options[$name] ?? $default;
    }

    /**
     * @return array<string, array<string, string>|string>
     */
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
