<?php

declare(strict_types=1);

namespace ApheleiaCli\Tests;

use ApheleiaCli\Support;
use PHPUnit\Framework\TestCase;

class SupportTest extends TestCase
{
    public function provideTestCamelCase()
    {
        yield ['', ''];

        yield ['camelCase', 'camelCase'];

        yield ['camelCase', 'camel-case'];
        yield ['camelCase', 'Camel-Case'];

        yield ['camelCase', 'CamelCase'];

        yield ['camelCase', 'camel_case'];
        yield ['camelCase', 'Camel_Case'];

        yield ['camelCase', 'camel case'];
        yield ['camelCase', 'Camel Case'];

        yield ['camelCase', '-camel-case-'];
        yield ['camelCase', '_camel_case_'];
        yield ['camelCase', ' camel case '];

        yield ['caMelCase', 'caMel-case'];
        yield ['caMELCase', 'caMEL-case'];

        yield ['camel2Case', 'camel-2-case'];
        // yield ['2CamelCase', '2camel-case'];
        // yield ['camel2Case', 'camel2case'];
    }

    public function provideTestKebabCase()
    {
        yield ['', ''];

        yield ['kebab-case', 'kebabCase'];

        yield ['kebab-case', 'kebab-case'];
        yield ['kebab-case', 'Kebab-Case'];

        yield ['kebab-case', 'KebabCase'];

        yield ['kebab-case', 'Kebab_Case'];
        yield ['kebab-case', 'kebab_case'];

        yield ['kebab-case', 'kebab case'];
        yield ['kebab-case', 'Kebab Case'];

        yield ['-kebab-case-', '-kebab-case-'];
        yield ['-kebab-case-', '_kebab_case_'];
        yield ['-kebab-case-', ' kebab case '];

        yield ['ke-bab-case', 'keBab_case'];
        yield ['ke-b-a-b-case', 'keBAB_case'];

        yield ['kebab-2-case', 'kebab_2_case'];
        yield ['2kebab-case', '2kebab_case'];
        yield ['kebab2-case', 'kebab2Case'];
        yield ['kebab2case', 'kebab2case'];
    }

    public function provideTestPascalCase()
    {
        yield ['', ''];

        yield ['PascalCase', 'pascalCase'];

        yield ['PascalCase', 'pascal-case'];
        yield ['PascalCase', 'Pascal-Case'];

        yield ['PascalCase', 'PascalCase'];

        yield ['PascalCase', 'Pascal_Case'];
        yield ['PascalCase', 'pascal_case'];

        yield ['PascalCase', 'pascal case'];
        yield ['PascalCase', 'Pascal Case'];

        yield ['PascalCase', '-pascal-case-'];
        yield ['PascalCase', '_pascal_case_'];
        yield ['PascalCase', ' pascal case '];

        yield ['PaScalCase', 'paScal-case'];
        yield ['PasCALCase', 'pasCAL-case'];

        yield ['Pascal2Case', 'pascal-2-case'];
        yield ['Pascal2Case', 'pascal2Case'];

        // yield ['2PascalCase', '2pascal-case'];
        // yield ['Pascal2Case', 'pascal2case'];
    }

    public function provideTestSnakeCase()
    {
        yield ['', ''];

        yield ['snake_case', 'snakeCase'];

        yield ['snake_case', 'snake-case'];
        yield ['snake_case', 'Snake-Case'];

        yield ['snake_case', 'SnakeCase'];

        yield ['snake_case', 'Snake_Case'];
        yield ['snake_case', 'snake_case'];

        yield ['snake_case', 'snake case'];
        yield ['snake_case', 'Snake Case'];

        yield ['_snake_case_', '-snake-case-'];
        yield ['_snake_case_', '_snake_case_'];
        yield ['_snake_case_', ' snake case '];

        yield ['sn_ake_case', 'snAke-case'];
        yield ['sn_a_k_e_case', 'snAKE-case'];

        yield ['snake_2_case', 'snake-2-case'];
        yield ['2snake_case', '2snake-case'];
        yield ['snake2_case', 'snake2Case'];
        yield ['snake2case', 'snake2case'];
    }

    /**
     * @dataProvider provideTestCamelCase
     */
    public function testCamelCase($expected, $input)
    {
        $this->assertSame($expected, Support::camelCase($input));
    }

    /**
     * @dataProvider provideTestKebabCase
     */
    public function testKebabCase($expected, $input)
    {
        $this->assertSame($expected, Support::kebabCase($input));
    }

    /**
     * @dataProvider provideTestPascalCase
     */
    public function testPascalCase($expected, $input)
    {
        $this->assertSame($expected, Support::pascalCase($input));
    }

    /**
     * @dataProvider provideTestSnakeCase
     */
    public function testSnakeCase($expected, $input)
    {
        $this->assertSame($expected, Support::snakeCase($input));
    }
}
