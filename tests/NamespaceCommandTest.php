<?php

declare(strict_types=1);

namespace ApheleiaCli\Tests;

use ApheleiaCli\Argument;
use ApheleiaCli\Flag;
use ApheleiaCli\NamespaceCommand;
use ApheleiaCli\NamespaceIdentifier;
use ApheleiaCli\Option;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class NamespaceCommandTest extends TestCase
{
    public function testAcceptArbitraryOptions()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('cannot accept arbitrary options');

        (new NamespaceCommand('name', 'description'))->setAcceptArbitraryOptions(true);
    }

    public function testAddArgument()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot add arguments');

        (new NamespaceCommand('name', 'description'))->addArgument(new Argument('name'));
    }

    public function testAddFlag()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot add flags');

        (new NamespaceCommand('name', 'description'))->addFlag(new Flag('name'));
    }

    public function testAddOption()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot add options');

        (new NamespaceCommand('name', 'description'))->addOption(new Option('name'));
    }

    public function testDefaults()
    {
        $command = new NamespaceCommand('name', 'description');

        $this->assertFalse($command->getAcceptArbitraryOptions());
        $this->assertNull($command->getAfterInvokeCallback());
        $this->assertSame([], $command->getArguments());
        $this->assertNull($command->getBeforeInvokeCallback());
        $this->assertSame(NamespaceIdentifier::class, $command->getHandler());
        $this->assertSame([], $command->getOptions());
        $this->assertSame([], $command->getSynopsis());
        $this->assertSame(null, $command->getWhen());
    }

    public function testSetAfterInvokeCallback()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot set after-invoke callback');

        (new NamespaceCommand('name', 'description'))->setAfterInvokeCallback(fn () => '');
    }

    public function testSetBeforeInvokeCallback()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot set before-invoke callback');

        (new NamespaceCommand('name', 'description'))->setBeforeInvokeCallback(fn () => '');
    }

    public function testSetHandler()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot manually set handler');

        (new NamespaceCommand('name', 'description'))->setHandler(fn () => '');
    }

    public function testSetName()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot change name');

        (new NamespaceCommand('name', 'description'))->setName('new-name');
    }

    public function testSetWhen()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot set when');

        (new NamespaceCommand('name', 'description'))->setWhen('later');
    }
}
