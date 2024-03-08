<?php

declare(strict_types=1);

namespace ApheleiaCli\Tests\Invoker;

use ApheleiaCli\Invoker\PhpDiGenericInvoker;
use PHPUnit\Framework\TestCase;

// @todo InvokerInterface mocks?
class PhpDiGenericInvokerTest extends TestCase
{
    use CreatesPhpDiInvoker;

    public function testInvoke()
    {
        $count = 0;
        $callback = function () use (&$count) {
            $count++;
        };

        (new PhpDiGenericInvoker($this->createPhpDiInvoker()))->invoke($callback);

        $this->assertSame(1, $count);
    }

    public function testInvokeWithArguments()
    {
        $count = 0;
        $receivedKey = null;

        $callback = function ($key) use (&$count, &$receivedKey) {
            $count++;
            $receivedKey = $key;
        };

        $arguments = ['key' => 'value'];

        (new PhpDiGenericInvoker($this->createPhpDiInvoker()))
            ->invoke($callback, $arguments);

        $this->assertSame(1, $count);
        $this->assertSame('value', $receivedKey);
    }
}
