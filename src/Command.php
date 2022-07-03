<?php

declare(strict_types=1);

namespace ApheleiaCli;

use InvalidArgumentException;
use ReflectionMethod;
use RuntimeException;
use WP_CLI\Dispatcher\CommandNamespace;

class Command
{
    public const STATUS_FAILURE = 1;
    public const STATUS_INVALID = 2;
    public const STATUS_SUCCESS = 0;

    /**
     * @var bool
     */
    protected $acceptArbitraryOptions = false;

    /**
     * @var ?callable
     */
    protected $afterInvokeCallback;

    /**
     * @var array<string, Argument>
     */
    protected $arguments = [];

    /**
     * @var ?callable
     */
    protected $beforeInvokeCallback;

    /**
     * @var ?string
     */
    protected $description;

    /**
     * @var ?callable|class-string<CommandNamespace>
     */
    protected $handler;

    /**
     * @var string
     * @psalm-suppress PropertyNotSetInConstructor
     */
    protected $name;

    /**
     * @var array<string, Flag|Option>
     */
    protected $options = [];

    /**
     * @var ?Command
     */
    protected $parent;

    /**
     * @var ?string
     */
    protected $usage;

    /**
     * @var ?string
     */
    protected $when;

    public function __construct()
    {
        $this->configure();
    }

    public function addArgument(Argument $argument): self
    {
        $name = $argument->getName();

        if ($this->hasParameter($name)) {
            throw new RuntimeException(
                "Cannot register argument '{$name}' - parameter with this name already exists"
            );
        }

        $lastArgument = end($this->arguments);

        if ($lastArgument instanceof Argument) {
            if ($lastArgument->getRepeating()) {
                throw new RuntimeException(
                    'Cannot register additional arguments after a repeating argument'
                );
            }

            // Required arguments should never come after optional arguments.
            if ($lastArgument->getOptional() && ! $argument->getOptional()) {
                throw new RuntimeException(
                    'Cannot register required argument after an optional argument'
                );
            }
        }

        $this->arguments[$name] = $argument;

        return $this;
    }

    public function addFlag(Flag $flag): self
    {
        $name = $flag->getName();

        if ($this->hasParameter($name)) {
            throw new RuntimeException(
                "Cannot register flag '{$name}' - parameter with this name already exists"
            );
        }

        $this->options[$name] = $flag;

        return $this;
    }

    public function addOption(Option $option): self
    {
        $name = $option->getName();

        if ($this->hasParameter($name)) {
            throw new RuntimeException(
                "Cannot register option '{$name}' - parameter with this name already exists"
            );
        }

        $this->options[$name] = $option;

        return $this;
    }

    public function getAcceptArbitraryOptions(): bool
    {
        return $this->acceptArbitraryOptions;
    }

    public function getAfterInvokeCallback(): ?callable
    {
        if (null === $this->afterInvokeCallback && method_exists($this, 'afterInvoke')) {
            $this->assertThisMethodIsPublic('afterInvoke');

            /** @var callable */
            return [$this, 'afterInvoke'];
        }

        return $this->afterInvokeCallback;
    }

    /**
     * @return array<string, Argument>
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function getBeforeInvokeCallback(): ?callable
    {
        if (null === $this->beforeInvokeCallback && method_exists($this, 'beforeInvoke')) {
            $this->assertThisMethodIsPublic('beforeInvoke');

            /** @var callable */
            return [$this, 'beforeInvoke'];
        }

        return $this->beforeInvokeCallback;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @return callable|class-string<CommandNamespace>
     */
    public function getHandler()
    {
        if (null !== $this->handler) {
            return $this->handler;
        }

        if (method_exists($this, 'handle')) {
            $this->assertThisMethodIsPublic('handle');

            /** @var callable */
            return [$this, 'handle'];
        }

        throw new RuntimeException(
            "Handler not set for command '{$this->getName()}'"
            . ' - set explicitly using the \$command->setHandler() method'
            . ' or implicitly by implementing the \'handle\' method on your command class'
        );
    }

    /**
     * @return non-empty-string
     */
    public function getName(): string
    {
        if (! is_string($this->name) || '' === $this->name) {
            throw new InvalidArgumentException('Command name must be non-empty string');
        }

        $name = $this->name;

        if ($this->parent instanceof Command) {
            $name = "{$this->parent->getName()} {$name}";
        }

        return $name;
    }

    /**
     * @return array<string, Flag|Option>
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * @return array<int, array{type: 'assoc'|'flag'|'generic'|'positional', name?: string, optional: bool, repeating: bool, description?: string, default?: string, options?: string[], value?: array{optional: true, name: string}}>
     */
    public function getSynopsis(): array
    {
        $arguments = array_map(
            fn (Argument $argument): array => $argument->getSynopsis(),
            $this->arguments
        );

        $options = array_map(
            /**
             * @param Flag|Option $option
             */
            fn ($option): array => $option->getSynopsis(),
            $this->options
        );

        $synopsis = array_values(array_merge($arguments, $options));

        if ($this->acceptArbitraryOptions) {
            $synopsis[] = [
                'type' => 'generic',
                'optional' => true,
                'repeating' => false,
            ];
        }

        return $synopsis;
    }

    public function getUsage(): ?string
    {
        return $this->usage;
    }

    public function getWhen(): ?string
    {
        return $this->when;
    }

    public function setAcceptArbitraryOptions(bool $acceptArbitraryOptions = true): self
    {
        $this->acceptArbitraryOptions = $acceptArbitraryOptions;

        return $this;
    }

    public function setAfterInvokeCallback(callable $afterInvokeCallback): self
    {
        $this->afterInvokeCallback = $afterInvokeCallback;

        return $this;
    }

    public function setBeforeInvokeCallback(callable $beforeInvokeCallback): self
    {
        $this->beforeInvokeCallback = $beforeInvokeCallback;

        return $this;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function setHandler(callable $handler): self
    {
        $this->handler = $handler;

        return $this;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function setParent(?Command $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    public function setUsage(string $usage): self
    {
        $this->usage = $usage;

        return $this;
    }

    public function setWhen(string $when): self
    {
        $this->when = $when;

        return $this;
    }

    protected function assertThisMethodIsPublic(string $method): void
    {
        if (! (new ReflectionMethod($this, $method))->isPublic()) {
            throw new RuntimeException("Command method '{$method}' must have public visibility");
        }
    }

    protected function configure(): void
    {
        // Nothing by default...
    }

    protected function hasParameter(string $name): bool
    {
        return \array_key_exists($name, $this->arguments)
            || \array_key_exists($name, $this->options);
    }
}
