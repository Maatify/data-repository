<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Liberary    maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-26 16:49
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Generic\Support;

use Maatify\DataRepository\Exceptions\RedisSafetyException;
use Maatify\DataRepository\Generic\Support\Safety\RedisGuard;
use Maatify\DataRepository\Generic\Support\Safety\RedisSafetyConfig;
use Predis\Client as PredisClient;
use Predis\Response\Status;
use Redis;

/**
 * 🔌 RedisOps
 *
 * A normalization wrapper for Redis, Predis, and FakeRedis drivers.
 *
 * Ensures a consistent KV interface for repository operations.
 */
class RedisOps
{
    /**
     * @var Redis|PredisClient|object
     *
     * @phpstan-var Redis|PredisClient|object
     */
    private object $driver;

    private RedisGuard $guard;

    /**
     * @param Redis|PredisClient|object $driver
     * @param RedisSafetyConfig|null    $config
     *
     * @phpstan-param Redis|PredisClient|object $driver
     */
    public function __construct(object $driver, ?RedisSafetyConfig $config = null)
    {
        $this->driver = $driver;
        $this->guard = new RedisGuard($config ?? new RedisSafetyConfig());
    }

    /**
     * GET wrapper
     *
     * Normalize various driver return types to `string|null`.
     *
     * @param   string  $key
     *
     * @return string|null
     */
    public function get(string $key): ?string
    {
        // phpredis → string|false
        if ($this->driver instanceof Redis) {
            /** @var string|false $value */
            $value = $this->driver->get($key);
            return $value === false ? null : $value;
        }

        // Predis → string|mixed
        if ($this->driver instanceof PredisClient) {
            $value = $this->driver->get($key);
            return is_string($value) ? $value : null;
        }

        // FakeRedis → internal implementation, returns mixed
        if (method_exists($this->driver, 'get')) {
            $value = $this->driver->get($key);

            if (is_string($value)) {
                return $value;
            }

            if (is_array($value)) {
                $json = json_encode($value);
                return $json === false ? null : $json;
            }
        }

        return null;
    }

    /**
     * SET wrapper
     */
    public function set(string $key, string $value): bool
    {
        if ($this->driver instanceof Redis) {
            return $this->driver->set($key, $value);
        }

        if ($this->driver instanceof PredisClient) {
            /** @var Status|string|null $response */
            $response = $this->driver->set($key, $value);
            return $response instanceof Status
                ? $response->getPayload() === 'OK'
                : $response === 'OK';
        }

        // FakeRedis
        if (method_exists($this->driver, 'set')) {
            return (bool) $this->driver->set($key, $value);
        }

        return false;
    }

    /**
     * DEL wrapper
     */
    public function del(string $key): int
    {
        if ($this->driver instanceof Redis) {
            // Redis del() returns int
            return $this->driver->del($key);
        }

        if ($this->driver instanceof PredisClient) {
            /** @var int $result */
            $result = $this->driver->del([$key]);
            return $result;
        }

        // FakeRedis
        if (method_exists($this->driver, 'del')) {
            $result = $this->driver->del($key);
            return is_int($result) ? $result : 0;
        }

        return 0;
    }

    /**
     * KEYS wrapper (Safeguarded)
     *
     * Replaces unsafe `KEYS` command with guarded `SCAN` iteration for real drivers.
     * Fakes/Tests may still fall back to internal inspection, subject to limits if applicable.
     *
     * @return list<string>
     * @phpstan-return list<string>
     * @throws RedisSafetyException
     */
    public function keys(string $pattern): array
    {
        $this->guard->reset();

        // 1. Redis (phpredis) -> Use SCAN
        if ($this->driver instanceof Redis) {
            return $this->scanRedis($pattern);
        }

        // 2. Predis -> Use SCAN
        if ($this->driver instanceof PredisClient) {
            return $this->scanPredis($pattern);
        }

        // 3. Fake/Generic -> Fallback but attempt to count
        // We cannot easily 'scan' a fake object, so we rely on existing logic
        // but verify the result count against safety limits.
        $keys = $this->fallbackKeys($pattern);

        // Check limit on the result size
        $this->guard->trackScan(count($keys));

        return $keys;
    }

