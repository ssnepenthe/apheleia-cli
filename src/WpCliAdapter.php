<?php

declare(strict_types=1);

namespace ApheleiaCli;

use WP_CLI;

class WpCliAdapter implements WpCliAdapterInterface
{
    /**
     * @param callable|class-string<\WP_CLI\Dispatcher\CommandNamespace> $callable
     * @param array{before_invoke?: callable, after_invoke?: callable, shortdesc?: string, longdesc?: string, synopsis?: array|string, when?: string, is_deferred?: bool} $args
     */
    public function addCommand(string $name, $callable, array $args = []): bool
    {
        return WP_CLI::add_command($name, $callable, $args);
    }

    public function addWpHook(
        string $tag,
        callable $callback,
        int $priority = 10,
        int $acceptedArgs = 1
    ): void {
        WP_CLI::add_wp_hook($tag, $callback, $priority, $acceptedArgs);
    }

    /**
     * @return never
     */
    public function halt(int $code)
    {
        WP_CLI::halt($code);
    }

    public function isQuiet(): bool
    {
        return WP_CLI::get_runner()->config['quiet'];
    }
}
