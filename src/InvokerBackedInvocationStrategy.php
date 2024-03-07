<?php

declare(strict_types=1);

namespace ApheleiaCli;

use Invoker\Invoker;
use Invoker\InvokerInterface;
use Invoker\ParameterResolver\AssociativeArrayResolver;
use Invoker\ParameterResolver\DefaultValueResolver;
use Invoker\ParameterResolver\NumericArrayResolver;
use Invoker\ParameterResolver\ResolverChain;

class InvokerBackedInvocationStrategy implements InvocationStrategyInterface
{
    /**
     * @var InvokerInterface
     */
    protected $invoker;

    public function __construct(?InvokerInterface $invoker = null)
    {
        $this->invoker = $invoker ?: new Invoker(
            new ResolverChain([
                new NumericArrayResolver(),
                new AssociativeArrayResolver(),
                new TransformingAssociativeArrayParameterResolver(),
                new DefaultValueResolver(),
            ])
        );
    }

    /**
     * @return mixed
     */
    public function call(callable $callback, array $arguments = [])
    {
        return $this->invoker->call(
            $callback,
            array_merge(['context' => $arguments], $arguments)
        );
    }

    /**
     * @return mixed
     */
    public function callCommandHandler(Command $command, array $arguments = [])
    {
        $args = $argsCopy = $arguments['args'] ?? [];
        $assocArgs = $assocArgsCopy = $arguments['assocArgs'] ?? [];

        $parameters = [
            'args' => $args,
            'assocArgs' => $assocArgs,
            'arguments' => $args,
            'options' => $assocArgs,
            'context' => $arguments,
        ];

        $registeredArgs = $command->getArguments();

        while (count($argsCopy)) {
            $current = array_shift($registeredArgs);

            $name = $current->getName();

            if ($current->getRepeating()) {
                $parameters[$name] = $argsCopy;

                $argsCopy = [];
            } else {
                $arg = array_shift($argsCopy);

                $parameters[$name] = $arg;
            }
        }

        foreach ($command->getOptions() as $option) {
            $name = $option->getName();

            if (array_key_exists($name, $assocArgs)) {
                $parameters[$name] = $assocArgsCopy[$name];

                unset($assocArgsCopy[$name]);
            }
        }

        if ($command->getAcceptArbitraryOptions() && ! empty($assocArgsCopy)) {
            $parameters['arbitraryOptions'] = $assocArgsCopy;
        }

        return $this->invoker->call($command->getHandler(), $parameters);
    }

    public function getInvoker(): InvokerInterface
    {
        return $this->invoker;
    }
}
