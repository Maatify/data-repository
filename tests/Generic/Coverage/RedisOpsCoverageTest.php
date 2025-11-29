<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library    maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-25 09:20
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Generic\Coverage;

use Maatify\DataRepository\Generic\Support\RedisOps;
use PHPUnit\Framework\TestCase;
use Predis\Client;

class RedisOpsCoverageTest extends TestCase
{
    public function testKeysDelegation(): void
    {
        // Use a concrete fake class to avoid PHPUnit mock builder complexity with Predis magic methods
        $fake = new FakePredisClientForOps();
        $fake->expectedKeysPattern = '*';
        $fake->keysResult = ['key1', 'key2'];

        $ops = new RedisOps($fake);
        $result = $ops->keys('*');
        $this->assertEquals(['key1', 'key2'], $result);
    }

    public function testDelDelegation(): void
    {
        $fake = new FakePredisClientForOps();
        $fake->expectedDelKey = ['key1']; // RedisOps wraps single key in array for Predis
        $fake->delResult = 1;

        $ops = new RedisOps($fake);
        $result = $ops->del('key1');
        $this->assertEquals(1, $result);
    }
}

// Extends Client to pass instanceof PredisClient check in RedisOps
class FakePredisClientForOps extends Client
{
    public string $expectedKeysPattern = '';

    /** @var array<int, string> */
    public array $keysResult = [];

    /** @var array<int, string> */
    public array $expectedDelKey = [];

    public int $delResult = 0;

    public function __construct()
    {
        // Bypass parent constructor
    }

    /**
     * @return array<int, string>
     */
    public function keys(string $pattern): array
    {
        if ($pattern !== $this->expectedKeysPattern) {
            throw new \RuntimeException("Unexpected keys pattern: $pattern");
        }
        return $this->keysResult;
    }

    /**
     * @param array<int, string>|string $keys
     */
    public function del(array|string $keys): int
    {
        if ($keys !== $this->expectedDelKey) {
            throw new \RuntimeException('Unexpected del keys: ' . json_encode($keys));
        }
        return $this->delResult;
    }
}
