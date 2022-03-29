<?php

declare(strict_types=1);

namespace ToyWpCli;

interface WpCliAdapterInterface
{
    /**
     * @param callable|class-string<\WP_CLI\Dispatcher\CommandNamespace> $callable
     * @param array $args
     */
    public function addCommand(string $name, $callable, array $args = []): bool;
    public function addWpHook(
        string $tag,
        callable $callback,
        int $priority = 10,
        int $acceptedArgs = 1
    ): void;
}
