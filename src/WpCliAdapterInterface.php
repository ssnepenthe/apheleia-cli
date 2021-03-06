<?php

declare(strict_types=1);

namespace ApheleiaCli;

interface WpCliAdapterInterface
{
    /**
     * @param callable|class-string<\WP_CLI\Dispatcher\CommandNamespace> $callable
     * @param array{before_invoke?: callable, after_invoke?: callable, shortdesc?: string, longdesc?: string, synopsis?: array|string, when?: string, is_deferred?: bool} $args
     */
    public function addCommand(string $name, $callable, array $args = []): bool;

    public function addWpHook(
        string $tag,
        callable $callback,
        int $priority = 10,
        int $acceptedArgs = 1
    ): void;

    /**
     * @return never
     */
    public function halt(int $code);
}
