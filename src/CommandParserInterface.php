<?php

declare(strict_types=1);

namespace ToyWpCli;

interface CommandParserInterface
{
    public function parse(string $command): Command;
}
