<?php

declare(strict_types=1);

namespace ApheleiaCli;

use WP_CLI;

class WpCliAdapter implements WpCliAdapterInterface
{
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
}
