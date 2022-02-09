<?php

namespace ToyWpCli;

class DefaultInvocationStrategy implements InvocationStrategyInterface
{
	public function call($callback)
	{
		return $callback();
	}

	public function callCommandHandler(Command $command, array $args, array $assoc_args)
	{
		return ($command->getHandler())($args, $assoc_args);
	}
}
