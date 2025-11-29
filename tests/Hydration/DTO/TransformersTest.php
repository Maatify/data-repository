<?php

declare(strict_types=1);

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     Maatify.dev Data Repository
 * @Project     maatify/data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-27 14:35:00
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

namespace Maatify\DataRepository\Tests\Hydration\DTO;

use DateTimeImmutable;
use Maatify\DataRepository\Hydration\Transformers\DateTimeTransformer;
use Maatify\DataRepository\Hydration\Transformers\JsonTransformer;
use PHPUnit\Framework\TestCase;

class TransformersTest extends TestCase
{
    public function testJsonTransformer(): void
    {
        $transformer = new JsonTransformer();

        // Valid JSON
        $json = '{"key":"value"}';
        $this->assertEquals(['key' => 'value'], $transformer->transform($json));

        // Already array
        $array = ['key' => 'value'];
        $this->assertEquals($array, $transformer->transform($array));

        // Invalid JSON
        $this->assertEquals([], $transformer->transform('invalid'));
    }

    public function testDateTimeTransformer(): void
    {
        $transformer = new DateTimeTransformer();

        // String date
        $dateStr = '2023-01-01 12:00:00';
        $result = $transformer->transform($dateStr);
        $this->assertInstanceOf(DateTimeImmutable::class, $result);
        $this->assertEquals($dateStr, $result->format('Y-m-d H:i:s'));

        // Timestamp
        $ts = 1672574400; // 2023-01-01 12:00:00 UTC
        $resultTs = $transformer->transform($ts);
        $this->assertInstanceOf(DateTimeImmutable::class, $resultTs);
        $this->assertEquals($ts, $resultTs->getTimestamp());

        // Null/Invalid
        $this->assertNull($transformer->transform(null));
        $this->assertNull($transformer->transform('invalid-date'));
    }

    public function testDateTimeTransformerWithFormat(): void
    {
        $transformer = new DateTimeTransformer('d/m/Y');
        $result = $transformer->transform('01/01/2023');

        $this->assertInstanceOf(DateTimeImmutable::class, $result);
        $this->assertEquals('2023-01-01', $result->format('Y-m-d'));
    }
}
