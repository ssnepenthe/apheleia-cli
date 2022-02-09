<?php

namespace ToyWpCli;

use Invoker\Invoker;
use Invoker\InvokerInterface;

// @todo include global args in values provided to callable
class InvokerBackedInvocationStrategy implements InvocationStrategyInterface
{
	protected $invoker;

	public function __construct(?InvokerInterface $invoker = null)
	{
		$this->invoker = $invoker ?: new Invoker();
	}

	public function call($callback)
	{
		return $this->invoker->call($callback);
	}

	public function callCommandHandler(Command $command, array $args, array $assoc_args)
	{
		// @todo Add config for whether we use snake case or camel case?
		$parameters = [
			'args' => $args,
			'arguments' => $args,
			'assocArgs' => $assoc_args,
			'assoc_args' => $assoc_args,
			'opts' => $assoc_args,
			'options' => $assoc_args,
		];

		$registeredArgs = $command->getArguments();

		while (count($args)) {
			$current = array_shift($registeredArgs);

			$name = $current->getName();
			$snakeName = $this->snakeCase($name);
			$camelName = $this->camelCase($name);

			if ($current->getRepeating()) {
				$parameters[$snakeName] = $args;
				$parameters[$camelName] = $args;

				$args = [];
			} else {
				$arg = array_shift($args);

				$parameters[$snakeName] = $arg;
				$parameters[$camelName] = $arg;
			}
		}

		foreach ($command->getOptions() as $option) {
			$name = $option->getName();
			$snakeName = $this->snakeCase($name);
			$camelName = $this->camelCase($name);

			if (array_key_exists($name, $assoc_args)) {
				$parameters[$snakeName] = $assoc_args[$name];
				$parameters[$camelName] = $assoc_args[$name];

				unset($assoc_args[$name]);
			} elseif ($option instanceof Flag) {
				$parameters[$snakeName] = false;
				$parameters[$camelName] = false;
			}
		}

		// @todo Configurable arbitrary options variable name.
		if ($command->getAcceptArbitraryOptions() && ! empty($assoc_args)) {
			$parameters['arbitraryOptions'] = $assoc_args;
			$parameters['arbitrary_options'] = $assoc_args;
		}

		return $this->invoker->call($command->getHandler(), $parameters);
	}

	// @todo According to the SynopsisParser class, options should match ([a-z-_0-9]+) and args
	// should match ([a-zA-Z-_|,0-9]+). So these string helpers are likely insufficient.
	protected function camelCase(string $string): string
	{
		return lcfirst(str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $string))));
	}

	protected function snakeCase(string $string): string
	{
		return strtolower(str_replace('-', '_', $string));
	}
}
