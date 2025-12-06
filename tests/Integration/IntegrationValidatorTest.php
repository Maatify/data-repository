<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-27 20:00
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Integration;

use PHPUnit\Framework\TestCase;

abstract class IntegrationValidatorTest extends TestCase
{
    // This class provides the structure for integrating with Real and Fake adapters.
    // It is designed to be extended by specific integration tests.

    protected function setUp(): void
    {
        // parent::setUp();
        // Here we would typically set up the environment or connections.
    }

    /**
     * @param array<string, mixed> $expected
     * @param array<string, mixed> $actual
     */
    protected function assertArraysEqual(array $expected, array $actual, string $message = ''): void
    {
        ksort($expected);
        ksort($actual);
        $this->assertEquals($expected, $actual, $message);
    }
}
