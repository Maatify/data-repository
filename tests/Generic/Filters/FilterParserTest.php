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

namespace Maatify\DataRepository\Tests\Generic\Filters;

use InvalidArgumentException;
use Maatify\DataRepository\Generic\Support\FilterParser;
use PHPUnit\Framework\TestCase;

class FilterParserTest extends TestCase
{
    private FilterParser $parser;

    protected function setUp(): void
    {
        $this->parser = new FilterParser();
    }

    public function testParseEmpty(): void
    {
        $this->assertEmpty($this->parser->parse([]));
    }

    public function testParseSimpleEquality(): void
    {
        $filters = ['status' => 1];
        $result = $this->parser->parse($filters);

        $this->assertCount(1, $result);
        $this->assertSame('status', $result[0]->field);
        $this->assertSame('=', $result[0]->operator);
        $this->assertSame(1, $result[0]->value);
    }

    public function testParseSimpleNull(): void
    {
        $filters = ['deleted_at' => null];
        $result = $this->parser->parse($filters);

        $this->assertCount(1, $result);
        $this->assertSame('deleted_at', $result[0]->field);
        $this->assertSame('IS NULL', $result[0]->operator);
        $this->assertNull($result[0]->value);
    }

    public function testParseOperators(): void
    {
        $filters = [
            'age' => [
                '>' => 18,
                '<' => 30
            ]
        ];
        $result = $this->parser->parse($filters);

        $this->assertCount(2, $result);

        $this->assertSame('age', $result[0]->field);
        $this->assertSame('>', $result[0]->operator);
        $this->assertSame(18, $result[0]->value);

        $this->assertSame('age', $result[1]->field);
        $this->assertSame('<', $result[1]->operator);
        $this->assertSame(30, $result[1]->value);
    }

    public function testParseMixed(): void
    {
        $filters = [
            'status' => 'active',
            'role' => ['IN' => [1, 2]]
        ];
        $result = $this->parser->parse($filters);

        $this->assertCount(2, $result);

        $this->assertSame('status', $result[0]->field);
        $this->assertSame('=', $result[0]->operator);
        $this->assertSame('active', $result[0]->value);

        $this->assertSame('role', $result[1]->field);
        $this->assertSame('IN', $result[1]->operator);
        $this->assertSame([1, 2], $result[1]->value);
    }

    public function testParseInvalidOperator(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Unsupported operator 'INVALID'");

        $this->parser->parse(['age' => ['INVALID' => 10]]);
    }
}
