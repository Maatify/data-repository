<?php

declare(strict_types=1);

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-25 01:05:00
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

namespace Maatify\DataRepository\Base;

use Maatify\Common\Contracts\Adapter\AdapterInterface;
use Maatify\Common\Contracts\Repository\RepositoryInterface;
use Maatify\DataRepository\Exceptions\RepositoryException;
use Maatify\DataRepository\Hydration\HydratorInterface;
use Maatify\DataRepository\Logging\RepositoryLogger;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

abstract class BaseRepository implements RepositoryInterface
{
    protected AdapterInterface $adapter;
    protected LoggerInterface $logger;
    protected string $tableName = '';
    protected ?HydratorInterface $hydrator = null;

    public function __construct(AdapterInterface $adapter, ?LoggerInterface $logger = null)
    {
        $this->setAdapter($adapter);
        // Wrap logger to ensure consistent context source or use NullLogger if none provided
        $this->logger = $logger ? new RepositoryLogger($logger) : new NullLogger();
    }

    /**
     * @throws RepositoryException
     */
    public function setAdapter(AdapterInterface $adapter): static
    {
        $this->adapter = $adapter;
        $this->validateAdapter();

        return $this;
    }

    /**
     * Override in child classes to enforce specific adapter types (e.g., MySQL vs. Mongo).
     *
     * @throws RepositoryException
     */
    protected function validateAdapter(): void
    {
        // Base implementation permits any valid AdapterInterface.
        // Child classes should check $this->adapter->getType().
    }

    public function getTableName(): string
    {
        return $this->tableName;
    }

    protected function setTableName(string $tableName): void
    {
        $this->tableName = $tableName;
    }

    /**
     * Expose the raw driver for internal repository logic ONLY.
     *
     * @return mixed
     */
    protected function getDriver(): mixed
    {
        return $this->adapter->getDriver();
    }

    public function setHydrator(HydratorInterface $hydrator): static
    {
        $this->hydrator = $hydrator;
        return $this;
    }

    public function getHydrator(): ?HydratorInterface
    {
        return $this->hydrator;
    }
}
