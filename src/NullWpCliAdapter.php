<?php

declare(strict_types=1);

namespace ApheleiaCli;

use LogicException;

class NullWpCliAdapter implements WpCliAdapterInterface
{
    /**
     * @param callable|class-string<\WP_CLI\Dispatcher\CommandNamespace> $callable
     * @param array{before_invoke?: callable, after_invoke?: callable, shortdesc?: string, longdesc?: string, synopsis?: array|string, when?: string, is_deferred?: bool} $args
     */
    public function addCommand(string $name, $callable, array $args = []): bool
    {
        return true;
    }

    public function addWpHook(
        string $tag,
        callable $callback,
        int $priority = 10,
        int $acceptedArgs = 1
    ): void {
        //
    }

    /**
     * @return never
     */
    public function halt(int $code)
    {
        throw new LogicException('The \'halt\' method should not be called on NullWpCliAdapter');
    }
}
