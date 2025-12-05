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

namespace Maatify\DataRepository\Tests\Generic\Normalization;

use Maatify\DataRepository\Generic\Support\NormalizerOptions;
use Maatify\DataRepository\Generic\Support\ResultNormalizer;
use PHPUnit\Framework\TestCase;

class NormalizerOptionsTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $options = NormalizerOptions::create();

        $this->assertFalse($options->shouldKeepMongoId());
        $this->assertFalse($options->isRecursive());
        $this->assertTrue($options->isStrictIdTypes());
    }

    public function testImmutabilityAndSetters(): void
    {
        $options = NormalizerOptions::create();

        $newOptions = $options->withKeepMongoId(true);
        $this->assertNotSame($options, $newOptions);
        $this->assertTrue($newOptions->shouldKeepMongoId());
        $this->assertFalse($options->shouldKeepMongoId());

        $newOptions2 = $newOptions->withRecursive(true);
        $this->assertNotSame($newOptions, $newOptions2);
        $this->assertTrue($newOptions2->isRecursive());
        $this->assertFalse($newOptions->isRecursive());

        $newOptions3 = $newOptions2->withStrictIdTypes(false);
        $this->assertNotSame($newOptions2, $newOptions3);
        $this->assertFalse($newOptions3->isStrictIdTypes());
        $this->assertTrue($newOptions2->isStrictIdTypes());
    }

    public function testIntegrationWithResultNormalizer(): void
    {
        $options = NormalizerOptions::create()
            ->withKeepMongoId(true)
            ->withRecursive(true)
            ->withStrictIdTypes(false);

        $row = [
            '_id' => 123,
            'data' => [
                'nested' => 'value'
            ]
        ];

        // ResultNormalizer::normalizeWithOptions
        $normalized = ResultNormalizer::normalizeWithOptions($row, $options);

        $this->assertArrayHasKey('_id', $normalized); // Kept because of keepMongoId(true)
        $this->assertArrayHasKey('id', $normalized);
        $this->assertEquals(123, $normalized['id']);
        $this->assertEquals(123, $normalized['_id']);

        // Recursive check logic - if recursive was false, 'data' might be untouched or handled differently depending on implementation
        // But here we just check that options are respected.
    }
}
