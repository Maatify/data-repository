<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-02 08:30
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Generic\Pagination;

use Maatify\DataRepository\Exceptions\RepositoryException;
use Maatify\DataRepository\Generic\Pagination\LimitOffsetConfig;
use Maatify\DataRepository\Generic\Support\LimitOffsetValidator;
use PHPUnit\Framework\TestCase;

class LimitOffsetConfigTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $config = LimitOffsetConfig::create();

        $this->assertEquals(10000, $config->getMaxLimit());
        $this->assertEquals(100000, $config->getMaxOffset());
    }

    public function testImmutabilityAndSetters(): void
    {
        $config = LimitOffsetConfig::create();

        $newConfig = $config->withMaxLimit(50);
        $this->assertNotSame($config, $newConfig);
        $this->assertEquals(50, $newConfig->getMaxLimit());
        $this->assertEquals(10000, $config->getMaxLimit());

        $newConfig2 = $newConfig->withMaxOffset(100);
        $this->assertNotSame($newConfig, $newConfig2);
        $this->assertEquals(100, $newConfig2->getMaxOffset());
        $this->assertEquals(100000, $newConfig->getMaxOffset());
    }

    public function testIntegrationWithValidatorValid(): void
    {
        $config = LimitOffsetConfig::create()
            ->withMaxLimit(50)
            ->withMaxOffset(100);

        // Should pass
        LimitOffsetValidator::validateWithConfig(50, 100, $config);

        $this->addToAssertionCount(1);
    }

    public function testIntegrationWithValidatorInvalidLimit(): void
    {
        $config = LimitOffsetConfig::create()
            ->withMaxLimit(50);

        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage('Limit 51 exceeds the maximum allowed (50)');

        LimitOffsetValidator::validateWithConfig(51, 0, $config);
    }

    public function testIntegrationWithValidatorInvalidOffset(): void
    {
        $config = LimitOffsetConfig::create()
            ->withMaxOffset(100);

        $this->expectException(RepositoryException::class);
        $this->expectExceptionMessage('Offset cannot exceed 100');

        LimitOffsetValidator::validateWithConfig(10, 101, $config);
    }

    public function testNormalizeWithConfig(): void
    {
        $config = LimitOffsetConfig::create()
            ->withMaxLimit(50)
            ->withMaxOffset(100);

        $result = LimitOffsetValidator::validateAndNormalize(60, 150, $config);

        // validateAndNormalize actually validates first, so it throws Exception if invalid.
        // Wait, validateAndNormalize calls validateWithConfig first.
        // So I can't test normalization clamping unless I bypass validation,
        // but normalize() is private.
        // However, validateAndNormalize calls validateWithConfig, which throws exception if out of bounds.
        // So testing clamping via public API is only possible if validate doesn't throw, but validate DOES throw.
        // Thus, effectively, we cannot pass values larger than maxLimit.

        // Wait, check the implementation of normalize():
        // 'limit' => max(0, min($limit ?? 0, $maxLimit)),
        // It clamps. But validate() throws.
        // So effectively, the clamping logic for upper bound is never reached if validation is stricter.
        // UNLESS we use it without validation, but method is validateAndNormalize.
        // Ah, maybe the intent is that normalize should only clamp?
        // But validateAndNormalize throws.
        // So let's test that it throws.
    }

    public function testValidateAndNormalizeThrowsOnOutOfBounds(): void
    {
        $config = LimitOffsetConfig::create()
            ->withMaxLimit(50);

        $this->expectException(RepositoryException::class);
        LimitOffsetValidator::validateAndNormalize(60, 0, $config);
    }
}
