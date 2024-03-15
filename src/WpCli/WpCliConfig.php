<?php

declare(strict_types=1);

namespace ApheleiaCli\WpCli;

use WP_CLI;

class WpCliConfig implements WpCliConfigInterface
{
    public function debugGroup(): ?string
    {
        $debug = WP_CLI::get_runner()->config['debug'];

        return is_string($debug) ? $debug : null;
    }

    public function inColor(): bool
    {
        return (bool) WP_CLI::get_runner()->in_color();
    }

    public function isDebug(): bool
    {
        return false !== WP_CLI::get_runner()->config['debug'];
    }

    public function isQuiet(): bool
    {
        return (bool) WP_CLI::get_runner()->config['quiet'];
    }
}
