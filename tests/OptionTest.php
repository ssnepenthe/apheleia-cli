<?php

declare(strict_types=1);

namespace ApheleiaCli\Tests;

use ApheleiaCli\Option;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class OptionTest extends TestCase
{
    public function testConstructWithInvalidName()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid option name');

        new Option('!@#');
    }

    public function testGetSynopsis()
    {
        $option = new Option('some-name');

        $this->assertSame([
            'type' => 'assoc',
            'name' => 'some-name',
            'optional' => true,
            'repeating' => false,
        ], $option->getSynopsis());
    }

    public function testGetSynopsisWhenOptionIsRequiredAndValueIsOptional()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Required option \'some-name\' cannot have an optional value'
        );

        $option = new Option('some-name');

        $option->setValueIsOptional(true);
        $option->setOptional(false);

        $option->getSynopsis();
    }

    public function testGetSynopsisWhenValueIsOptional()
    {
        $option = new Option('some-name');

        $option->setValueIsOptional(true);

        $this->assertSame([
            'type' => 'assoc',
            'name' => 'some-name',
            'optional' => true,
            'repeating' => false,
            'value' => [
                'optional' => true,
                'name' => 'some-name',
            ],
        ], $option->getSynopsis());
    }

    public function testGetSynopsisWithNonDefaultSettings()
    {
        $option = new Option('some-name');

        $option->setDefault('Apple');
        $option->setDescription('Just a fruit');
        $option->setOptional(false);
        $option->setOptions('one', 'two', 'three');

        $this->assertSame([
            'type' => 'assoc',
            'name' => 'some-name',
            'optional' => false,
            'repeating' => false,
            'description' => 'Just a fruit',
            'default' => 'Apple',
            'options' => ['one', 'two', 'three'],
        ], $option->getSynopsis());
    }

    public function testIsValidName()
    {
        $this->assertTrue(Option::isValidName('abc123-_'));
        $this->assertFalse(Option::isValidName('abc!123'));
        $this->assertFalse(Option::isValidName('!@#'));
    }
}
