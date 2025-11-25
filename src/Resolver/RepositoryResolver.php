<?php

declare(strict_types=1);

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-24 16:49:00
 * @see         https://www.maatify.dev
 * @link        https://github.com/Maatify/data-repository
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

namespace Maatify\DataRepository\Resolver;

use Maatify\Common\Contracts\Adapter\AdapterInterface;
use Maatify\DataRepository\Exceptions\RepositoryException;
use Psr\Log\LoggerInterface;

class RepositoryResolver
{
    /**
     * @var array<string, AdapterInterface>
     */
    private array $adapters = [];

    private ?LoggerInterface $logger = null;

    public function __construct(?LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    public function registerAdapter(string $name, AdapterInterface $adapter): void
    {
        $this->adapters[$name] = $adapter;
        $this->logger?->info("Adapter registered: {$name}", ['source' => 'maatify/data-repository']);
    }

    /**
     * @throws RepositoryException
     */
    public function getAdapter(string $name): AdapterInterface
    {
        if (!isset($this->adapters[$name])) {
            throw RepositoryException::driverNotSupported($name);
        }
        return $this->adapters[$name];
    }

    public function hasAdapter(string $name): bool
    {
        return isset($this->adapters[$name]);
    }
}
