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

use Predis\Client as PredisClient;
use Redis;

/**
 * 🔌 RedisOps
 *
 * A normalization wrapper for Redis, Predis, and FakeRedis drivers.
 *
 * Ensures a consistent KV interface for repository operations.
 */
final class RedisOps
{
    /**
     * @var Redis|PredisClient|object
     *
     * @phpstan-var Redis|PredisClient|object
     */
    private object $driver;

    /**
     * @param Redis|PredisClient|object $driver
     *
     * @phpstan-param Redis|PredisClient|object $driver
     */
    public function __construct(object $driver)
    {
        $this->driver = $driver;
    }

    /**
     * GET wrapper
     *
     * Normalize various driver return types to `string|null`.
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
            /** @phpstan-ignore-next-line dynamic Redis command on Predis client */
            $value = $this->driver->get($key);
            return is_string($value) ? $value : null;
        }

        // FakeRedis → internal implementation, returns mixed
        /** @phpstan-ignore-next-line dynamic Redis-like get() on fake driver */
        $value = $this->driver->get($key);

        if (is_string($value)) {
            return $value;
        }

        if (is_array($value)) {
            $json = json_encode($value);

            return $json === false ? null : $json;
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
            /** @phpstan-ignore-next-line dynamic Redis command on Predis client */
            $res = $this->driver->set($key, $value);
            return $res === true || $res === 'OK';
        }

        // FakeRedis
        /** @phpstan-ignore-next-line dynamic Redis-like set() on fake driver */
        return (bool)$this->driver->set($key, $value);
    }

    /**
     * DEL wrapper
     */
    public function del(string $key): int
    {
        if ($this->driver instanceof Redis) {
            return (int)$this->driver->del($key);
        }

        if ($this->driver instanceof PredisClient) {
            /** @phpstan-ignore-next-line dynamic Redis command on Predis client */
            $res = $this->driver->del([$key]);
            return is_int($res) ? $res : 0;
        }

        // FakeRedis
        /** @phpstan-ignore-next-line dynamic Redis-like del() on fake driver */
        $res = $this->driver->del($key);
        return is_int($res) ? $res : 0;
    }

    /**
     * KEYS wrapper
     *
     * @return list<string>
     * @phpstan-return list<string>
     */
    public function keys(string $pattern): array
    {
        if ($this->driver instanceof Redis) {
            /** @var array<int, mixed> $keys */
            $keys = $this->driver->keys($pattern);

            $result = array_values(array_filter($keys, 'is_string'));
            /** @var list<string> $result */

            return $result;
        }

        if ($this->driver instanceof PredisClient) {
            /** @phpstan-ignore-next-line dynamic Redis command on Predis client */
            $keys = $this->driver->keys($pattern);
            /** @var array<int, mixed> $keys */

            $result = array_values(array_filter($keys, 'is_string'));
            /** @var list<string> $result */

            return $result;
        }

        // Generic object / FakeRedis-style drivers
        // Prefer a native `keys()` implementation when available.
        if (method_exists($this->driver, 'keys')) {
            $keys = $this->driver->keys($pattern);
            /** @var array<int, mixed> $keys */

            $result = array_values(array_filter($keys, 'is_string'));
            /** @var list<string> $result */

            return $result;
        }

        // As a last resort (for fakes that expose an internal `$store` without
        // a dedicated `keys()` API, e.g. FakeRedisAdapter), attempt to
        // introspect the public/protected/private `store` property via
        // reflection. This keeps the repository adapter-agnostic while still
        // enabling efficient key scans in tests.
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

            // Only support simple "prefix*" patterns for fakes, which is what
            // GenericRedisRepository uses (keyPrefix + '*').
            $prefix = $pattern;
            $pos = strpos($pattern, '*');
            if ($pos !== false) {
                $prefix = substr($pattern, 0, $pos);
            }

            $filtered = array_filter(
                $allKeys,
                static fn ($key): bool => is_string($key) && str_starts_with($key, $prefix)
            );

            $result = array_values($filtered);
            /** @var list<string> $result */

            return $result;
        } catch (\ReflectionException) {
            // If reflection fails for any reason, fall back to an empty set to
            // avoid fatal errors while keeping behavior deterministic.
            return [];
        }
    }
}
