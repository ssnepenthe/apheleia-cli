<?php

declare(strict_types=1);

namespace ApheleiaCli;

class DefaultInvocationStrategy extends AbstractInvocationStrategy
{
    /**
     * @return mixed
     */
    public function call($callback)
    {
        return $callback($this->context);
    }

    /**
     * @return mixed
     */
    public function callCommandHandler(Command $command)
    {
        return ($command->getHandler())(
            $this->context['args'] ?? [],
            $this->context['assocArgs'] ?? [],
            $this->context
        );
    }
}
