<?php

declare(strict_types=1);

namespace ApheleiaCli;

use ApheleiaCli\Input\WpCliInput;
use ApheleiaCli\Invoker\GenericInvokerInterface;
use ApheleiaCli\Invoker\HandlerInvokerInterface;
use ApheleiaCli\Invoker\InvokerFactoryInterface;
use ApheleiaCli\Output\ConsoleOutput;
use ApheleiaCli\WpCli\WpCliConfigInterface;

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
     *
     * @var WpCliConfigInterface
     */
    protected $config;

    /**
     * @var InvokerFactoryInterface
     */
    protected $invokerFactory;

    /**
     * @var WpCliAdapterInterface
     */
    protected $wpCliAdapter;

    public function __construct(
        Command $command,
        InvokerFactoryInterface $invokerFactory,
        WpCliAdapterInterface $wpCliAdapter,
        WpCliConfigInterface $config
    ) {
        $this->command = $command;
        $this->invokerFactory = $invokerFactory;
        $this->wpCliAdapter = $wpCliAdapter;
        $this->config = $config;
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
            $args['before_invoke'] = fn () => $this->createGenericInvoker()->invoke($beforeInvoke);
        }

        if ($afterInvoke = $this->command->getAfterInvokeCallback()) {
            $args['after_invoke'] = fn () => $this->createGenericInvoker()->invoke($afterInvoke);
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

    protected function createGenericInvoker(): GenericInvokerInterface
    {
        return $this->invokerFactory->createGenericInvoker($this->command->getGenericInvokerClass());
    }

    protected function createHandlerInvoker(): HandlerInvokerInterface
    {
        return $this->invokerFactory->createHandlerInvoker($this->command->getHandlerInvokerClass());
    }

    /**
     * @return int
     */
    protected function handle(array $args, array $assocArgs)
    {
        $status = $this->createHandlerInvoker()->invoke(
            $this->command->getHandler(),
            new WpCliInput($args, $assocArgs, $this->command),
            new ConsoleOutput($this->config->isQuiet()),
            $this->command,
        );

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
