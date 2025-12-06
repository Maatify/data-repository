<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-29 02:07
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Generic\Normalization;

use InvalidArgumentException;
use Maatify\DataRepository\Generic\Support\ResultNormalizer;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class ResultNormalizerCoverageTest extends TestCase
{
    public function testAssertAssocRowThrowsExceptionForInvalidKeys(): void
    {
        // We need an array that is NOT a list (so we enter assertAssocRow)
        // AND has a non-string key (so assertAssocRow throws).
        // Example: [1 => 'a'] is not a list (keys not starting at 0).
        // array_is_list([1 => 'a']) is false (on PHP 8.1+).
        // assertAssocRow checks if key '1' is string. It is int. Throws.

        $invalidRow = [1 => 'value'];

        $normalizer = ResultNormalizer::create()->recursive(true);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('normalizeRowArray() can only be called on associative (string-keyed) arrays.');

        // Pass it wrapped in another array so it triggers the recursive check on the value
        $normalizer->normalizeRow(['data' => $invalidRow]);
    }

    public function testNormalizeStrictIdWithUnsupportedTypeViaReflection(): void
    {
        // verify normalizeStrictId throws exception for unsupported types
        // This is normally unreachable because isIdValue filters types before calling normalizeStrictId
        // But we want to ensure the method itself is robust (defensive coding).

        $normalizer = ResultNormalizer::create();
        $ref = new ReflectionClass($normalizer);
        $method = $ref->getMethod('normalizeStrictId');
        $method->setAccessible(true);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported ID type: double');

        $method->invoke($normalizer, 1.5);
    }

    public function testNormalizeStrictIdWithInvalidStringViaReflection(): void
    {
        // verify normalizeStrictId throws exception for invalid string
        // This is normally unreachable because isIdValue filters invalid strings

        $normalizer = ResultNormalizer::create();
        $ref = new ReflectionClass($normalizer);
        $method = $ref->getMethod('normalizeStrictId');
        $method->setAccessible(true);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid ID format: "invalid-id"');

        $method->invoke($normalizer, 'invalid-id');
    }
}
