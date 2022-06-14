<?php

declare(strict_types=1);

namespace ApheleiaCli;

use Closure;
use RuntimeException;

class CommandRegistry
{
    /**
     * @var bool
     */
    protected $allowChildlessNamespaces = false;

    /**
     * @var CommandParserInterface
     */
    protected $commandParser;

    /**
     * @var InvocationStrategyInterface
     */
    protected $invocationStrategy;

    /**
     * @var list<string>
     */
    protected $namespace = [];

    /**
     * @var array<string, Command>
     */
    protected $registeredCommands = [];

    /**
     * @var WpCliAdapterInterface
     */
    protected $wpCliAdapter;

    public function __construct(
        ?InvocationStrategyInterface $invocationStrategy = null,
        ?CommandParserInterface $commandParser = null,
        ?WpCliAdapterInterface $wpCliAdapter = null
    ) {
        $this->invocationStrategy = $invocationStrategy ?: new DefaultInvocationStrategy();
        $this->commandParser = $commandParser ?: new CommandParser();
        $this->wpCliAdapter = $wpCliAdapter ?: new WpCliAdapter();
    }

    public function add(Command $command): void
    {
        if (! empty($this->namespace)) {
            $command->setNamespace(implode(' ', $this->namespace));
        }

        $name = $command->getName();

        if (\array_key_exists($name, $this->registeredCommands)) {
            throw new RuntimeException(
                "Cannot register command '{$name}' - command with this name already exists"
            );
        }

        $this->registeredCommands[$name] = $command;
    }

    public function allowChildlessNamespaces(bool $allowChildlessNamespaces = true): self
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

    /**
     * @return array<string, Command>
     */
    public function getRegisteredCommands(): array
    {
        return $this->registeredCommands;
    }

    public function initialize(string $when = 'plugins_loaded'): void
    {
        $this->wpCliAdapter->addWpHook($when, function () {
            $this->doInitialize();
        });
    }

    public function initializeImmediately(): void
    {
        $this->doInitialize();
    }

    /**
     * @param non-empty-string $namespace
     */
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
                $this->remove($command);
            }
        }
    }

    public function remove(Command $command): void
    {
        $name = $command->getName();

        if (! \array_key_exists($name, $this->registeredCommands)) {
            throw new RuntimeException(
                "Cannot remove command '{$name}' - no command with this name has been registered"
            );
        }

        unset($this->registeredCommands[$name]);
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

            $this->wpCliAdapter->addCommand($command->getName(), $handler, $args);
        }
    }

    protected function wrapCallback($callback): Closure
    {
        /**
         * @return mixed
         */
        return function () use ($callback) {
            return $this->invocationStrategy->call($callback);
        };
    }

    protected function wrapCommandHandler(Command $command): Closure
    {
        return function (array $args, array $assocArgs) use ($command) {
            return $this->invocationStrategy->callCommandHandler($command, $args, $assocArgs);
        };
    }
}
