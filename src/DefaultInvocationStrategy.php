<?php

declare(strict_types=1);

namespace ToyWpCli;

class DefaultInvocationStrategy implements InvocationStrategyInterface
{
    /**
     * @return mixed
     */
    public function call($callback)
    {
        return $callback();
    }

    /**
     * @return mixed
     */
    public function callCommandHandler(Command $command, array $args, array $assocArgs)
    {
        return ($command->getHandler())($args, $assocArgs);
    }
}
