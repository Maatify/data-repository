<?php

require __DIR__ . '/../../vendor/autoload.php';

use Maatify\DataRepository\Generic\GenericMySQLRepository;
use Maatify\DataRepository\Resolver\RepositoryResolver;

// Mocking a concrete class for the example
class UserRepository extends GenericMySQLRepository
{
    protected string $tableName = 'users';
}

echo "MySQL Advanced Filtering Example:\n";

// 1. IN Operator
// findBy(['role' => ['IN' => ['admin', 'editor']]]);
echo "1. Filter by IN: ['role' => ['IN' => ['admin', 'editor']]]\n";

// 2. LIKE Operator
// findBy(['name' => ['LIKE' => '%John%']]);
echo "2. Filter by LIKE: ['name' => ['LIKE' => '%John%']]\n";

// 3. Range Operators
// findBy(['age' => ['>' => 18, '<=' => 65]]);
echo "3. Filter by Range: ['age' => ['>' => 18, '<=' => 65]]\n";

// 4. BETWEEN Operator
// findBy(['created_at' => ['BETWEEN' => ['2023-01-01', '2023-12-31']]]);
echo "4. Filter by BETWEEN: ['created_at' => ['BETWEEN' => ['2023-01-01', '2023-12-31']]]\n";
