<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library    maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-25 03:03
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Generic;

use Maatify\DataRepository\Base\BaseMongoRepository;
use Maatify\DataRepository\Exceptions\RepositoryException;
use Maatify\DataRepository\Generic\Support\FilterUtils;
use Maatify\DataRepository\Generic\Support\LimitOffsetValidator;
use Maatify\DataRepository\Generic\Support\MongoOps;
use Maatify\DataRepository\Generic\Support\OrderUtils;
use Maatify\DataRepository\Generic\Support\RepositoryHydrationTrait;
use Maatify\Common\Pagination\DTO\PaginationResultDTO;
use Maatify\Common\Pagination\Helpers\PaginationHelper;
use MongoDB\Collection;
use MongoDB\BSON\ObjectId;

abstract class GenericMongoRepository extends BaseMongoRepository
{
    use RepositoryHydrationTrait;

    protected string $collectionName = '';

    private ?MongoOps $mongoOps = null;

    /**
     * @return array<string, mixed>|null
     * @throws RepositoryException
     */
    public function find(int|string $id): ?array
    {
        try {
            $filter = $this->buildIdFilter($id);
            /** @var array<string, mixed>|object|null $result */
            $result = $this->getCollectionObj()->findOne($filter);

            return $this->getMongoOps()->toArray($result);
        } catch (\Exception $e) {
            throw new RepositoryException('Find failed: ' . $e->getMessage(), 0, $e);
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
        LimitOffsetValidator::validate($limit, $offset);

        try {
            $normalizedFilters = FilterUtils::buildMongoFilter($filters);

            $options = [];
            if ($orderBy) {
                $options['sort'] = OrderUtils::buildMongoSort($orderBy);
            }
            if ($limit !== null) {
                $options['limit'] = $limit;
            }
            if ($offset !== null) {
                $options['skip'] = $offset;
            }

            $cursor = $this->getCollectionObj()->find($normalizedFilters, $options);

            return $this->getMongoOps()->cursorToArray($cursor);
        } catch (\Exception $e) {
            throw new RepositoryException('FindBy failed: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * @param   array<string, mixed>  $filters
     *
     * @return array<string, mixed>|null
     * @throws RepositoryException
     */
    public function findOneBy(array $filters): ?array
    {
        try {
            $normalizedFilters = FilterUtils::buildMongoFilter($filters);

            /** @var array<string, mixed>|object|null $result */
            $result = $this->getCollectionObj()->findOne($normalizedFilters);

            return $this->getMongoOps()->toArray($result);
        } catch (\Exception $e) {
            throw new RepositoryException('FindOneBy failed: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     * @throws RepositoryException
     */
    public function findAll(): array
    {
        return $this->findBy([]);
    }

    /**
     * @param   array<string, mixed>  $filters
     *
     * @throws RepositoryException
     */
    public function count(array $filters = []): int
    {
        try {
            $normalizedFilters = FilterUtils::buildMongoFilter($filters);

            return $this->getCollectionObj()->countDocuments($normalizedFilters);
        } catch (\Exception $e) {
            throw new RepositoryException('Count failed: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * @throws RepositoryException
     */
    public function insert(array $data): int|string
    {
        try {
            $result = $this->getCollectionObj()->insertOne($data);
            $id = $result->getInsertedId();

            $normalizedId = $this->getMongoOps()->normalizeInsertedId($id);

            if ($normalizedId === '') {
                throw new RepositoryException('Insert failed: received invalid ID type from driver.');
            }

            return $normalizedId;
        } catch (\Exception $e) {
            if ($e instanceof RepositoryException) {
                throw $e;
            }
            throw new RepositoryException('Insert failed: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * @throws RepositoryException
     */
    public function update(int|string $id, array $data): bool
    {
        try {
            $filter = $this->buildIdFilter($id);
            $result = $this->getCollectionObj()->updateOne($filter, ['$set' => $data]);

            return $result->getMatchedCount() > 0;
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
            $filter = $this->buildIdFilter($id);
            $result = $this->getCollectionObj()->deleteOne($filter);

            return $result->getDeletedCount() > 0;
        } catch (\Exception $e) {
            throw new RepositoryException('Delete failed: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * @throws RepositoryException
     */
    private function getCollectionObj(): Collection
    {
        if (empty($this->collectionName)) {
            if (empty($this->tableName)) {
                throw new RepositoryException('Collection name not defined for GenericMongoRepository.');
            }
            $this->collectionName = $this->tableName;
        }

        /** @var mixed $collection */
        $collection = $this->getCollection($this->collectionName);

        if (! $collection instanceof Collection) {
            throw new RepositoryException('Failed to retrieve MongoDB Collection.');
        }

        return $collection;
    }

    /**
     * Lazily create a MongoOps helper wired to the current MongoDB collection.
     *
     * @throws RepositoryException
     */
    protected function getMongoOps(): MongoOps
    {
        if ($this->mongoOps === null) {
            $this->mongoOps = new MongoOps($this->getCollectionObj());
        }

        return $this->mongoOps;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildIdFilter(int|string $id): array
    {
        if (is_string($id) && strlen($id) === 24 && ctype_xdigit($id)) {
            return ['_id' => new ObjectId($id)];
        }

        return ['_id' => $id];
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
        return $this->paginateBy([], $page, $perPage, $orderBy);
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

        $total = $this->count($filters);
        $offset = ($page - 1) * $perPage;

        $data = $this->findBy($filters, $orderBy, $perPage, $offset);

        $pagination = PaginationHelper::buildMeta($total, $page, $perPage);

        return new PaginationResultDTO($data, $pagination);
    }

}
