<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-25 05:35
 * @see         https://www.maatify.dev
 * @link        https://github.com/Maatify/data-repository
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Generic\LimitOffset;

use Maatify\DataRepository\Exceptions\RepositoryException;
use Maatify\DataRepository\Generic\Support\LimitOffsetValidator;
use PHPUnit\Framework\TestCase;

class LimitOffsetValidatorTest extends TestCase
{
    public function testValidInputs(): void
    {
        LimitOffsetValidator::validate(10, 0);
        LimitOffsetValidator::validate(null, null);
        LimitOffsetValidator::validate(1, 100);
        LimitOffsetValidator::validate(null, 5);
        LimitOffsetValidator::validate(5, null);
        LimitOffsetValidator::validate(LimitOffsetValidator::MAX_LIMIT, LimitOffsetValidator::MAX_OFFSET);

        $this->assertTrue(true);
    }

    public function testInvalidLimitZero(): void
    {
        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage("Invalid limit value: 0. Limit must be >= 1.");
        LimitOffsetValidator::validate(0, null);
    }

    public function testInvalidLimitNegative(): void
    {
        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage("Invalid limit value: -1. Limit must be >= 1.");
        LimitOffsetValidator::validate(-1, null);
    }

    public function testLimitExceedsMax(): void
    {
        $max = LimitOffsetValidator::MAX_LIMIT;
        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage("Limit " . ($max + 1) . " exceeds the maximum allowed ({$max}).");
        LimitOffsetValidator::validate($max + 1, null);
    }

    public function testInvalidOffsetNegative(): void
    {
        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage("Offset must be >= 0. Given: -1");
        LimitOffsetValidator::validate(null, -1);
    }

    public function testOffsetExceedsMax(): void
    {
        $max = LimitOffsetValidator::MAX_OFFSET;
        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage("Offset cannot exceed {$max}. Given: " . ($max + 1));
        LimitOffsetValidator::validate(null, $max + 1);
    }

    public function testNormalize(): void
    {
        $norm = LimitOffsetValidator::normalize(-5, -5);
        $this->assertSame(0, $norm['limit']); // min 0 (since max(0, min(limit, max))) -> if limit is -5, min(-5, 10000) is -5. max(0, -5) is 0.
        $this->assertSame(0, $norm['offset']);

        $norm = LimitOffsetValidator::normalize(LimitOffsetValidator::MAX_LIMIT + 100, LimitOffsetValidator::MAX_OFFSET + 100);
        $this->assertSame(LimitOffsetValidator::MAX_LIMIT, $norm['limit']);
        $this->assertSame(LimitOffsetValidator::MAX_OFFSET, $norm['offset']);
    }

    public function testValidateAndNormalize(): void
    {
        $res = LimitOffsetValidator::validateAndNormalize(50, 10);
        $this->assertSame(50, $res['limit']);
        $this->assertSame(10, $res['offset']);

        $this->expectException(RepositoryException::class);
        LimitOffsetValidator::validateAndNormalize(-1, 0);
    }
}
