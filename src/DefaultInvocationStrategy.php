<?php

declare(strict_types=1);

namespace ApheleiaCli;

class DefaultInvocationStrategy extends AbstractInvocationStrategy
{
    /**
     * @return mixed
     */
    public function call(callable $callback)
    {
        return $callback($this->context);
    }

    /**
     * @return mixed
     */
    public function callCommandHandler(Command $command)
    {
        $args = $assocArgs = [];

        if (isset($this->context['args']) && is_array($this->context['args'])) {
            $args = $this->context['args'];
        }

        if (isset($this->context['assocArgs']) && is_array($this->context['assocArgs'])) {
            $assocArgs = $this->context['assocArgs'];
        }

        return ($command->getHandler())($args, $assocArgs, $this->context);
    }
}
