<?php

declare(strict_types=1);

namespace ApheleiaCli;

class CommandAddition
{
    /**
     * @var bool
     */
    protected $autoExit = true;

    /**
     * @var Command
     */
    protected $command;

    /**
     * @var InvocationStrategyFactoryInterface
     */
    protected $invocationStrategyFactory;

    /**
     * @var WpCliAdapterInterface
     */
    protected $wpCliAdapter;

    public function __construct(
        Command $command,
        InvocationStrategyFactoryInterface $invocationStrategyFactory,
        WpCliAdapterInterface $wpCliAdapter
    ) {
        $this->command = $command;
        $this->invocationStrategyFactory = $invocationStrategyFactory;
        $this->wpCliAdapter = $wpCliAdapter;
    }

    /**
     * @return array{shortdesc?: non-falsy-string, synopsis?: non-empty-array<int, array{type: 'assoc'|'flag'|'generic'|'positional', name?: string, optional: bool, repeating: bool, description?: string, default?: string, options?: string[], value?: array{optional: true, name: string}}>, longdesc?: non-falsy-string, before_invoke?: \Closure, after_invoke?: \Closure, when?: non-falsy-string}
     */
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
            $args['before_invoke'] = fn () => $this->createInvocationStrategy()->call($beforeInvoke);
        }

        if ($afterInvoke = $this->command->getAfterInvokeCallback()) {
            $args['after_invoke'] = fn () => $this->createInvocationStrategy()->call($afterInvoke);
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

    /**
     * @return \Closure|class-string<\WP_CLI\Dispatcher\CommandNamespace>
     */
    public function getHandler()
    {
        $handler = $this->command->getHandler();

        if (is_callable($handler)) {
            $handler = fn (array $args, array $assocArgs) => $this->handle($args, $assocArgs);
        }

        return $handler;
    }

    /**
     * @return non-empty-string
     */
    public function getName(): string
    {
        return $this->command->getName();
    }

    public function setAutoExit(bool $autoExit): self
    {
        $this->autoExit = $autoExit;

        return $this;
    }

    protected function createInvocationStrategy(): InvocationStrategyInterface
    {
        return $this->invocationStrategyFactory->create($this->command->getRequiredInvocationStrategy());
    }

    /**
     * @return int
     */
    protected function handle(array $args, array $assocArgs)
    {
        $status = $this->createInvocationStrategy()
            ->callCommandHandler($this->command, compact('args', 'assocArgs'));

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
