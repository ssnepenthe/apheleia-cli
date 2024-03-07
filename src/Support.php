<?php

declare(strict_types=1);

namespace ApheleiaCli;

class Support
{
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
