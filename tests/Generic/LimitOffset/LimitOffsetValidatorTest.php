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

        $this->assertTrue(true); // If no exception thrown, test passes
    }

    public function testInvalidLimitZero(): void
    {
        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage("Limit must be a positive integer.");
        LimitOffsetValidator::validate(0, null);
    }

    public function testInvalidLimitNegative(): void
    {
        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage("Limit must be a positive integer.");
        LimitOffsetValidator::validate(-1, null);
    }

    public function testInvalidOffsetNegative(): void
    {
        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage("Offset must be a non-negative integer.");
        LimitOffsetValidator::validate(null, -1);
    }
}
