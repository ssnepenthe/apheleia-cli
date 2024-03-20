<?php

declare(strict_types=1);

namespace ApheleiaCli\Tests\Input;

use ApheleiaCli\Input\ArrayInput;
use PHPUnit\Framework\TestCase;

class ArrayInputTest extends TestCase
{
    public function testArguments()
    {
        $arguments = [
            'argone' => 'argvalone',
            'argtwo' => 'argvaltwo',
        ];
        $options = [
            'optone' => 'optvalone',
        ];
        $flags = [
            'flagone' => true,
        ];

        $input = new ArrayInput($arguments, $options, $flags);

        $this->assertSame($arguments, $input->getArguments());

        $this->assertTrue($input->hasArgument('argtwo'));
        $this->assertSame('argvaltwo', $input->getArgument('argtwo'));

        $this->assertFalse($input->hasArgument('argthree'));
        $this->assertNull($input->getArgument('argthree'));
    }

    public function testFlags()
    {
        $arguments = [
            'argone' => 'argvalone',
        ];
        $options = [
            'optone' => 'optvalone',
        ];
        $flags = [
            'flagone' => true,
            'flagtwo' => false,
        ];

        $input = new ArrayInput($arguments, $options, $flags);

        $this->assertSame($flags, $input->getFlags());

        $this->assertTrue($input->hasFlag('flagtwo'));
        $this->assertFalse($input->getFlag('flagtwo'));

        $this->assertFalse($input->hasFlag('flagthree'));
        $this->assertNull($input->getFlag('flagthree'));
    }

    public function testGet()
    {
        $arguments = [
            'argone' => 'argvalone',
        ];
        $options = [
            'optone' => 'optvalone',
        ];
        $flags = [
            'flagone' => true,
        ];

        $input = new ArrayInput($arguments, $options, $flags);

        $this->assertSame('argvalone', $input->get('argone'));
        $this->assertSame('default', $input->get('argtwo', 'default'));

        $this->assertSame('optvalone', $input->get('optone'));
        $this->assertSame('default', $input->get('opttwo', 'default'));

        $this->assertTrue($input->get('flagone'));
        $this->assertFalse($input->get('flagtwo', false));
    }

    public function testOptions()
    {
        $arguments = [
            'argone' => 'argvalone',
        ];
        $options = [
            'optone' => 'optvalone',
            'opttwo' => 'optvaltwo',
        ];
        $flags = [
            'flagone' => true,
        ];

        $input = new ArrayInput($arguments, $options, $flags);

        $this->assertSame($options, $input->getOptions());

        $this->assertTrue($input->hasOption('opttwo'));
        $this->assertSame('optvaltwo', $input->getOption('opttwo'));

        $this->assertFalse($input->hasOption('optthree'));
        $this->assertNull($input->getOption('optthree'));
    }

    public function testWpCliCompatibility()
    {
        $arguments = [
            'argone' => 'argvalone',
            'argtwo' => ['argvaltwoone', 'argvaltwotwo'],
        ];
        $options = [
            'optone' => 'optvalone',
            'arbitraryOptions' => [
                'opttwo' => 'optvaltwo',
                'optthree' => 'optvalthree',
            ],
        ];
        $flags = [
            'flagone' => true,
            'flagtwo' => false,
        ];

        $input = new ArrayInput($arguments, $options, $flags);

        $this->assertSame(['argvalone', 'argvaltwoone', 'argvaltwotwo'], $input->getWpCliArguments());
        $this->assertSame([
            'optone' => 'optvalone',
            'opttwo' => 'optvaltwo',
            'optthree' => 'optvalthree',
            'flagone' => true,
            'flagtwo' => false,
        ], $input->getWpCliAssociativeArguments());
    }
}
