<?php

declare(strict_types=1);

namespace ApheleiaCli;

use ApheleiaCli\Input\InputInterface;
use ApheleiaCli\Input\WpCliInput;
use ApheleiaCli\Invoker\GenericInvokerInterface;
use ApheleiaCli\Invoker\HandlerInvokerInterface;
use ApheleiaCli\Invoker\InvokerFactoryInterface;
use ApheleiaCli\Output\ConsoleOutput;
use ApheleiaCli\Output\ConsoleOutputInterface;
use ApheleiaCli\WpCli\WpCliConfigInterface;
use LogicException;

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
     * @var callable(string[], array<string, string>, Command):InputInterface
     */
    protected $inputFactory;

    /**
     * @var InvokerFactoryInterface
     */
    protected $invokerFactory;

    /**
     * @var callable(WpCliConfigInterface):ConsoleOutputInterface
     */
    protected $outputFactory;

    /**
     * @var ?int
     */
    protected $statusCode = null;

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

        $this->inputFactory = fn ($args, $assocArgs, $command) => new WpCliInput($args, $assocArgs, $command);
        $this->outputFactory = fn ($config) => new ConsoleOutput($config->isQuiet());
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

        if (null !== ($afterInvoke = $this->command->getAfterInvokeCallback())) {
            $args['after_invoke'] = function () use ($afterInvoke) {
                $this->createGenericInvoker()->invoke($afterInvoke);

                if ($this->autoExit) {
                    $this->wpCliAdapter->halt(is_int($this->statusCode) ? $this->statusCode : Status::SUCCESS);
                }
            };
        } elseif (! $this->command instanceof NamespaceCommand && $this->autoExit) {
            $args['after_invoke'] = function () {
                if ($this->autoExit) {
                    $this->wpCliAdapter->halt(is_int($this->statusCode) ? $this->statusCode : Status::SUCCESS);
                }
            };
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

    /**
     * @param callable(string[], array<string, string>, Command):InputInterface $inputFactory
     */
    public function setInputFactory(callable $inputFactory): self
    {
        $this->inputFactory = $inputFactory;

        return $this;
    }

    /**
     * @param callable(WpCliConfigInterface):ConsoleOutputInterface $outputFactory
     */
    public function setOutputFactory(callable $outputFactory): self
    {
        $this->outputFactory = $outputFactory;

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

    protected function handle(array $args, array $assocArgs): int
    {
        $handler = $this->command->getHandler();

        if (! is_callable($handler)) {
            throw new LogicException(
                'CommandAddition::handle() should never be called for a namespace command handler'
            );
        }

        $status = $this->createHandlerInvoker()->invoke(
            $handler,
            ($this->inputFactory)($args, $assocArgs, $this->command),
            ($this->outputFactory)($this->config),
            $this->command,
        );

        if (! is_int($status) || $status < 0) {
            $status = 0;
        }

        if ($status > 255) {
            $status = 255;
        }

        return $this->statusCode = $status;
    }
}
