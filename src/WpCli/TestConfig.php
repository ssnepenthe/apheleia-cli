<?php

declare(strict_types=1);

namespace ApheleiaCli\WpCli;

class TestConfig implements WpCliConfigInterface
{
    protected $debugGroup;

    protected $inColor;

    protected $isDebug;
    protected $isQuiet;

    public function __construct(
        bool $isQuiet = false,
        bool $inColor = false,
        bool $debug = false,
        ?string $debugGroup = null
    ) {
        $this->isQuiet = $isQuiet;
        $this->inColor = $inColor;
        $this->isDebug = $debug;
        $this->debugGroup = $debugGroup;
    }

    public function debugGroup(): ?string
    {
        return $this->debugGroup;
    }

    public function inColor(): bool
    {
        return $this->inColor;
    }

    public function isDebug(): bool
    {
        return $this->isDebug;
    }

    public function isQuiet(): bool
    {
        return $this->isQuiet;
    }
}
