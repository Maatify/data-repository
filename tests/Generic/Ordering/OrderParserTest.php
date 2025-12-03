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

use InvalidArgumentException;
use Maatify\DataRepository\Generic\Support\OrderField;
use Maatify\DataRepository\Generic\Support\OrderParser;
use PHPUnit\Framework\TestCase;

class OrderParserTest extends TestCase
{
    private OrderParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new OrderParser();
    }

    public function testParseEmpty(): void
    {
        $this->assertEmpty($this->parser->parse([]));
        $this->assertEmpty($this->parser->parse(null));
    }

    public function testParseValid(): void
    {
        $input = ['name' => 'ASC', 'age' => 'DESC'];
        $result = $this->parser->parse($input);

        $this->assertCount(2, $result);
        $this->assertInstanceOf(OrderField::class, $result[0]);
        $this->assertEquals('name', $result[0]->field);
        $this->assertEquals('ASC', $result[0]->direction);

        $this->assertEquals('age', $result[1]->field);
        $this->assertEquals('DESC', $result[1]->direction);
    }

    public function testInvalidDirectionFallsBackToAsc(): void
    {
        $input = ['score' => 'INVALID'];
        $result = $this->parser->parse($input);

        $this->assertCount(1, $result);
        $this->assertEquals('score', $result[0]->field);
        $this->assertEquals('ASC', $result[0]->direction);
    }

    public function testInvalidDirectionThrowsExceptionWhenRequested(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->parser->parse(['score' => 'INVALID'], true);
    }

    public function testSanitization(): void
    {
        $input = ['user;drop table' => 'ASC'];
        $result = $this->parser->parse($input);

        $this->assertCount(1, $result);
        $this->assertEquals('userdroptable', $result[0]->field);
    }

    public function testEmptyColumnIsIgnored(): void
    {
        $input = ['!@#$' => 'ASC'];
        $result = $this->parser->parse($input);

        $this->assertEmpty($result);
    }
}
