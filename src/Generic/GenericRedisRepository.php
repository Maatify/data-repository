<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library    maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-25 03:05
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Generic;

use Maatify\DataRepository\Base\BaseRedisRepository;
use Maatify\DataRepository\Exceptions\RepositoryException;
use Maatify\DataRepository\Generic\Support\RedisOps;
use Predis\Client as PredisClient;
use Redis;

abstract class GenericRedisRepository extends BaseRedisRepository
{
    use RepositoryHydrationTrait;

    protected string $keyPrefix = '';

    private ?RedisOps $redisOps = null;

    /**
     * @return array<string, mixed>|null
     * @throws RepositoryException
     */
    public function find(int|string $id): ?array
    {
        try {
            $key = $this->getKey($id);

            $data = $this->getRedisOps()->get($key);

            if ($data === null) {
                return null;
            }

            $decoded = json_decode($data, true);

            if (! is_array($decoded)) {
                return null;
            }

            /** @var array<string, mixed> $decoded */
            return $decoded;
        } catch (\Exception $e) {
            throw new RepositoryException('Find failed: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * @throws RepositoryException
     */
    public function insert(array $data): int|string
    {
        if (! isset($data['id'])) {
            throw new RepositoryException("Generic Redis Insert requires 'id' in data payload.");
        }

        $id = $data['id'];

        if (! is_int($id) && ! is_string($id)) {
            throw new RepositoryException("Generic Redis Insert 'id' must be int|string.");
        }
        $key = $this->getKey($id);

        $payload = json_encode($data);
        if ($payload === false) {
            throw new RepositoryException('Failed to JSON-encode data for Redis insert.');
        }

        try {
            $this->getRedisOps()->set($key, $payload);
        } catch (\Exception $e) {
            throw new RepositoryException('Insert failed: ' . $e->getMessage(), 0, $e);
        }

        return $id;
    }

    /**
     * @throws RepositoryException
     */
    public function update(int|string $id, array $data): bool
    {
        $existing = $this->find($id);
        if ($existing === null) {
            return false;
        }

        $merged = array_merge($existing, $data);
        $merged['id'] = $id;

        $payload = json_encode($merged);
        if ($payload === false) {
            throw new RepositoryException('Failed to JSON-encode data for Redis update.');
        }

        try {
            return $this->getRedisOps()->set($this->getKey($id), $payload);
        } catch (\Exception $e) {
            throw new RepositoryException('Update failed: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * @throws RepositoryException
     */
    public function delete(int|string $id): bool
    {
        try {
            return $this->getRedisOps()->del($this->getKey($id)) > 0;
        } catch (\Exception $e) {
            throw new RepositoryException('Delete failed: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * @param   array<string, mixed>        $filters
     * @param   array<string, string>|null  $orderBy
     *
     * @return array<int, array<string, mixed>>
     * @throws RepositoryException
     */
    public function findBy(array $filters, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        throw new RepositoryException('findBy() is not supported in GenericRedisRepository (Key-Value store). Use find() by ID.');
    }

    /**
     * @param   array<string, mixed>  $filters
     *
     * @return array<string, mixed>|null
     * @throws RepositoryException
     */
    public function findOneBy(array $filters): ?array
    {
        throw new RepositoryException('findOneBy() is not supported in GenericRedisRepository.');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function findAll(): array
    {
        /** @var array<int, string> $keys */
        $keys = $this->getRedisOps()->keys($this->keyPrefix . '*');

        /** @var array<int, array<string, mixed>> $results */
        $results = [];
        foreach ($keys as $key) {
            $data = $this->getRedisOps()->get($key);
            if ($data === null) {
                continue;
            }

            $decoded = json_decode($data, true);
            if (! is_array($decoded)) {
                continue;
            }

            /** @var array<string, mixed> $decoded */
            $results[] = $decoded;
        }

        return $results;
    }

    /**
     * @param   array<string, mixed>  $filters
     *
     * @throws RepositoryException
     */
    public function count(array $filters = []): int
    {
        if (! empty($filters)) {
            throw new RepositoryException('Filtering count is not supported in Redis.');
        }
        /** @var array<int, string> $keys */
        $keys = $this->getRedisOps()->keys($this->keyPrefix . '*');

        return count($keys);
    }

    private function getKey(int|string $id): string
    {
        return $this->keyPrefix . $id;
    }

    /**
     * @return Redis|PredisClient
     *
     * @phpstan-return Redis|PredisClient
     */
    private function getRedis(): object
    {
        /** @var Redis|PredisClient $driver */
        $driver = $this->getDriver();

        return $driver;
    }

    /**
     * Lazily create a RedisOps helper wired to the current Redis driver.
     * (Integrated via Phase 9 Ops)
     */
    protected function getRedisOps(): RedisOps
    {
        if ($this->redisOps === null) {
            $this->redisOps = new RedisOps($this->getRedis());
        }

        return $this->redisOps;
    }
}
