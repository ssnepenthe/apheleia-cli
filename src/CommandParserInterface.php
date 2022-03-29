<?php

declare(strict_types=1);

namespace ApheleiaCli;

interface CommandParserInterface
{
    public function parse(string $command): Command;
}
