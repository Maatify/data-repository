<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library    maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-25 10:05
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use Maatify\DataRepository\Exceptions\RepositoryException;
use Maatify\DataRepository\Generic\GenericMySQLRepository;
use Maatify\DataAdapters\Core\DatabaseResolver; // Assumed available if integrated

// Mocking a Repository for demonstration since we don't have a real DB connection here
class DemoRepository extends GenericMySQLRepository
{
    // ... setup would go here
}

echo "--- Standardized Exception Handling ---\n";

try {
    // Simulate a repository call that fails (e.g. connection error)
    // $repo->find(1);
    throw new \PDOException('SQLSTATE[HY000] [2002] Connection refused');
} catch (\PDOException $e) {
    // In the actual repository code (Phase 8), this catch block exists internally
    // and re-throws RepositoryException.

    // Demonstration of how to handle the result:
    $repoException = new RepositoryException("Find failed: " . $e->getMessage(), 0, $e);
    echo "Caught standardized exception: " . $repoException->getMessage() . "\n";
}


echo "\n--- NULL Value Handling ---\n";
$data = [
    'name' => 'Jane',
    'email' => null, // Explicitly setting NULL
];
// Repositories (MySQL) will bind this as SQL NULL.
print_r($data);


echo "\n--- Partial Update Logic ---\n";
$updateData = []; // Empty array
if (empty($updateData)) {
    echo "Update skipped: Data array is empty (Returns false immediately).\n";
} else {
    echo "Performing update...\n";
}
