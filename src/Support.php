<?php

declare(strict_types=1);

namespace ApheleiaCli;

use Closure;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;

class Support
{
    public static function callableReflector(callable $callable): ReflectionFunctionAbstract
    {
        if ($callable instanceof Closure) {
            return new ReflectionFunction($callable);
        }

        if (is_string($callable)) {
            if (false !== strpos($callable, '::')) {
                return new ReflectionMethod($callable);
            }

            return new ReflectionFunction($callable);
        }

        if (is_object($callable)) {
            return new ReflectionMethod($callable, '__invoke');
        }

        // @see https://github.com/vimeo/psalm/issues/7778
        return new ReflectionMethod($callable[0], $callable[1]);
    }

    public static function camelCase(string $string): string
    {
        return lcfirst(
            str_replace(
                ' ',
                '',
                ucwords(
                    str_replace(
                        ['-', '_'],
                        ' ',
                        preg_replace(
                            '/\B([A-Z])/',
                            '-$1',
                            $string
                        )
                    )
                )
            )
        );
    }

    public static function kebabCase(string $string): string
    {
        return preg_replace(
            '/[-_\s]+/',
            '-',
            strtolower(
                preg_replace(
                    '/\B([A-Z])/',
                    '-$1',
                    $string
                )
            )
        );
    }

    public static function pascalCase(string $string): string
    {
        return str_replace(
            ' ',
            '',
            ucwords(
                str_replace(
                    ['-', '_'],
                    ' ',
                    preg_replace(
                        '/\B([A-Z])/',
                        '-$1',
                        $string
                    )
                )
            )
        );
    }

    public static function snakeCase(string $string): string
    {
        return preg_replace(
            '/[-_\s]+/',
            '_',
            strtolower(
                preg_replace(
                    '/\B([A-Z])/',
                    '-$1',
                    $string
                )
            )
        );
    }
}
