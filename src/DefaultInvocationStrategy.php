<?php

declare(strict_types=1);

namespace ToyWpCli;

class DefaultInvocationStrategy implements InvocationStrategyInterface
{
    public function call($callback)
    {
        return $callback();
    }

    public function callCommandHandler(Command $command, array $args, array $assocArgs)
    {
        return ($command->getHandler())($args, $assocArgs);
    }
}
