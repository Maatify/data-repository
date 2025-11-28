<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library    maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-25 02:58
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Generic;

use Maatify\DataRepository\Base\BaseMySQLRepository;
use Maatify\DataRepository\Exceptions\RepositoryException;
use Maatify\DataRepository\Generic\Support\FilterUtils;
use Maatify\DataRepository\Generic\Support\LimitOffsetValidator;
use Maatify\DataRepository\Generic\Support\MysqlOps;
use Maatify\DataRepository\Generic\Support\OrderUtils;
use PDO;

abstract class GenericMySQLRepository extends BaseMySQLRepository
{
    protected string $primaryKey = 'id';

    private ?MysqlOps $mysqlOps = null;

    /**
     * @return array<string, mixed>|null
     * @throws RepositoryException
     */
    public function find(int|string $id): ?array
    {
        $stmt = $this->getPdo()->prepare("SELECT * FROM `{$this->tableName}` WHERE `{$this->primaryKey}` = :id LIMIT 1");
        $stmt->bindValue(':id', $id);
        $stmt->execute();

        /** @var array<string, mixed>|false $result */
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result === false ? null : $result;
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

        [$where, $params] = $this->buildWhereClause($filters);

        $sql = "SELECT * FROM `{$this->tableName}` {$where}";

        if (! empty($orderBy)) {
            $sql .= ' ' . OrderUtils::buildSqlOrderBy($orderBy);
        }

        if ($limit !== null) {
            $sql .= ' LIMIT ' . (int)$limit;
        }

        if ($offset !== null) {
            $sql .= ' OFFSET ' . (int)$offset;
        }

        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute($params);
        /** @var array<int, array<string, mixed>> $result */
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    /**
     * @param   array<string, mixed>  $filters
     *
     * @return array<string, mixed>|null
     * @throws RepositoryException
     */
    public function findOneBy(array $filters): ?array
    {
        $results = $this->findBy($filters, null, 1);

        return $results[0] ?? null;
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
        [$where, $params] = $this->buildWhereClause($filters);
        $sql = "SELECT COUNT(*) FROM `{$this->tableName}` {$where}";

        $stmt = $this->getPdo()->prepare($sql);
        $stmt->execute($params);

        return (int)$stmt->fetchColumn();
    }

    /**
     * @throws RepositoryException
     */
    public function insert(array $data): int|string
    {
        $columns = array_keys($data);
        $placeholders = array_map(fn ($col) => ":{$col}", $columns);

        $sql = sprintf(
            'INSERT INTO `%s` (`%s`) VALUES (%s)',
            $this->tableName,
            implode('`, `', $columns),
            implode(', ', $placeholders)
        );

        $pdo = $this->getPdo();
        $pdo->prepare($sql)->execute($data);

        $lastId = $pdo->lastInsertId();

        return $lastId === false ? 0 : $lastId;
    }

    /**
     * @throws RepositoryException
     */
    public function update(int|string $id, array $data): bool
    {
        if (empty($data)) {
            return false;
        }

        $sets = [];
        foreach (array_keys($data) as $col) {
            $sets[] = "`{$col}` = :{$col}";
        }

        $sql = sprintf(
            'UPDATE `%s` SET %s WHERE `%s` = :primaryKey',
            $this->tableName,
            implode(', ', $sets),
            $this->primaryKey
        );

        $data['primaryKey'] = $id;

        $stmt = $this->getPdo()->prepare($sql);

        return $stmt->execute($data);
    }

    /**
     * @throws RepositoryException
     */
    public function delete(int|string $id): bool
    {
        $sql = "DELETE FROM `{$this->tableName}` WHERE `{$this->primaryKey}` = :id";
        $stmt = $this->getPdo()->prepare($sql);
        $stmt->bindValue(':id', $id);

        return $stmt->execute();
    }

    /**
     * @throws RepositoryException
     */
    private function getPdo(): PDO
    {
        /** @var mixed $driver */
        $driver = $this->getDriver();

        if ($driver instanceof PDO) {
            return $driver;
        }

        // Safely check for DBAL
        if (is_object($driver) && method_exists($driver, 'getNativeConnection')) {
            /** @var mixed $native */
            $native = $driver->getNativeConnection();
            if ($native instanceof PDO) {
                return $native;
            }
        }

        throw new RepositoryException('GenericMySQLRepository requires a PDO driver or compatible wrapper.');
    }

    /**
     * Lazily create a MysqlOps helper wired to the current PDO driver.
     */
    protected function getMysqlOps(): MysqlOps
    {
        if ($this->mysqlOps === null) {
            $this->mysqlOps = new MysqlOps($this->getPdo());
        }

        return $this->mysqlOps;
    }

    /**
     * @param   array<string, mixed>  $filters
     *
     * @return array{0: string, 1: array<string, mixed>}
     */
    protected function buildWhereClause(array $filters): array
    {
        return FilterUtils::buildSqlWhere($filters);
    }
}