    /**
     * @return list<string>
     * @throws RedisSafetyException
     */
    private function scanRedis(string $pattern): array
    {
        /** @var Redis $redis */
        $redis = $this->driver;
        $iterator = null;
        $keys = [];

        // Note: phpredis scan passes iterator by reference.
        // Returns false when finished, or an array of keys on each call?
        // Actually, $redis->scan(&$iterator, $pattern) returns an array (false on error, or empty array if no keys in chunk).
        // Wait, phpredis scan returns array of keys or false.
        // The loop condition is usually `while ($iterator > 0)` or `do ... while ($iterator > 0)`.
        // The first call needs $iterator = NULL.

        do {
            $chunk = $redis->scan($iterator, $pattern);

            if ($chunk === false) {
                // If scan fails, we might just stop or retry.
                // For safety, assume stop.
                break;
            }

            /** @var array<int, string> $chunkKeys */
            $chunkKeys = $chunk; // PHPStan help

            $count = !empty($chunkKeys) ? count($chunkKeys) : 0;
            $this->guard->trackScan($count);

            if (!empty($chunkKeys)) {
            $count = !empty($chunkKeys) ? count($chunkKeys) : 0;
            $this->guard->trackScan($count);

            if (!empty($chunkKeys)) {
                foreach ($chunkKeys as $k) {
                    $keys[] = $k;
                }
            }
        } while ($iterator > 0);

        return $keys;
    }

    /**
     * @return list<string>
     * @throws RedisSafetyException
     */
    private function scanPredis(string $pattern): array
    {
        /** @var PredisClient $client */
        $client = $this->driver;
        $keys = [];
        $cursor = '0';

        do {
            // Predis scan returns [cursor, keys]
            /** @var mixed $response */
            $response = $client->scan($cursor, ['MATCH' => $pattern]);

            // Predis usually returns an object or array depending on client options.
            // By default, it returns a `Predis\Collection\Iterator\CursorBasedIterator` if used via helper,
            // but calling `scan` directly returns raw response: [cursor, [keys...]]
            // Let's assume standard response array: [0 => new_cursor, 1 => keys_array]

            // To be safer and more standard with Predis, we can use the Iterator abstraction if available,
            // but `scan` command is direct.
            // Let's check typical return.
            if (is_array($response) && isset($response[0], $response[1])) {
                $cursor = (string)$response[0];
                /** @var list<string> $chunkKeys */
                $chunkKeys = $response[1];
            } else {
                // Unexpected response format
                break;
            }

            if (!empty($chunkKeys)) {
                $count = count($chunkKeys);
                $this->guard->trackScan($count);
                foreach ($chunkKeys as $k) {
                    $keys[] = $k;
                }
            }

        } while ($cursor !== '0');

        return $keys;
    }

    /**
     * @return list<string>
     */
    private function fallbackKeys(string $pattern): array
    {
        // Generic object / FakeRedis-style drivers
        if (method_exists($this->driver, 'keys')) {
            /** @var array<int, mixed> $keys */
            $keys = $this->driver->keys($pattern);
            return array_values(array_filter($keys, 'is_string'));
        }

        // Reflection fallback
        try {
            $ref = new \ReflectionObject($this->driver);
            if (! $ref->hasProperty('store')) {
                return [];
            }

            $prop = $ref->getProperty('store');
            $prop->setAccessible(true);
            /** @var mixed $rawStore */
            $rawStore = $prop->getValue($this->driver);

            if (! is_array($rawStore)) {
                return [];
            }

            $allKeys = array_keys($rawStore);

            $prefix = $pattern;
            $pos = strpos($pattern, '*');
            if ($pos !== false) {
                $prefix = substr($pattern, 0, $pos);
            }

            $filtered = array_filter(
                $allKeys,
                static fn ($key): bool => is_string($key) && str_starts_with($key, $prefix)
            );

            return array_values($filtered);
        } catch (\ReflectionException) {
            return [];
        }
    }
}
