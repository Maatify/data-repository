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
use Maatify\DataRepository\Generic\Pagination\LimitOffsetConfig;
use Maatify\DataRepository\Generic\Support\LimitOffsetValidator;
use Maatify\DataRepository\Generic\Support\RedisOps;
use Maatify\DataRepository\Generic\Support\Safety\RedisSafetyConfig;
use Maatify\DataRepository\Generic\Support\RepositoryHydrationTrait;
use Maatify\Common\Pagination\DTO\PaginationResultDTO;
use Maatify\Common\Pagination\Helpers\PaginationHelper;
use Predis\Client as PredisClient;
use Redis;

/**
 * @template T of object
 * @extends BaseRedisRepository<T>
 */
abstract class GenericRedisRepository extends BaseRedisRepository
{
    /** @use RepositoryHydrationTrait<T> */
    use RepositoryHydrationTrait;

    protected string $keyPrefix = '';

    private ?RedisOps $redisOps = null;

    protected function getLimitOffsetConfig(): ?LimitOffsetConfig
    {
        return null;
    }

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
        // 1. Fetch all items (inefficient for large sets, but required for in-memory filtering)
        $all = $this->findAll();

        // 2. Filter
        $filtered = [];
        foreach ($all as $item) {
            if ($this->matches($item, $filters)) {
                $filtered[] = $item;
            }
        }

        // 3. Sort
        if ($orderBy) {
            $filtered = Support\OrderUtils::sortArray($filtered, $orderBy);
        }

        // 4. Limit/Offset
        if ($limit !== null || $offset !== null) {
            $offset = $offset ?? 0;
            $limit = $limit ?? count($filtered); // if limit is null, take all
            $filtered = array_slice($filtered, $offset, $limit);
        }

        return array_values($filtered); // Re-index array
    }

    /**
     * @param   array<string, mixed>  $filters
     *
     * @return array<string, mixed>|null
     * @throws RepositoryException
     */
    public function findOneBy(array $filters): ?array
    {
        // Use findBy with limit 1
        $results = $this->findBy($filters, null, 1);

        return $results[0] ?? null;
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
            $this->redisOps = new RedisOps($this->getRedis(), $this->getRedisSafetyConfig());
        }

        return $this->redisOps;
    }

    /**
     * @return RedisSafetyConfig
     */
    protected function getRedisSafetyConfig(): RedisSafetyConfig
    {
        return new RedisSafetyConfig();
    }

    /**
     * @param   int   $page
     * @param   int   $perPage
     * @param   array<string, string>|null $orderBy
     *
     * @return PaginationResultDTO
     * @throws RepositoryException
     */
    public function paginate(int $page = 1, int $perPage = 10, ?array $orderBy = null): PaginationResultDTO
    {
        // Optimization: Fetch all keys first, then slice keys, then fetch only required values.
        // This avoids fetching and decoding the entire dataset when we only need a subset.

        if ($page < 1) {
            $page = 1;
        }
        if ($perPage < 1) {
            $perPage = 10;
        }

        /** @var array<int, string> $keys */
        $keys = $this->getRedisOps()->keys($this->keyPrefix . '*');
        $total = count($keys);

        $offset = ($page - 1) * $perPage;

        LimitOffsetValidator::validateWithConfig($perPage, $offset, $this->getLimitOffsetConfig());

        $pagedKeys = array_slice($keys, $offset, $perPage);

        $data = [];
        foreach ($pagedKeys as $key) {
            $content = $this->getRedisOps()->get($key);
            if ($content !== null) {
                $decoded = json_decode($content, true);
                if (is_array($decoded)) {
                    /** @var array<string, mixed> $decoded */
                    $data[] = $decoded;
                }
            }
        }

        $pagination = PaginationHelper::buildMeta($total, $page, $perPage);

        return new PaginationResultDTO($data, $pagination);
    }

    /**
     * @param   array<string, mixed>       $filters
     * @param   int                        $page
     * @param   int                        $perPage
     * @param   array<string, string>|null $orderBy
     *
     * @return PaginationResultDTO
     * @throws RepositoryException
     */
    public function paginateBy(array $filters, int $page = 1, int $perPage = 10, ?array $orderBy = null): PaginationResultDTO
    {
        if ($page < 1) {
            $page = 1;
        }
        if ($perPage < 1) {
            $perPage = 10;
        }

        // Get all filtered items
        $allFiltered = $this->findBy($filters, $orderBy);
        $total = count($allFiltered);

        // Slice for pagination
        $offset = ($page - 1) * $perPage;

        LimitOffsetValidator::validateWithConfig($perPage, $offset, $this->getLimitOffsetConfig());

        $data = array_slice($allFiltered, $offset, $perPage);

        $pagination = PaginationHelper::buildMeta($total, $page, $perPage);

        return new PaginationResultDTO($data, $pagination);
    }

    /**
     * @param array<string, mixed> $item
     * @param array<string, mixed> $filters
     */
    private function matches(array $item, array $filters): bool
    {
        foreach ($filters as $field => $value) {
            if (!array_key_exists($field, $item)) {
                return false;
            }

            // Simple equality check
            // For robust support, we should handle IN arrays
            if (is_array($value)) {
                // If it's a simple list, treat as IN
                if (array_keys($value) === range(0, count($value) - 1)) {
                    if (!in_array($item[$field], $value, true)) {
                        return false;
                    }
                } else {
                    // Operator map logic? For now, just false or minimal support
                    // Task says "NoSQL Robustness", let's try strict equality for arrays? No.
                    // Let's assume FilterUtils style operators are not passed here yet
                    // or treat as inequality match failure for complex structures.
                    return false;
                }
            } else {
                // Scalar equality
                if ($item[$field] != $value) {
                    return false;
                }
            }
        }
        return true;
    }
}
