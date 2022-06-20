<?php

declare(strict_types=1);

namespace ApheleiaCli;

use Invoker\ParameterResolver\ParameterResolver;
use ReflectionFunctionAbstract;

class TransformingAssociativeArrayParameterResolver implements ParameterResolver
{
    /**
     * @var array<int, callable(string):string>
     */
    protected $parameterNameTransformers;

    /**
     * @param array<int, callable(string):string> $parameterNameTransformers
     */
    public function __construct(array $parameterNameTransformers = [])
    {
        $this->setParameterNameTransformers(...($parameterNameTransformers ?: [
            [Support::class, 'kebabCase'],
            [Support::class, 'snakeCase'],
            [Support::class, 'camelCase'],
            [Support::class, 'pascalCase'],
        ]));
    }

    public function getParameters(
        ReflectionFunctionAbstract $reflection,
        array $providedParameters,
        array $resolvedParameters
    ): array {
        $parameters = $reflection->getParameters();

        if (! empty($resolvedParameters)) {
            $parameters = array_diff_key($parameters, $resolvedParameters);
        }

        foreach ($parameters as $index => $parameter) {
            foreach ($this->parameterNameTransformers as $parameterNameTransformer) {
                $name = $parameterNameTransformer($parameter->getName());

                if (array_key_exists($name, $providedParameters)) {
                    $resolvedParameters[$index] = $providedParameters[$name];

                    break;
                }
            }
        }

        return $resolvedParameters;
    }

    /**
     * @param callable(string):string ...$parameterNameTransformers
     */
    public function setParameterNameTransformers(callable ...$parameterNameTransformers): void
    {
        $this->parameterNameTransformers = $parameterNameTransformers;
    }
}
