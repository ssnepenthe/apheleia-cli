<?php

declare(strict_types=1);

namespace ApheleiaCli\Input;

interface InputInterface
{
    /**
     * @param array<string, string>|bool|string|string[]|null $default
     * @return array<string, string>|bool|string|string[]|null
     */
    public function get(string $name, $default = null);

    /**
     * @param string|string[]|null $default
     * @return string|string[]|null
     */
    public function getArgument(string $name, $default = null);

    /**
     * @return array<string, string|string[]>
     */
    public function getArguments(): array;

    public function getFlag(string $name, ?bool $default = null): ?bool;

    /**
     * @return array<string, bool>
     */
    public function getFlags(): array;

    /**
     * @param array<string, string>|string|null $default
     * @return array<string, string>|string|null
     */
    public function getOption(string $name, $default = null);

    /**
     * @return array<string, array<string, string>|string>
     */
    public function getOptions(): array;

    /**
     * @return string[]
     */
    public function getWpCliArguments(): array;

    /**
     * @return array<string, bool|string>
     */
    public function getWpCliAssociativeArguments(): array;

    public function hasArgument(string $name): bool;

    public function hasFlag(string $name): bool;

    public function hasOption(string $name): bool;
}
