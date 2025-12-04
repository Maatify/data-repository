<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-02
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/data-repository
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Examples\Phase26;

use Maatify\Common\Contracts\Adapter\AdapterInterface;
use Maatify\DataRepository\Generic\GenericMySQLRepository;
use ReflectionClass;
use ReflectionMethod;

/**
 * This example demonstrates the Official Public API Surface of Generic Repositories.
 * It uses Reflection to inspect a repository and print its allowed public methods.
 *
 * This confirms the work done in Phase 26: Public API Tightening.
 */

require __DIR__ . '/../../vendor/autoload.php';

// 1. Define a dummy concrete repository
class MyUserRepo extends GenericMySQLRepository
{
    protected string $tableName = 'users';

    public function __construct(AdapterInterface $adapter)
    {
        parent::__construct($adapter);
    }
}

// 2. Inspect the API Surface
$reflector = new ReflectionClass(MyUserRepo::class);
$methods = $reflector->getMethods(ReflectionMethod::IS_PUBLIC);

echo "=== Official Public API Surface (GenericMySQLRepository) ===\n";

$officialMethods = [
    'find', 'findBy', 'findOneBy', 'findAll', 'count',
    'insert', 'update', 'delete',
    'paginate', 'paginateBy',
    'findObject', 'findObjectsBy', 'paginateObjects', 'paginateObjectsBy',
    'setHydrator', 'getHydrator',
    'setAdapter', 'getTableName',
    '__construct'
];

foreach ($methods as $method) {
    // Filter out inherited methods from PHP core or traits if needed,
    // but here we just list what is exposed.
    $name = $method->getName();

    if (in_array($name, $officialMethods, true)) {
        echo "✅ {$name}()\n";
    } else {
        // This should not happen if the API audit was successful
        echo "⚠️  Unexpected Public Method: {$name}()\n";
    }
}

echo "\n=== Internal Helper Check (Should be hidden) ===\n";
$internalHelpers = ['buildWhereClause', 'getMysqlOps', 'getPdo'];

foreach ($internalHelpers as $helper) {
    if ($reflector->hasMethod($helper)) {
        $method = $reflector->getMethod($helper);
        if ($method->isPublic()) {
            echo "❌ ERROR: {$helper}() is PUBLIC!\n";
        } else {
            $type = $method->isProtected() ? 'protected' : 'private';
            echo "🔒 {$helper}() is hidden ({$type})\n";
        }
    } else {
        echo "❓ {$helper}() not found on this class.\n";
    }
}
