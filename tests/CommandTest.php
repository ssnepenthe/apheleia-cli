<?php

declare(strict_types=1);

namespace ApheleiaCli\Tests;

use ApheleiaCli\Argument;
use ApheleiaCli\Command;
use ApheleiaCli\Flag;
use ApheleiaCli\Option;
use Closure;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class CommandTest extends TestCase
{
    public function testAddArgumentThrowsAfterRepeatingArgumentHasBeenAdded()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Cannot register additional arguments after a repeating argument'
        );

        $command = new Command();

        $command->addArgument(
            (new Argument('irrelevant'))
                ->setRepeating(true)
        );

        $command->addArgument(new Argument('also-irrelevant'));
    }

    public function testAddArgumentThrowswhenAddingRequiredArgumentAfterOptionalArgument()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Cannot register required argument after an optional argument'
        );

        $command = new Command();

        $command->addArgument(
            (new Argument('irrelevant'))
                ->setOptional(true)
        );

        $command->addArgument(new Argument('also-irrelevant'));
    }

    public function testAddArgumentThrowsWhenArgumentWithSameNameExists()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('parameter with this name already exists');

        $command = new Command();
        $command->addArgument(new Argument('irrelevant'));
        $command->addArgument(new Argument('irrelevant'));
    }

    public function testAddArgumentThrowsWhenOptionWithSameNameExists()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('parameter with this name already exists');

        $command = new Command();
        $command->addOption(new Option('irrelevant'));
        $command->addArgument(new Argument('irrelevant'));
    }

    public function testAddFlagThrowsWhenArgumentWithSameNameExists()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('parameter with this name already exists');

        $command = new Command();
        $command->addArgument(new Argument('irrelevant'));
        $command->addFlag(new Flag('irrelevant'));
    }

    public function testAddFlagThrowsWhenOptionWithSameNameExists()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('parameter with this name already exists');

        $command = new Command();
        $command->addOption(new Option('irrelevant'));
        $command->addFlag(new Flag('irrelevant'));
    }

    public function testAddOptionThrowsWhenArgumentWithSameNameExists()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('parameter with this name already exists');

        $command = new Command();
        $command->addArgument(new Argument('irrelevant'));
        $command->addOption(new Option('irrelevant'));
    }

    public function testAddOptionThrowsWhenOptionWithSameNameExists()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('parameter with this name already exists');

        $command = new Command();
        $command->addOption(new Option('irrelevant'));
        $command->addOption(new Option('irrelevant'));
    }

    public function testConstructorCallsConfigureMethod()
    {
        $command = new class () extends Command {
            public $configureWasCalled = false;

            public function configure(): void
            {
                $this->configureWasCalled = true;
            }
        };

        $this->assertTrue($command->configureWasCalled);
    }

    public function testGetAfterInvokeCallbackDefault()
    {
        $command = new Command();

        $this->assertNull($command->getAfterInvokeCallback());

        $command = new class () extends Command {
            public function afterInvoke()
            {
            }
        };

        $this->assertSame([$command, 'afterInvoke'], $command->getAfterInvokeCallback());
    }

    public function testGetAfterInvokeCallbackDefaultWithInvalidVisibility()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('method \'afterInvoke\' must have public visibility');

        $command = new class () extends Command {
            protected function afterInvoke()
            {
            }
        };

        $command->getAfterInvokeCallback();
    }

    public function testGetBeforeInvokeCallbackDefault()
    {
        $command = new Command();

        $this->assertNull($command->getBeforeInvokeCallback());

        $command = new class () extends Command {
            public function beforeInvoke()
            {
            }
        };

        $this->assertSame([$command, 'beforeInvoke'], $command->getBeforeInvokeCallback());
    }

    public function testGetBeforeInvokeCallbackDefaultWithInvalidVisibility()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('method \'beforeInvoke\' must have public visibility');

        $command = new class () extends Command {
            protected function beforeInvoke()
            {
            }
        };

        $command->getBeforeInvokeCallback();
    }

    public function testGetHandler()
    {
        $command = new Command();

        $command->setHandler(fn () => '');

        $this->assertInstanceOf(Closure::class, $command->getHandler());
    }

    public function testGetHandlerDefault()
    {
        $command = new class () extends Command {
            public function handle()
            {
            }
        };

        $this->assertSame([$command, 'handle'], $command->getHandler());
    }

    public function testGetHandlerThrowsWhenHandlerNotConfigured()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Handler not set for command');

        $command = (new Command())->setName('irrelevant');

        $command->getHandler();
    }

    public function testGetName()
    {
        $command = (new Command())->setName('irrelevant');

        $this->assertSame('irrelevant', $command->getName());
    }

    public function testGetNameThrowsWhenNameIsEmpty()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Command name must be non-empty string');

        $command = new class () extends Command {
            protected $name = '';
        };

        $command->getName();
    }

    public function testGetNameThrowsWhenNameIsNotString()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Command name must be non-empty string');

        $command = new class () extends Command {
            protected $name = 5;
        };

        $command->getName();
    }

    public function testGetNameWithMultipleAncestors()
    {
        $command = (new Command())->setName('command');
        $parent = (new Command())->setName('parent');
        $grandparent = (new Command())->setName('grandparent');

        $command->setParent($parent);
        $parent->setParent($grandparent);

        $this->assertSame('grandparent parent command', $command->getName());
    }

    public function testGetNameWithParent()
    {
        $command = (new Command())->setName('command');
        $parent = (new Command())->setName('parent');

        $command->setParent($parent);

        $this->assertSame('parent command', $command->getName());
    }

    public function testGetSynopsis()
    {
        $command = (new Command())->setName('irrelevant');

        $command->addArgument(new Argument('irrelevant-arg'));
        $command->addFlag(new Flag('irrelevant-flag'));
        $command->addOption(new Option('irrelevant-opt'));

        $this->assertSame([
            [
                'type' => 'positional',
                'name' => 'irrelevant-arg',
                'optional' => false,
                'repeating' => false,
            ],
            [
                'type' => 'flag',
                'name' => 'irrelevant-flag',
                'optional' => true,
                'repeating' => false,
            ],
            [
                'type' => 'assoc',
                'name' => 'irrelevant-opt',
                'optional' => true,
                'repeating' => false,
            ],
        ], $command->getSynopsis());
    }

    public function testGetSynopsisWhenCommandAcceptsArbitraryOptions()
    {
        $command = (new Command())->setName('irrelevant');

        $command->addArgument(new Argument('irrelevant-arg'));
        $command->setAcceptArbitraryOptions(true);

        $this->assertSame([
            [
                'type' => 'positional',
                'name' => 'irrelevant-arg',
                'optional' => false,
                'repeating' => false,
            ],
            [
                'type' => 'generic',
                'optional' => true,
                'repeating' => false,
            ],
        ], $command->getSynopsis());
    }

    public function testParentCanBeUnset()
    {
        $command = (new Command())->setName('command');
        $parent = (new Command())->setName('parent');

        $command->setParent($parent);

        $this->assertSame('parent command', $command->getName());

        $command->setParent(null);

        $this->assertSame('command', $command->getName());
    }
}
