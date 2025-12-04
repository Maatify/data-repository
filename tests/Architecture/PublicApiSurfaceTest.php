<?php

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

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Architecture;

use Maatify\DataRepository\Generic\GenericMongoRepository;
use Maatify\DataRepository\Generic\GenericMySQLRepository;
use Maatify\DataRepository\Generic\GenericRedisRepository;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

class PublicApiSurfaceTest extends TestCase
{
    /**
     * The definitive list of methods that MUST be public in Generic Repositories.
     * All other methods should be protected or private.
     *
     * @var string[]
     */
    private const ALLOWED_PUBLIC_METHODS = [
        '__construct',
        // CRUD
        'find',
        'findBy',
        'findOneBy',
        'findAll',
        'count',
        'insert',
        'update',
        'delete',
        // Pagination
        'paginate',
        'paginateBy',
        // Hydration Trait
        'findObject',
        'findObjectsBy',
        'paginateObjects',
        'paginateObjectsBy',
        // Base Repository
        'setHydrator',
        'getHydrator',
        'setAdapter',
        'getTableName',
        // Mongo Specific
        'setCollectionName',
    ];

    /**
     * @return array<string, array{0: class-string}>
     */
    public function repositoryProvider(): array
    {
        return [
            'MySQL' => [GenericMySQLRepository::class],
            'Mongo' => [GenericMongoRepository::class],
            'Redis' => [GenericRedisRepository::class],
        ];
    }

    /**
     * @dataProvider repositoryProvider
     * @param class-string $className
     */
    public function testPublicApiSurface(string $className): void
    {
        $reflection = new ReflectionClass($className);
        $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method) {
            $name = $method->getName();
            $this->assertContains(
                $name,
                self::ALLOWED_PUBLIC_METHODS,
                "Method {$className}::{$name} is public but not in the allowed API list. " .
                "It should likely be protected or private."
            );
        }
    }

    /**
     * Verifies that methods intended to be internal are NOT public.
     */
    public function testInternalMethodsAreHidden(): void
    {
        $this->assertMethodIsNotPublic(GenericMySQLRepository::class, 'buildWhereClause');
        $this->assertMethodIsNotPublic(GenericMySQLRepository::class, 'getMysqlOps');
        $this->assertMethodIsNotPublic(GenericMySQLRepository::class, 'getPdo');

        $this->assertMethodIsNotPublic(GenericMongoRepository::class, 'getMongoOps');
        $this->assertMethodIsNotPublic(GenericMongoRepository::class, 'getCollectionObj');
        $this->assertMethodIsNotPublic(GenericMongoRepository::class, 'buildIdFilter');

        $this->assertMethodIsNotPublic(GenericRedisRepository::class, 'getRedisOps');
        $this->assertMethodIsNotPublic(GenericRedisRepository::class, 'getRedis');
        $this->assertMethodIsNotPublic(GenericRedisRepository::class, 'getKey');
        $this->assertMethodIsNotPublic(GenericRedisRepository::class, 'matches');
    }

    /**
     * @param class-string $className
     */
    private function assertMethodIsNotPublic(string $className, string $methodName): void
    {
        $reflection = new ReflectionClass($className);

        // Use try-catch or checks because method might not exist if it was removed/renamed
        if (!$reflection->hasMethod($methodName)) {
            // If it doesn't exist, it's definitely not public
            $this->addToAssertionCount(1);
            return;
        }

        $method = $reflection->getMethod($methodName);
        $this->assertFalse(
            $method->isPublic(),
            "Method {$className}::{$methodName} must NOT be public."
        );
    }
}
