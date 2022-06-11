<?php

declare(strict_types=1);

namespace ApheleiaCli;

use InvalidArgumentException;
use RuntimeException;

class Command
{
    /**
     * @var bool
     */
    protected $acceptArbitraryOptions = false;

    protected $afterInvokeCallback;

    /**
     * @var array<string, Argument>
     */
    protected $arguments = [];

    protected $beforeInvokeCallback;

    /**
     * @var string|null
     */
    protected $description;

    protected $handler;

    /**
     * @var non-empty-string
     * @psalm-suppress PropertyNotSetInConstructor
     */
    protected $name;

    /**
     * @var string|null
     */
    protected $namespace;

    /**
     * @var array<string, Flag|Option>
     */
    protected $options = [];

    /**
     * @var string|null
     */
    protected $usage;

    /**
     * @var string|null
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

    public function getAfterInvokeCallback()
    {
        if (null === $this->afterInvokeCallback && method_exists($this, 'afterInvoke')) {
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

    public function getBeforeInvokeCallback()
    {
        if (null === $this->beforeInvokeCallback && method_exists($this, 'beforeInvoke')) {
            return [$this, 'beforeInvoke'];
        }

        return $this->beforeInvokeCallback;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getHandler()
    {
        if (null !== $this->handler) {
            return $this->handler;
        }

        if (method_exists($this, 'handle')) {
            return [$this, 'handle'];
        }

        throw new RuntimeException(
            "Handler not set for command '{$this->getName()}'"
            . ' - set explicitly using the \$command->setHandler() method'
            . ' or implicitly by implementing the \'handle\' method on your command class'
        );
    }

    public function getName(): string
    {
        if (! is_string($this->name) || '' === $this->name) {
            throw new InvalidArgumentException('Command name must be non-empty string');
        }

        return $this->namespace ? "{$this->namespace} {$this->name}" : $this->name;
    }

    /**
     * @return array<string, Flag|Option>
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    public function getSynopsis(): array
    {
        $arguments = array_map(function (Argument $argument): array {
            return $argument->getSynopsis();
        }, $this->arguments);

        $options = array_map(
            /**
             * @param Flag|Option $option
             */
            function ($option): array {
                return $option->getSynopsis();
            },
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

    public function setAfterInvokeCallback($afterInvokeCallback): self
    {
        $this->afterInvokeCallback = $afterInvokeCallback;

        return $this;
    }

    public function setBeforeInvokeCallback($beforeInvokeCallback): self
    {
        $this->beforeInvokeCallback = $beforeInvokeCallback;

        return $this;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function setHandler($handler): self
    {
        $this->handler = $handler;

        return $this;
    }

    /**
     * @param non-empty-string $name
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function setNamespace(string $namespace): self
    {
        $this->namespace = $namespace;

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
