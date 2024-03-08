<?php

declare(strict_types=1);

namespace ApheleiaCli\Tests\Invoker;

use ApheleiaCli\Invoker\DefaultGenericInvoker;
use PHPUnit\Framework\TestCase;

class DefaultGenericInvokerTest extends TestCase
{
    public function testInvoke()
    {
        $count = 0;
        $callback = function () use (&$count) {
            $count++;
        };

        (new DefaultGenericInvoker())->invoke($callback);

        $this->assertSame(1, $count);
    }

    public function testInvokerWithArguments()
    {
        $count = 0;
        $receivedContext = [];
        $callback = function ($context) use (&$count, &$receivedContext) {
            $count++;
            $receivedContext = $context;
        };

        (new DefaultGenericInvoker())
            ->invoke($callback, $context = ['some' => 'context']);

        $this->assertSame(1, $count);
        $this->assertSame($context, $receivedContext);
    }
}
