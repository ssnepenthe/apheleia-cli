<?php

declare(strict_types=1);

namespace ApheleiaCli\Tests\Output;

use ApheleiaCli\Output\StreamOutput;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class StreamOutputTest extends TestCase
{
    public function testConstructThrowsForInvalidStream()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('must be a stream');

        new StreamOutput('stringval');
    }

    public function testWrite()
    {
        $output = new StreamOutput($this->createInMemoryStream());

        $output->write('apples', true);
        $output->write('bananas');

        rewind($output->getStream());

        $this->assertSame('apples' . PHP_EOL . 'bananas', stream_get_contents($output->getStream()));
    }

    public function testWriteln()
    {
        $output = new StreamOutput($this->createInMemoryStream());

        $output->writeln('apples');

        rewind($output->getStream());

        $this->assertSame('apples' . PHP_EOL, stream_get_contents($output->getStream()));
    }

    public function testWriteWhenQuiet()
    {
        $output = new StreamOutput($this->createInMemoryStream(), true);

        $output->write('apples');

        rewind($output->getStream());

        $this->assertSame('', stream_get_contents($output->getStream()));
    }

    public function testWriteWithReadOnlyStream()
    {
        $output = new StreamOutput($this->createInMemoryStream('r'));

        $output->write('apples');

        rewind($output->getStream());

        $this->assertSame('', stream_get_contents($output->getStream()));
    }

    private function createInMemoryStream($mode = 'a')
    {
        return fopen('php://memory', $mode);
    }
}
