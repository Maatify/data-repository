<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-25
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Generic\Ordering;

use Maatify\DataRepository\Generic\Support\MySQLOrderBuilder;
use Maatify\DataRepository\Generic\Support\OrderUtils;
use PHPUnit\Framework\TestCase;

class MySQLOrderBuilderTest extends TestCase
{
    private MySQLOrderBuilder $builder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->builder = new MySQLOrderBuilder();
    }

    public function testBuildEmptyReturnsEmptyString(): void
    {
        $this->assertSame('', $this->builder->build([]));
        $this->assertSame('', $this->builder->build(null));
    }

    public function testBuildSingleColumnAsc(): void
    {
        $expected = 'ORDER BY `name` ASC';
        $this->assertSame($expected, $this->builder->build(['name' => 'ASC']));
    }

    public function testBuildSingleColumnDesc(): void
    {
        $expected = 'ORDER BY `age` DESC';
        $this->assertSame($expected, $this->builder->build(['age' => 'DESC']));
    }

    public function testBuildMultipleColumns(): void
    {
        $orderBy = ['name' => 'ASC', 'age' => 'DESC'];
        $expected = 'ORDER BY `name` ASC, `age` DESC';
        $this->assertSame($expected, $this->builder->build($orderBy));
    }

    public function testBuildIgnoresInvalidColumns(): void
    {
        // 'invalid column' contains space, should be ignored by regex
        $orderBy = ['valid_col' => 'ASC', 'invalid column' => 'DESC'];
        $expected = 'ORDER BY `valid_col` ASC';
        $this->assertSame($expected, $this->builder->build($orderBy));
    }

    public function testBuildWithTableDotColumnSyntax(): void
    {
        $orderBy = ['users.name' => 'ASC'];
        $expected = 'ORDER BY `users`.`name` ASC';
        $this->assertSame($expected, $this->builder->build($orderBy));
    }

    public function testBuildWithDifferentQuoteChar(): void
    {
        $orderBy = ['name' => 'ASC'];
        $expected = 'ORDER BY "name" ASC';
        $this->assertSame($expected, $this->builder->build($orderBy, '"'));
    }

    public function testBuildJson(): void
    {
        $sql = $this->builder->buildJson('details', '$.level', 'DESC');
        $expected = "JSON_UNQUOTE(JSON_EXTRACT(`details`, '$.level')) DESC";
        $this->assertSame($expected, $sql);
    }

    public function testBuildJsonAddsDollarSignIfMissing(): void
    {
        // 'level' should become '$.level'
        $sql = $this->builder->buildJson('details', 'level', 'ASC');
        $expected = "JSON_UNQUOTE(JSON_EXTRACT(`details`, '$.level')) ASC";
        $this->assertSame($expected, $sql);
    }

    public function testBuildJsonHandlesDotPrefix(): void
    {
        // '.level' should become '$.level'
        $sql = $this->builder->buildJson('details', '.level', 'ASC');
        $expected = "JSON_UNQUOTE(JSON_EXTRACT(`details`, '$.level')) ASC";
        $this->assertSame($expected, $sql);
    }

    public function testBuildJsonEmptyColumnReturnsEmpty(): void
    {
        $this->assertSame('', $this->builder->buildJson('', '$.level', 'ASC'));
    }

    public function testBuildJsonEmptyPathReturnsEmpty(): void
    {
        $this->assertSame('', $this->builder->buildJson('details', '', 'ASC'));
    }

    public function testBuildJsonSanitizesColumnName(): void
    {
        // 'det;ails' -> 'details'
        $sql = $this->builder->buildJson('det;ails', '$.level', 'ASC');
        $expected = "JSON_UNQUOTE(JSON_EXTRACT(`details`, '$.level')) ASC";
        $this->assertSame($expected, $sql);
    }
}
