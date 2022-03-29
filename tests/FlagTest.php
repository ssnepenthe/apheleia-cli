<?php

declare(strict_types=1);

namespace ApheleiaCli\Tests;

use ApheleiaCli\Flag;
use PHPUnit\Framework\TestCase;

class FlagTest extends TestCase
{
    public function testGetSynopsis()
    {
        $flag = new Flag('some-name');

        $this->assertSame([
            'type' => 'flag',
            'name' => 'some-name',
            'optional' => true,
            'repeating' => false,
        ], $flag->getSynopsis());
    }

    public function testGetSynopsisWithNonDefaultSettings()
    {
        $flag = new Flag('some-name');

        $flag->setDescription('Description goes here...');

        $this->assertSame([
            'type' => 'flag',
            'name' => 'some-name',
            'optional' => true,
            'repeating' => false,
            'description' => 'Description goes here...',
        ], $flag->getSynopsis());
    }
}
