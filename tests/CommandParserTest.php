<?php

declare(strict_types=1);

namespace ApheleiaCli\Tests;

use ApheleiaCli\CommandParser;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class CommandParserTest extends TestCase
{
    public function provideTestParseArguments()
    {
        // Arguments.
        yield ['command <regular>', [
            [
                'type' => 'positional',
                'name' => 'regular',
                'optional' => false,
                'repeating' => false,
            ],
        ]];

        yield ['command <repeating>...', [
            [
                'type' => 'positional',
                'name' => 'repeating',
                'optional' => false,
                'repeating' => true,
            ],
        ]];

        yield ['command [<optional>]', [
            [
                'type' => 'positional',
                'name' => 'optional',
                'optional' => true,
                'repeating' => false,
            ],
        ]];

        yield ['command [<optional-repeating>...]', [
            [
                'type' => 'positional',
                'name' => 'optional-repeating',
                'optional' => true,
                'repeating' => true,
            ],
        ]];

        // Options
        yield ['command --required=<required>', [
            [
                'type' => 'assoc',
                'name' => 'required',
                'optional' => false,
                'repeating' => false,
            ],
        ]];

        yield ['command [--optional=<optional>]', [
            [
                'type' => 'assoc',
                'name' => 'optional',
                'optional' => true,
                'repeating' => false,
            ],
        ]];

        yield ['command [--dbl-optional[=<dbl-optional>]]', [
            [
                'type' => 'assoc',
                'name' => 'dbl-optional',
                'optional' => true,
                'repeating' => false,
                'value' => [
                    'optional' => true,
                    'name' => 'dbl-optional',
                ],
            ],
        ]];

        // Flags
        yield ['command [--optional]', [
            [
                'type' => 'flag',
                'name' => 'optional',
                'optional' => true,
                'repeating' => false,
            ]
        ]];

        // Generic
        yield ['command [--<field>=<value>]', [
            [
                'type' => 'generic',
                'optional' => true,
                'repeating' => false,

            ]
        ]];
    }

    public function provideTestParseWithUnrecognizedToken()
    {
        // Optional arguments must have matching square brackets
        yield ['[<argument>'];
        yield ['<argument>]'];

        // Repeating arguments are defined by three periods
        yield ['<argument>..'];
        yield ['<argument>....'];
        yield ['[<argument>..]'];
        yield ['[<argument>....]'];

        // Optional options must have matching square brackets
        yield ['[--option=<option>'];
        yield ['--option=<option>]'];

        // Option names can't start with =, [, ], <, >
        yield ['--=option=<=option>'];
        yield ['--[option=<[option>'];
        yield ['--]option=<]option>'];
        yield ['--<option=<<option>'];
        yield ['-->option=<>option>'];

        // Optional option value must have matching square brackets.
        yield ['[--option[=<option>]'];
        yield ['[--option=<option>]]'];

        // Option value name must be at least one character long
        yield ['--option=<>'];

        // Flags are always optional
        yield ['--flag'];

        // Flag names can't start with =, [, ], <, >
        yield ['--=flag'];
        yield ['--[flag'];
        yield ['--]flag'];
        yield ['--<flag'];
        yield ['-->flag'];

        // Completely unrecognized
        yield ['{argument}'];
    }

    /** @dataProvider provideTestParseArguments */
    public function testParseArguments($input, $output)
    {
        $parser = new CommandParser();

        $this->assertSame($output, $parser->parse($input)->getSynopsis());
    }

    public function testParseNames()
    {
        $parser = new CommandParser();

        // Name and namespace.
        $this->assertSame('command', $parser->parse('command')->getName());
        $this->assertSame('parent command', $parser->parse('parent command')->getName());
        $this->assertSame(
            'grandparent parent command',
            $parser->parse('grandparent parent command')->getName()
        );
    }

    public function testParseWithEmptyInput()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot parse empty command string');

        (new CommandParser())->parse('');
    }

    public function testParseWithNoName()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Command string must start with the command name');

        (new CommandParser())->parse('<argument> --option=<option>');
    }

    /** @dataProvider provideTestParseWithUnrecognizedToken */
    public function testParseWithUnrecognizedToken($token)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Unrecognized token '{$token}'");

        (new CommandParser())->parse("command {$token}");
    }
}
