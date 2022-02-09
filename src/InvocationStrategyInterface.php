<?php

namespace ToyWpCli;

interface InvocationStrategyInterface
{
    public function call($callback);
    public function callCommandHandler(Command $command, array $args, array $assoc_args);
}
