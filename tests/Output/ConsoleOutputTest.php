<?php

declare(strict_types=1);

namespace ApheleiaCli\Tests\Output;

use ApheleiaCli\Output\TestConsoleOutput;
use PHPUnit\Framework\TestCase;

class ConsoleOutputTest extends TestCase
{
    public function testErrorOutputIsNeverQuiet()
    {
        $output = new TestConsoleOutput(true);

        $output->write('stdout');
        $output->getErrorOutput()->write('stderr');

        rewind($output->getStream());
        rewind($output->getErrorOutput()->getStream());

        $this->assertSame('', stream_get_contents($output->getStream()));
        $this->assertSame('stderr', stream_get_contents($output->getErrorOutput()->getStream()));
    }
}
