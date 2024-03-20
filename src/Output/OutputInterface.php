<?php

declare(strict_types=1);

namespace ApheleiaCli\Output;

interface OutputInterface
{
    public function write(string $message, bool $newline = false): void;

    public function writeln(string $message): void;
}
