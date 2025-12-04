<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Liberary    maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-04 10:07
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Examples\Phase1;

require __DIR__ . '/../../vendor/autoload.php';

use Maatify\Common\Contracts\Adapter\AdapterInterface;
use Maatify\DataRepository\Exceptions\RepositoryException;
use Maatify\DataRepository\Resolver\RepositoryResolver;

// Mock adapter
$adapter = new class () implements AdapterInterface {
    public function connect(): void
    {
    }

    public function isConnected(): bool
    {
        return true;
    }

    public function getConnection(): object
    {
        return new \stdClass();
    }

    public function healthCheck(): bool
    {
        return true;
    }

    public function disconnect(): void
    {
    }

    public function getDriver(): object
    {
        return new \stdClass();
    }

    public function getType(): string
    {
        return 'mock';
    }
};

// 1. Initialize Resolver
$resolver = new RepositoryResolver();

// 2. Register Adapter
$resolver->registerAdapter('main_db', $adapter);

// 3. Resolve Adapter
try {
    $resolved = $resolver->getAdapter('main_db');
    echo "Adapter resolved successfully.\n";
} catch (RepositoryException $e) {
    echo 'Error: ' . $e->getMessage() . "\n";
}

// 4. Handle Missing Adapter
try {
    $resolver->getAdapter('missing_db');
} catch (RepositoryException $e) {
    echo 'Caught expected error: ' . $e->getMessage() . "\n";
}
