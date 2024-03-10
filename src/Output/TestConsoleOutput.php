<?php

declare(strict_types=1);

namespace ApheleiaCli\Output;

class TestConsoleOutput extends ConsoleOutput
{
    protected function errorStream()
    {
        return fopen('php://memory', 'a');
    }

    protected function outputStream()
    {
        return fopen('php://memory', 'a');
    }
}
