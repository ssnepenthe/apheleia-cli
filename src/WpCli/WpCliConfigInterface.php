<?php

declare(strict_types=1);

namespace ApheleiaCli\WpCli;

interface WpCliConfigInterface
{
    public function debugGroup(): ?string;
    public function inColor(): bool;
    public function isDebug(): bool;
    public function isQuiet(): bool;
}
