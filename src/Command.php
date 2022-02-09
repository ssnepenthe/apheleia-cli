<?php

namespace ToyWpCli;

use InvalidArgumentException;
use RuntimeException;

// @todo CommandInterface?
class Command
{
    protected $acceptArbitraryOptions = false;
    protected $afterInvokeCallback;
    protected $arguments = [];
    protected $beforeInvokeCallback;
    protected $description;
    protected $handler;
    protected $name;
    protected $namespace;
    protected $options = [];
    protected $usage;
    protected $when;

    public function __construct()
    {
        $this->configure();
    }

    public function addArgument(Argument $argument)
    {
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

        $this->arguments[$argument->getName()] = $argument;

        return $this;
    }

    public function addFlag(Flag $flag)
    {
        $this->options[$flag->getName()] = $flag;

        return $this;
    }

    public function addOption(Option $option)
    {
        $this->options[$option->getName()] = $option;

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

    public function getUsage(): ?string
    {
        return $this->usage;
    }

    public function getName(): string
    {
        if (! is_string($this->name) || '' === $this->name) {
            throw new InvalidArgumentException('Command name must be non-empty string');
        }

        return $this->namespace ? "{$this->namespace} {$this->name}" : $this->name;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getSynopsis(): array
    {
        $arguments = array_map(function ($argument) {
            return $argument->getSynopsis();
        }, $this->arguments);

        $options = array_map(function ($option) {
            return $option->getSynopsis();
        }, $this->options);

        $synopsis = array_merge($arguments, $options);

        if ($this->acceptArbitraryOptions) {
            $synopsis[] = [
                'optional' => true,
                'repeating' => false,
                'type' => 'generic',
            ];
        }

        return $synopsis;
    }

    public function getWhen(): ?string
    {
        return $this->when;
    }

    public function setAcceptArbitraryOptions()
    {
        $this->acceptArbitraryOptions = true;

        return $this;
    }

    public function setAfterInvokeCallback($afterInvokeCallback)
    {
        $this->afterInvokeCallback = $afterInvokeCallback;

        return $this;
    }

    public function setBeforeInvokeCallback($beforeInvokeCallback)
    {
        $this->beforeInvokeCallback = $beforeInvokeCallback;

        return $this;
    }

    public function setDescription(string $description)
    {
        $this->description = $description;

        return $this;
    }

    public function setHandler($handler)
    {
        $this->handler = $handler;

        return $this;
    }

    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    public function setNamespace(string $namespace)
    {
        $this->namespace = $namespace;

        return $this;
    }

    public function setUsage(string $usage)
    {
        $this->usage = $usage;

        return $this;
    }

    public function setWhen(string $when)
    {
        $this->when = $when;

        return $this;
    }

    protected function configure()
    {
        // Nothing by default...
    }
}
