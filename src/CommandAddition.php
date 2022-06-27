<?php

declare(strict_types=1);

namespace ApheleiaCli;

class CommandAddition
{
    protected $autoExit = true;
    protected $command;
    protected $invocationStrategy;
    protected $wpCliAdapter;

    public function __construct(
        Command $command,
        InvocationStrategyInterface $invocationStrategy,
        WpCliAdapterInterface $wpCliAdapter
    ) {
        $this->command = $command;
        $this->invocationStrategy = $invocationStrategy;
        $this->wpCliAdapter = $wpCliAdapter;
    }

    public function getArgs(): array
    {
        $args = [];

        if ($shortdesc = $this->command->getDescription()) {
            $args['shortdesc'] = $shortdesc;
        }

        if (! empty($synopsis = $this->command->getSynopsis())) {
            $args['synopsis'] = $synopsis;
        }

        if ($longdesc = $this->command->getUsage()) {
            $args['longdesc'] = $longdesc;
        }

        if ($beforeInvoke = $this->command->getBeforeInvokeCallback()) {
            $args['before_invoke'] = fn () => $this->invocationStrategy->call($beforeInvoke);
        }

        if ($afterInvoke = $this->command->getAfterInvokeCallback()) {
            $args['after_invoke'] = fn () => $this->invocationStrategy->call($afterInvoke);
        }

        if ($when = $this->command->getWhen()) {
            $args['when'] = $when;
        }

        return $args;
    }

    public function getCommand(): Command
    {
        return $this->command;
    }

    public function getHandler()
    {
        $handler = $this->command->getHandler();

        if (NamespaceIdentifier::class !== $handler) {
            $handler = fn (array $args, array $assocArgs) => $this->handle($args, $assocArgs);
        }

        return $handler;
    }

    public function getName(): string
    {
        return $this->command->getName();
    }

    public function setAutoExit(bool $autoExit): self
    {
        $this->autoExit = $autoExit;

        return $this;
    }

    protected function handle(array $args, array $assocArgs)
    {
        $status = $this->invocationStrategy
            ->withContext(compact('args', 'assocArgs'))
            ->callCommandHandler($this->command);

        if (! is_int($status) || $status < 0) {
            $status = 0;
        }

        if ($status > 255) {
            $status = 255;
        }

        if ($this->autoExit) {
            $this->wpCliAdapter->halt($status);
        } else {
            return $status;
        }
    }
}
