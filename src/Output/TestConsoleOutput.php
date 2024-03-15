<?php

declare(strict_types=1);

namespace ApheleiaCli\Output;

class TestConsoleOutput extends ConsoleOutput
{
    /**
     * @return resource
     */
    protected function errorStream()
    {
        return fopen('php://memory', 'a');
    }

    /**
     * @return resource
     */
    protected function outputStream()
    {
        return fopen('php://memory', 'a');
    }
}
