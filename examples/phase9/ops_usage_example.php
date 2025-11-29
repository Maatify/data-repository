<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Liberary    maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-29 16:09
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use Maatify\DataRepository\Generic\GenericMySQLRepository;
use Maatify\DataRepository\Generic\Support\MysqlOps;

// This example demonstrates how the internal Ops classes are exposed
// (via protected methods) to help implement custom repository logic if needed.

class ExtendedUserRepository extends GenericMySQLRepository
{
    protected string $tableName = 'users';

    // Expose Ops for demonstration (normally protected)
    public function getOps(): MysqlOps
    {
        return $this->getMysqlOps();
    }

    // Demonstrate usage of Ops in a custom method
    public function directInsert(array $data): int|string
    {
        // Use raw PDO driver via Ops
        $pdo = $this->getOps()->getDriver();

        if ($pdo instanceof PDO) {
            $stmt = $pdo->prepare('INSERT INTO users (name) VALUES (:name)');
            $stmt->execute(['name' => $data['name']]);

            // Use Ops to normalize ID
            return $this->getOps()->lastInsertId();
        }

        return 0;
    }
}

// Check for adapter (like in the phase6 example)
if (! isset($mysqlAdapter)) {
    echo "This example requires \$mysqlAdapter to be set. Skipping execution.\n";
    echo "This script demonstrates how Generic Repositories leverage MysqlOps internally.\n";
} else {
    try {
        /** @var ExtendedUserRepository $repo */
        $repo = new ExtendedUserRepository($mysqlAdapter);

        $id = $repo->directInsert(['name' => 'Ops User']);
        echo 'Inserted User ID via Ops: ' . $id . "\n";
    } catch (Exception $e) {
        echo 'Error: ' . $e->getMessage() . "\n";
    }
}
