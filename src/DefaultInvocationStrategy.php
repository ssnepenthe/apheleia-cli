<?php

declare(strict_types=1);

namespace ApheleiaCli;

class DefaultInvocationStrategy implements InvocationStrategyInterface
{
    /**
     * @return mixed
     */
    public function call(callable $callback, array $arguments = [])
    {
        return $callback($arguments);
    }

    /**
     * @return mixed
     */
    public function callCommandHandler(Command $command, array $arguments = [])
    {
        $args = $assocArgs = [];

        if (isset($arguments['args']) && is_array($arguments['args'])) {
            $args = $arguments['args'];
        }

        if (isset($arguments['assocArgs']) && is_array($arguments['assocArgs'])) {
            $assocArgs = $arguments['assocArgs'];
        }

        return ($command->getHandler())($args, $assocArgs, $arguments);
    }
}
