<?php

declare(strict_types=1);

namespace ToyWpCli;

use Closure;
use WP_CLI;

class CommandRegistry
{
    protected $allowChildlessNamespaces = false;
    protected $commandParser;
    protected $invocationStrategy;
    protected $namespace = [];
    protected $registeredCommands = [];

    public function __construct(
        ?InvocationStrategyInterface $invocationStrategy = null,
        ?CommandParserInterface $commandParser = null
    ) {
        $this->invocationStrategy = $invocationStrategy ?: new DefaultInvocationStrategy();
        $this->commandParser = $commandParser ?: new CommandParser();
    }

    public function add(Command $command): void
    {
        if (! empty($this->namespace)) {
            $command->setNamespace(implode(' ', $this->namespace));
        }

        $this->registeredCommands[] = $command;
    }

    public function allowChildlessNamespaces(bool $allowChildlessNamespaces = true)
    {
        $this->allowChildlessNamespaces = $allowChildlessNamespaces;

        return $this;
    }

    public function command(string $command, $handler): CommandAdditionHelper
    {
        $command = $this->commandParser->parse($command);
        $command->setHandler($handler);

        $this->add($command);

        return new CommandAdditionHelper($command);
    }

    public function initialize(string $when = 'plugins_loaded'): void
    {
        add_action($when, function () {
            $this->doInitialize();
        });
    }

    public function initializeImmediately(): void
    {
        $this->doInitialize();
    }

    public function namespace(
        string $namespace,
        string $description,
        ?callable $callback = null
    ): void {
        $command = new Command();
        $command->setName($namespace);
        $command->setHandler(NamespaceIdentifier::class);
        $command->setDescription($description);

        $this->add($command);

        if (is_callable($callback)) {
            $preCallbackCommandCount = count($this->registeredCommands);

            $this->namespace[] = $namespace;

            $callback($this);

            array_pop($this->namespace);

            if (
                ! $this->allowChildlessNamespaces
                && count($this->registeredCommands) === $preCallbackCommandCount
            ) {
                array_pop($this->registeredCommands);
            }
        }
    }

    protected function doInitialize(): void
    {
        foreach ($this->registeredCommands as $command) {
            $args = [];

            if ($shortdesc = $command->getDescription()) {
                $args['shortdesc'] = $shortdesc;
            }

            if (! empty($synopsis = $command->getSynopsis())) {
                $args['synopsis'] = $synopsis;
            }

            if ($longdesc = $command->getUsage()) {
                $args['longdesc'] = $longdesc;
            }

            if ($beforeInvoke = $command->getBeforeInvokeCallback()) {
                $args['before_invoke'] = $this->wrapCallback($beforeInvoke);
            }

            if ($afterInvoke = $command->getAfterInvokeCallback()) {
                $args['after_invoke'] = $this->wrapCallback($afterInvoke);
            }

            if ($when = $command->getWhen()) {
                $args['when'] = $when;
            }

            $handler = $command->getHandler();

            if (NamespaceIdentifier::class !== $handler) {
                $handler = $this->wrapCommandHandler($command);
            }

            WP_CLI::add_command($command->getName(), $handler, $args);
        }
    }

    protected function wrapCallback($callback): Closure
    {
        return function () use ($callback) {
            return $this->invocationStrategy->call($callback);
        };
    }

    protected function wrapCommandHandler(Command $command): Closure
    {
        return function ($args, $assoc_args) use ($command) {
            return $this->invocationStrategy->callCommandHandler($command, $args, $assoc_args);
        };
    }
}
