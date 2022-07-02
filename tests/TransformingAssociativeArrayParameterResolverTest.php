<?php

declare(strict_types=1);

namespace ApheleiaCli\Tests;

use ApheleiaCli\TransformingAssociativeArrayParameterResolver;
use PHPUnit\Framework\TestCase;
use ReflectionFunction;

class TransformingAssociativeArrayParameterResolverTest extends TestCase
{
    public function testGetParameters()
    {
        $resolver = new TransformingAssociativeArrayParameterResolver();

        $reflection = new ReflectionFunction(
            fn ($param_one, $paramTwo, $ParamThree, $param_four) => ''
        );

        $parameters = $resolver->getParameters(
            $reflection,
            [
                'param-one' => 'value one',
                'param_two' => 'value two',
                'paramThree' => 'value three',
                'ParamFour' => 'value four',
            ],
            []
        );

        $this->assertCount(4, $parameters);
        $this->assertSame('value one', $parameters[0]);
        $this->assertSame('value two', $parameters[1]);
        $this->assertSame('value three', $parameters[2]);
        $this->assertSame('value four', $parameters[3]);
    }

    public function testGetParametersSkipsAlreadyResolvedParameters()
    {
        $resolver = new TransformingAssociativeArrayParameterResolver();

        $reflection = new ReflectionFunction(
            fn ($param_one, $paramTwo, $ParamThree, $param_four, $paramFive) => ''
        );

        $parameters = $resolver->getParameters(
            $reflection,
            [
                'param-one' => 'value one',
                'param_two' => 'value two',
                'paramThree' => 'value three',
                'ParamFour' => 'value four',
                'param-five' => 'value five',
            ],
            [
                // ParamThree
                2 => 'existing value',
            ]
        );

        $this->assertCount(5, $parameters);
        $this->assertSame('value one', $parameters[0]);
        $this->assertSame('value two', $parameters[1]);
        $this->assertSame('existing value', $parameters[2]);
        $this->assertSame('value four', $parameters[3]);
        $this->assertSame('value five', $parameters[4]);
    }

    public function testGetParametersSkipsMissingParameters()
    {
        $resolver = new TransformingAssociativeArrayParameterResolver();

        $reflection = new ReflectionFunction(fn ($paramOne, $ParamTwo) => '');

        $parameters = $resolver->getParameters(
            $reflection,
            [
                'param_two' => 'value two',
            ],
            []
        );

        $this->assertCount(1, $parameters);
        $this->assertArrayHasKey(1, $parameters);
        $this->assertSame('value two', $parameters[1]);
    }

    public function testGetParametersWithCustomParameterNameMappers()
    {
        $resolver = new TransformingAssociativeArrayParameterResolver([
            fn ($name) => str_ireplace(['one', 'two', 'three'], ['1', '2', '3'], $name),
        ]);

        $reflection = new ReflectionFunction(fn ($paramOne, $paramTwo, $paramThree) => '');

        $parameters = $resolver->getParameters(
            $reflection,
            [
                'param1' => 'value one',
                'param2' => 'value two',
                'param3' => 'value three',
            ],
            []
        );

        $this->assertCount(3, $parameters);
        $this->assertSame('value one', $parameters[0]);
        $this->assertSame('value two', $parameters[1]);
        $this->assertSame('value three', $parameters[2]);
    }
}
