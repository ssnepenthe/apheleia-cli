<?php

namespace ToyWpCli;

interface CommandParserInterface
{
    public function parse(string $command): Command;
}
