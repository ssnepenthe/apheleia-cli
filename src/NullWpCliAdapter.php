<?php

declare(strict_types=1);

namespace ApheleiaCli;

use LogicException;
use WP_CLI;

class NullWpCliAdapter implements WpCliAdapterInterface
{
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
