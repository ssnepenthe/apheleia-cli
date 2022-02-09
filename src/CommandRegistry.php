<?php

namespace ToyWpCli;

use Closure;
use WP_CLI;

// @todo registry interface
// @todo command aliases - I think we have to resort to reflection to achieve this.
//       Alias is a private property on WP_CLI\Dispatcher\SubCommand and seems to only be set from docblock.
class CommandRegistry
{
    protected $commandParser;
    protected $invocationStrategy;
    protected $namespace = [];
    protected $registeredCommands = [];

    public function __construct(?InvocationStrategyInterface $invocationStrategy = null)
    {
        $this->invocationStrategy = $invocationStrategy ?: new DefaultInvocationStrategy();

        // @todo interface + injection
        $this->commandParser = new CommandParser();
    }

    public function add(Command $command): void
    {
        if (! empty($this->namespace)) {
            $command->setNamespace(implode(' ', $this->namespace));
        }

        $this->registeredCommands[] = $command;
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
        // @todo WP_CLI::add_wp_hook()?
        add_action($when, function () {
            $this->doInitialize();
        });
    }

    public function initializeImmediately(): void
    {
        $this->doInitialize();
    }

    public function namespace(string $namespace, string $description, callable $callback): void
    {
        // @todo track previously registered namespaces?
        // @todo Examples?
        $command = new Command();
        $command->setName($namespace);
        $command->setHandler(NamespaceIdentifier::class);
        $command->setDescription($description);

        $this->add($command);

        $this->namespace[] = $namespace;

        $callback($this);

        array_pop($this->namespace);
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
        // @todo accept ...$args?
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
