<?php

declare(strict_types=1);

namespace ApheleiaCli\Output;

interface ConsoleOutputInterface extends OutputInterface
{
    public function getErrorOutput(): StreamOutput;
}
