<?php

declare(strict_types=1);

namespace ApheleiaCli;

use RuntimeException;

class NamespaceCommand extends Command
{
    protected $handler = NamespaceIdentifier::class;

    /**
     * @param non-empty-string $name
     */
    public function __construct(string $name, string $description)
    {
        $this->name = $name;
        $this->description = $description;
    }

    public function addArgument(Argument $argument): Command
    {
        throw new RuntimeException('Cannot add arguments to namespace commands');
    }

    public function addFlag(Flag $flag): Command
    {
        throw new RuntimeException('Cannot add flags to namespace commands');
    }

    public function addOption(Option $option): Command
    {
        throw new RuntimeException('Cannot add options to namespace commands');
    }

    public function setAcceptArbitraryOptions(bool $acceptArbitraryOptions = true): Command
    {
        throw new RuntimeException('Namespace commands cannot accept arbitrary options');
    }

    public function setAfterInvokeCallback(callable $afterInvokeCallback): Command
    {
        throw new RuntimeException('Cannot set after-invoke callback on namespace commands');
    }

    public function setBeforeInvokeCallback(callable $beforeInvokeCallback): Command
    {
        throw new RuntimeException('Cannot set before-invoke callback on namespace commands');
    }

    public function setHandler(callable $handler): Command
    {
        throw new RuntimeException('Cannot manually set handler on namespace commands');
    }

    public function setName(string $name): Command
    {
        throw new RuntimeException('Cannot change name after instantiation on namespace commands');
    }

    public function setWhen(string $when): Command
    {
        throw new RuntimeException('Cannot set when on namespace commands');
    }
}
