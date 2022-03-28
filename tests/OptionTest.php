<?php

declare(strict_types=1);

namespace ToyWpCli\Tests;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use ToyWpCli\Option;

class OptionTest extends TestCase
{
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

    public function testSetOptionalWhenValueIsOptionalTrue()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Cannot set "optional" to false when "valueIsOptional" is true'
        );

        $option = new Option('some-name');

        $option->setValueIsOptional(true);
        $option->setOptional(false);
    }

    public function testSetValueIsOptionalWhenOptionalFalse()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Cannot set "valueIsOptional" to true when "optional" is false'
        );

        $option = new Option('some-name');

        $option->setOptional(false);
        $option->setValueIsOptional(true);
    }
}
