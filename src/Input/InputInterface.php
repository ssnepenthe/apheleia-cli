<?php

declare(strict_types=1);

namespace ApheleiaCli\Input;

interface InputInterface
{
    public function get(string $name, $default = null);
    public function getArgument(string $name, $default = null);
    public function getArguments(): array;
    public function getFlag(string $name, $default = null);
    public function getFlags(): array;
    public function getOption(string $name, $default = null);
    public function getOptions(): array;
    public function hasArgument(string $name): bool;
    public function hasFlag(string $name): bool;
    public function hasOption(string $name): bool;
}
