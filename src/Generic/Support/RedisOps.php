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
            // في Redis الحقيقي، del() دايماً بترجع int
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
            /** @var array<int, mixed> $keys */
            $keys = $this->driver->keys($pattern);

            $result = array_values(array_filter($keys, 'is_string'));
            /** @var list<string> $result */

            return $result;
        }

        // Generic object / FakeRedis-style drivers
        // Prefer a native `keys()` implementation when available.
        if (method_exists($this->driver, 'keys')) {
            /** @var array<int, mixed> $keys */
            $keys = $this->driver->keys($pattern);

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
