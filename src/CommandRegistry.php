<?php

declare(strict_types=1);

namespace ApheleiaCli;

use RuntimeException;

class CommandRegistry
{
    /**
     * @var bool
     */
    protected $allowChildlessGroups = false;

    /**
     * @var bool
     */
    protected $autoExit = true;

    /**
     * @var CommandParserInterface
     */
    protected $commandParser;

    /**
     * @var ?Command
     */
    protected $currentGroupParent;

    /**
     * @var InvocationStrategyFactoryInterface
     */
    protected $invocationStrategyFactory;

    /**
     * @var array<int, callable(string):string>
     */
    protected $parameterNameMappers = [];

    /**
     * @var array<string, CommandAddition>
     */
    protected $registeredCommands = [];

    /**
     * @var WpCliAdapterInterface
     */
    protected $wpCliAdapter;

    public function __construct(
        ?InvocationStrategyFactoryInterface $invocationStrategyFactory = null,
        ?CommandParserInterface $commandParser = null,
        ?WpCliAdapterInterface $wpCliAdapter = null
    ) {
        $this->invocationStrategyFactory = $invocationStrategyFactory ?: new InvocationStrategyFactory();
        $this->commandParser = $commandParser ?: new CommandParser();

        if (! $wpCliAdapter instanceof WpCliAdapterInterface) {
            $wpCliAdapter = defined('WP_CLI') && WP_CLI
                ? new WpCliAdapter()
                : new NullWpCliAdapter();
        }

        $this->wpCliAdapter = $wpCliAdapter;
    }

    public function add(Command $command): Command
    {
        if ($this->currentGroupParent instanceof Command) {
            $command->setParent($this->currentGroupParent);
        }

        $name = $command->getName();

        if (\array_key_exists($name, $this->registeredCommands)) {
            throw new RuntimeException(
                "Cannot register command '{$name}' - command with this name already exists"
            );
        }

        $this->registeredCommands[$name] = new CommandAddition(
            $command,
            $this->invocationStrategyFactory,
            $this->wpCliAdapter
        );
        $this->registeredCommands[$name]->setAutoExit($this->autoExit);

        return $command;
    }

    public function allowChildlessGroups(bool $allowChildlessGroups = true): self
    {
        $this->allowChildlessGroups = $allowChildlessGroups;

        return $this;
    }

    public function command(string $command, callable $handler): ParsedCommandHelper
    {
        $command = $this->commandParser->parse($command);

        $addition = new ParsedCommandHelper($command, $this->parameterNameMappers);
        $addition->handler($handler);

        $this->add($command);

        return $addition;
    }

    /**
     * @return array<string, Command>
     */
    public function getRegisteredCommands(): array
    {
        return array_map(
            fn (CommandAddition $addition) => $addition->getCommand(),
            $this->registeredCommands
        );
    }

    public function group(string $group, string $description, callable $callback): Command
    {
        $command = $this->namespace($group, $description);

        $preCallbackCommandCount = count($this->registeredCommands);

        $previousGroupParent = $this->currentGroupParent;
        $this->currentGroupParent = $command;

        $callback($this);

        $this->currentGroupParent = $previousGroupParent;

        if (
            ! $this->allowChildlessGroups
            && count($this->registeredCommands) === $preCallbackCommandCount
        ) {
            $this->remove($command);
        }

        return $command;
    }

    public function initialize(string $when = 'plugins_loaded'): void
    {
        $this->wpCliAdapter->addWpHook($when, [$this, 'initializeImmediately']);
    }

    public function initializeImmediately(): void
    {
        foreach ($this->registeredCommands as $addition) {
            $this->wpCliAdapter->addCommand(
                $addition->getName(),
                $addition->getHandler(),
                $addition->getArgs()
            );
        }
    }

    public function namespace(string $namespace, string $description): Command
    {
        $command = new NamespaceCommand($namespace, $description);

        $this->add($command);

        return $command;
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

    public function setAutoExit(bool $autoExit): self
    {
        $this->autoExit = $autoExit;

        return $this;
    }

    /**
     * @param callable(string):string ...$parameterNameMappers
     */
    public function setParameterNameMappers(callable ...$parameterNameMappers): self
    {
        $this->parameterNameMappers = array_values($parameterNameMappers);

        return $this;
    }
}
