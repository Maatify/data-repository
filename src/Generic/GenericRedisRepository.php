<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Liberary    maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-25 03:05
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-repository  view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Generic;

use Maatify\DataRepository\Base\BaseRedisRepository;
use Maatify\DataRepository\Exceptions\RepositoryException;
use Predis\Client as PredisClient;
use Predis\Response\Status as PredisStatus;
use Redis;

abstract class GenericRedisRepository extends BaseRedisRepository
{
    protected string $keyPrefix = '';

    /**
     * @return array<string, mixed>|null
     */
    public function find(int|string $id): ?array
    {
        $key = $this->getKey($id);

        // Redis/Predis get() returns string|false|null
        /** @var string|false|null $data */
        $data = $this->getRedis()->get($key);

        if ($data === false || $data === null) {
            return null;
        }

        $decoded = json_decode($data, true);

        if (! is_array($decoded)) {
            return null;
        }

        /** @var array<string, mixed> $decoded */
        return $decoded;
    }

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

        $this->getRedis()->set($key, json_encode($data));

        return $id;
    }

    public function update(int|string $id, array $data): bool
    {
        $existing = $this->find($id);
        if ($existing === null) {
            return false;
        }

        $merged = array_merge($existing, $data);
        $merged['id'] = $id;

        /** @var bool|PredisStatus $response */
        $response = $this->getRedis()->set($this->getKey($id), json_encode($merged));

        // Normalize Predis status object
        if ($response === true) {
            return true;
        }

        if ($response instanceof PredisStatus) {
            return $response->getPayload() === 'OK';
        }

        return false;
    }

    public function delete(int|string $id): bool
    {
        return (bool)$this->getRedis()->del($this->getKey($id));
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
        $keys = $this->getRedis()->keys($this->keyPrefix . '*');

        /** @var array<int, array<string, mixed>> $results */
        $results = [];
        foreach ($keys as $key) {
            /** @var string|false|null $data */
            $data = $this->getRedis()->get($key);
            if ($data === false || $data === null) {
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
        $keys = $this->getRedis()->keys($this->keyPrefix . '*');

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
}
