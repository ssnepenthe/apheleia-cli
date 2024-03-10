<?php

declare(strict_types=1);

namespace ApheleiaCli\Tests\Output;

use ApheleiaCli\Output\TestConsoleOutput;
use ApheleiaCli\Output\WpCliLoggerStandIn;
use cli\Colors;
use PHPUnit\Framework\TestCase;

class WpCliLoggerStandInTest extends TestCase
{
    public function setUp(): void
    {
        Colors::clearStringCache();
    }

    public function tearDown(): void
    {
        Colors::clearStringCache();
    }

    public function testColors()
    {
        $output = new TestConsoleOutput();
        $logger = new WpCliLoggerStandIn($output, true, true);

        $logger->debug('apples');
        $logger->error('bananas');
        $logger->info('cherries');
        $logger->success('dates');
        $logger->warning('elderberry');
        $logger->errorMultiLine([
            'fig',
            'grape',
        ]);

        rewind($output->getStream());
        rewind($output->getErrorOutput()->getStream());

        $stdout = explode(PHP_EOL, trim(stream_get_contents($output->getStream())));
        $stderr = explode(PHP_EOL, trim(stream_get_contents($output->getErrorOutput()->getStream())));

        // Styling should be as if you were using WP-CLI logger directly.
        $this->assertCount(2, $stdout);
        $this->assertSame('cherries', $stdout[0]);
        $this->assertStringStartsWith("\033[32;1mSuccess:\033[0m", $stdout[1]);

        $this->assertCount(8, $stderr);
        $this->assertStringStartsWith("\033[34;1mDebug:\033[0m", $stderr[0]);
        $this->assertStringStartsWith("\033[31;1mError:\033[0m", $stderr[1]);
        $this->assertStringStartsWith("\033[36;1mWarning:\033[0m", $stderr[2]);
        $this->assertSame('', $stderr[3]);
        $this->assertSame("\t\033[37m\033[41m       \033[0m", $stderr[4]);
        $this->assertSame("\t\033[37m\033[41m fig   \033[0m", $stderr[5]);
        $this->assertSame("\t\033[37m\033[41m grape \033[0m", $stderr[6]);
        $this->assertSame("\t\033[37m\033[41m       \033[0m", $stderr[7]);
    }

    public function testDebugDefault()
    {
        $output = new TestConsoleOutput();
        $logger = new WpCliLoggerStandIn($output, false);

        $logger->debug('apples');

        rewind($output->getStream());
        rewind($output->getErrorOutput()->getStream());

        $stdout = stream_get_contents($output->getStream());
        $stderr = stream_get_contents($output->getErrorOutput()->getStream());

        $this->assertSame('', $stdout);
        $this->assertSame('', $stderr);
    }

    public function testDebugWithDebugEnabled()
    {
        $output = new TestConsoleOutput();
        $logger = new WpCliLoggerStandIn($output, false, true);

        $logger->debug('apples');
        $logger->debug('bananas', 'groupname');

        rewind($output->getStream());
        rewind($output->getErrorOutput()->getStream());

        $stdout = stream_get_contents($output->getStream());
        $stderr = stream_get_contents($output->getErrorOutput()->getStream());

        $this->assertSame('', $stdout);

        $this->assertMatchesRegularExpression('/Debug: apples \([\d\.]+s\)/', $stderr);
        $this->assertMatchesRegularExpression('/Debug \(groupname\): bananas \([\d\.]+s\)/', $stderr);
    }

    public function testDebugWithDebugSetToSpecificGroup()
    {
        $output = new TestConsoleOutput();
        $logger = new WpCliLoggerStandIn($output, false, true, 'groupname');

        $logger->debug('apples');
        $logger->debug('bananas', 'groupname');
        $logger->debug('cherries', 'wronggroup');

        rewind($output->getStream());
        rewind($output->getErrorOutput()->getStream());

        $stdout = stream_get_contents($output->getStream());
        $stderr = stream_get_contents($output->getErrorOutput()->getStream());

        $this->assertSame('', $stdout);

        // No group or wrong group should be discarded.
        $this->assertStringNotContainsString('apples', $stderr);
        $this->assertStringNotContainsString('cherries', $stderr);

        // Group name is not included in message when it is the only debug group.
        $this->assertStringNotContainsString('groupname', $stderr);

        $this->assertMatchesRegularExpression('/Debug: bananas \([\d\.]+s\)/', $stderr);
    }

    public function testError()
    {
        $output = new TestConsoleOutput();
        $logger = new WpCliLoggerStandIn($output, false);

        $logger->error('apples');

        rewind($output->getStream());
        rewind($output->getErrorOutput()->getStream());

        $stdout = stream_get_contents($output->getStream());
        $stderr = stream_get_contents($output->getErrorOutput()->getStream());

        $this->assertSame('', $stdout);

        $this->assertSame('Error: apples' . PHP_EOL, $stderr);
    }

    public function testErrorMultiLine()
    {
        $output = new TestConsoleOutput();
        $logger = new WpCliLoggerStandIn($output, false);

        $logger->errorMultiLine([
            'apples',
            'bananas',
            'cherries',
        ]);

        rewind($output->getStream());
        rewind($output->getErrorOutput()->getStream());

        $stdout = stream_get_contents($output->getStream());
        $stderr = stream_get_contents($output->getErrorOutput()->getStream());

        $this->assertSame('', $stdout);

        $expectedStdErr = implode(PHP_EOL, [
            "",
            "\t          ",
            "\t apples   ",
            "\t bananas  ",
            "\t cherries ",
            "\t          ",
            "",
            "",
        ]);
        $this->assertSame($expectedStdErr, $stderr);
    }

    public function testInfo()
    {
        $output = new TestConsoleOutput();
        $logger = new WpCliLoggerStandIn($output, false);

        $logger->info('apples');

        rewind($output->getStream());
        rewind($output->getErrorOutput()->getStream());

        $stdout = stream_get_contents($output->getStream());
        $stderr = stream_get_contents($output->getErrorOutput()->getStream());

        $this->assertSame('apples' . PHP_EOL, $stdout);

        $this->assertSame('', $stderr);
    }

    // public function testStringifyErrorMessage()
    // {
    //     // @todo
    // }

    public function testSuccess()
    {
        $output = new TestConsoleOutput();
        $logger = new WpCliLoggerStandIn($output, false);

        $logger->success('apples');

        rewind($output->getStream());
        rewind($output->getErrorOutput()->getStream());

        $stdout = stream_get_contents($output->getStream());
        $stderr = stream_get_contents($output->getErrorOutput()->getStream());

        $this->assertSame('Success: apples' . PHP_EOL, $stdout);

        $this->assertSame('', $stderr);
    }

    public function testWarning()
    {
        $output = new TestConsoleOutput();
        $logger = new WpCliLoggerStandIn($output, false);

        $logger->warning('apples');

        rewind($output->getStream());
        rewind($output->getErrorOutput()->getStream());

        $stdout = stream_get_contents($output->getStream());
        $stderr = stream_get_contents($output->getErrorOutput()->getStream());

        $this->assertSame('', $stdout);

        $this->assertSame('Warning: apples' . PHP_EOL, $stderr);
    }
}
