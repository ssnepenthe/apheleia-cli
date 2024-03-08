<?php

declare(strict_types=1);

namespace ApheleiaCli\Tests\Invoker;

use ApheleiaCli\Invoker\InvokerFactory;
use Invoker\InvokerInterface;
use ReflectionMethod;

trait CreatesPhpDiInvoker
{
    public function createPhpDiInvoker(): InvokerInterface
    {
        $ref = new ReflectionMethod(InvokerFactory::class, 'createPhpDiInvoker');
        $ref->setAccessible(true);

        return $ref->invoke(new InvokerFactory());
    }
}
