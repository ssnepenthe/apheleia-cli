<?php

declare(strict_types=1);

namespace ApheleiaCli\Tests\Input;

use ApheleiaCli\Argument;
use ApheleiaCli\Command;
use ApheleiaCli\Flag;
use ApheleiaCli\Input\WpCliInput;
use ApheleiaCli\Option;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class WpCliInputTest extends TestCase
{
    public function testArgumentProcessing()
    {
        $command = (new Command())
            ->setName('irrelevant')
            ->addArgument(new Argument('arg-one'))
            ->addArgument(new Argument('arg-two'));

        $input = new WpCliInput(['apple', 'banana'], [], $command);

        $this->assertSame([
            'arg-one' => 'apple',
            'arg-two' => 'banana',
        ], $input->getArguments());
        $this->assertSame([], $input->getOptions());
        $this->assertSame([], $input->getFlags());
    }

    public function testArgumentProcessingWhenWpCliProvidesTooFewArgs()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Missing required argument');

        $command = (new Command())
            ->setName('irrelevant')
            ->addArgument(new Argument('arg-one'))
            ->addArgument(new Argument('arg-two'));

        new WpCliInput(['apple'], [], $command);
    }

    public function testArgumentProcessingWhenWpCliProvidesTooManyArgs()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Too many args');

        $command = (new Command())
            ->setName('irrelevant')
            ->addArgument(new Argument('arg-one'));

        new WpCliInput(['apple', 'banana'], [], $command);
    }

    public function testArgumentProcessingWithOptionalArgs()
    {
        $command = (new Command())
            ->setName('irrelevant')
            ->addArgument(new Argument('arg-one'))
            ->addArgument(
                (new Argument('arg-two'))
                    ->setOptional(true)
            );

        $input = new WpCliInput(['apple'], [], $command);

        $this->assertSame(['arg-one' => 'apple'], $input->getArguments());
        $this->assertSame([], $input->getOptions());
        $this->assertSame([], $input->getFlags());
    }

    public function testArgumentProcessingWithRepeatingArg()
    {
        $command = (new Command())
            ->setName('irrelevant')
            ->addArgument(new Argument('arg-one'))
            ->addArgument(
                (new Argument('arg-two'))
                    ->setRepeating(true)
            );

        $input = new WpCliInput(['apple', 'banana', 'cherry'], [], $command);

        $this->assertSame([
            'arg-one' => 'apple',
            'arg-two' => ['banana', 'cherry'],
        ], $input->getArguments());
        $this->assertSame([], $input->getOptions());
        $this->assertSame([], $input->getFlags());
    }

    public function testFlagProcessing()
    {
        $command = (new Command())
            ->setName('irrelevant')
            ->addFlag(new Flag('flag-one'));

        $input = new WpCliInput([], ['flag-one' => true], $command);

        $this->assertSame([], $input->getArguments());
        $this->assertSame([], $input->getOptions());
        $this->assertSame(['flag-one' => true], $input->getFlags());
    }

    public function testOptionProcessing()
    {
        $command = (new Command())
            ->setName('irrelevant')
            ->addOption(new Option('opt-one'))
            ->addOption(
                (new Option('opt-two'))
                    ->setOptional(false)
            )
            ->setAcceptArbitraryOptions(true);

        $input = new WpCliInput(
            [],
            ['opt-two' => 'apple', 'the' => 'rest', 'goes' => 'to', 'arbitrary' => 'options'],
            $command
        );

        $this->assertSame([], $input->getArguments());
        $this->assertSame([
            'opt-two' => 'apple',
            'arbitraryOptions' => ['the' => 'rest', 'goes' => 'to', 'arbitrary' => 'options']
        ], $input->getOptions());
        $this->assertSame([], $input->getFlags());
    }

    public function testOptionProcessingWhenWpCliProvidesTooFewOptions()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Missing required option');

        $command = (new Command())
            ->setName('irrelevant')
            ->addOption(
                (new Option('opt-one'))
                    ->setOptional(false)
            )
            ->addOption(
                (new Option('opt-two'))
                    ->setOptional(false)
            );

        new WpCliInput([], ['opt-one' => 'apple'], $command);
    }

    public function testOptionProcessingWhenWpCliProvidesTooManyOptions()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Too many options');

        $command = (new Command())
            ->setName('irrelevant')
            ->addOption(
                (new Option('opt-one'))
                    ->setOptional(false)
            );

        new WpCliInput([], ['opt-one' => 'apple', 'opt-two' => 'banana'], $command);
    }
    public function testRawArgsAreUnmodified()
    {
        $command = (new Command())
            ->setName('irrelevant')
            ->addArgument(new Argument('arg-one'))
            ->addArgument(
                (new Argument('arg-two'))
                    ->setRepeating(true)
            )
            ->addOption(new Option('opt-one'))
            ->addFlag(new Flag('flag-one'))
            ->setAcceptArbitraryOptions(true);

        $args = ['apple', 'banana', 'cherry'];
        $assocArgs = [
            'opt-one' => 'date',
            'the' => 'rest',
            'goes' => 'to',
            'arbitrary' => 'options',
            'flag-one' => true
        ];

        $input = new WpCliInput($args, $assocArgs, $command);

        $this->assertSame($args, $input->getRawArgs());
        $this->assertSame($assocArgs, $input->getRawAssocArgs());

        // Sanity...
        $this->assertNotSame($args, $input->getArguments());
        $this->assertNotSame($assocArgs, $input->getOptions());
        $this->assertNotSame($assocArgs, $input->getFlags());
    }
}
