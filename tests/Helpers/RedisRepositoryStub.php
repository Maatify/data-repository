<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Liberary    maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-27 09:41
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Helpers;

use Maatify\DataRepository\Generic\GenericRedisRepository;
use Maatify\DataRepository\Generic\Support\RedisOps;

/**
 * @template T of object
 * @extends GenericRedisRepository<T>
 */
class RedisRepositoryStub extends GenericRedisRepository
{
    protected string $keyPrefix = 'test:';
    public function find(int|string $id): ?array
    {
        return null;
    }

    /**
     * @param   array<string, mixed>        $filters
     * @param   array<string, string>|null  $orderBy
     *
     * @return array<int, array<string, mixed>>
     */
    public function findBy(array $filters, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        return [];
    }

    /**
     * @param   array<string, mixed>  $filters
     *
     * @return array<string, mixed>|null
     */
    public function findOneBy(array $filters): ?array
    {
        return null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function findAll(): array
    {
        return [];
    }

    /**
     * @param   array<string, mixed>  $filters
     */
    public function count(array $filters = []): int
    {
        return 0;
    }

    /**
     * @param   array<string, mixed>  $data
     *
     * @return int|string
     */
    public function insert(array $data): int|string
    {
        return 1;
    }

    /**
     * @param   array<string, mixed>  $data
     */
    public function update(int|string $id, array $data): bool
    {
        return true;
    }

    public function delete(int|string $id): bool
    {
        return true;
    }

    public function getOps(): RedisOps
    {
        // force initializes the ops (lazy load)
        $this->getRedisOps();

        $ref = new \ReflectionClass(GenericRedisRepository::class);
        $prop = $ref->getProperty('redisOps');
        $prop->setAccessible(true);

        /** @var RedisOps $ops */
        $ops = $prop->getValue($this);

        return $ops;
    }
}
