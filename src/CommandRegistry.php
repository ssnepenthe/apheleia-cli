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
     * @var bool
     */
    protected $autoExit = true;

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
     * @var array<int, callable(string):string>
     */
    protected $parameterNameMappers = [];

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

    public function add(Command $command): Command
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

        return $command;
    }

    public function allowChildlessNamespaces(bool $allowChildlessNamespaces = true): self
    {
        $this->allowChildlessNamespaces = $allowChildlessNamespaces;

        return $this;
    }

    public function command(string $command, $handler): CommandAdditionHelper
    {
        $command = $this->commandParser->parse($command);

        $addition = new CommandAdditionHelper($command, $this->parameterNameMappers);
        $addition->handler($handler);

        $this->add($command);

        return $addition;
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
        $this->wpCliAdapter->addWpHook($when, fn () => $this->doInitialize());
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
    ): Command {
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

    public function setParameterNameMappers(callable ...$parameterNameMappers): self
    {
        $this->parameterNameMappers = $parameterNameMappers;

        return $this;
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
                $args['before_invoke'] = fn () => $this->invocationStrategy->call($beforeInvoke);
            }

            if ($afterInvoke = $command->getAfterInvokeCallback()) {
                $args['after_invoke'] = fn () => $this->invocationStrategy->call($afterInvoke);
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

    protected function wrapCommandHandler(Command $command): Closure
    {
        return function (array $args, array $assocArgs) use ($command) {
            $status = $this->invocationStrategy
                ->withContext(compact('args', 'assocArgs'))
                ->callCommandHandler($command);

            if (! is_int($status) || $status < 0) {
                $status = 0;
            }

            if ($status > 255) {
                $status = 255;
            }

            if ($this->autoExit) {
                $this->wpCliAdapter->halt($status);
            }

            return $status;
        };
    }
}
