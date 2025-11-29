<?php

declare(strict_types=1);

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     Maatify.dev Data Repository
 * @Project     maatify/data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-27 15:15:00
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

namespace Maatify\DataRepository\Tests\Generic\Hydration;

use Maatify\DataAdapters\Adapters\MySQLAdapter;
use Maatify\DataRepository\Generic\GenericMySQLRepository;
use Maatify\DataRepository\Hydration\BaseHydrator;
use Maatify\DataRepository\Tests\Helpers\RealAdapterTrait;
use PHPUnit\Framework\TestCase;

class TestDto
{
    public int $id;
    public string $name;
}

class TestHydrator extends BaseHydrator
{
    protected function createInstance(): object
    {
        return new TestDto();
    }
}

class TestRepository extends GenericMySQLRepository
{
    protected string $tableName = 'test_users';
}

class GenericRepositoryHydrationTest extends TestCase
{
    use RealAdapterTrait;

    private TestRepository $repo;
    private \PDO $pdo;

    protected function setUp(): void
    {
        $config = $this->getRealConfig();
        $adapter = new MySQLAdapter($config, 'main');
        $adapter->connect();

        $this->pdo = $adapter->getDriver();
        $this->pdo->exec("CREATE DATABASE IF NOT EXISTS maatify_dev");
        $this->pdo->exec("USE maatify_dev");
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS test_users (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(255))");
        $this->pdo->exec("TRUNCATE TABLE test_users");
        $this->pdo->exec("INSERT INTO test_users (id, name) VALUES (1, 'User One')");

        $this->repo = new TestRepository($adapter);
    }

    public function testFindObject(): void
    {
        $this->repo->setHydrator(new TestHydrator());
        $obj = $this->repo->findObject(1);

        $this->assertInstanceOf(TestDto::class, $obj);
        $this->assertEquals(1, $obj->id);
        $this->assertEquals('User One', $obj->name);
    }

    public function testFindObjectsBy(): void
    {
        $this->repo->setHydrator(new TestHydrator());
        $objects = $this->repo->findObjectsBy(['name' => 'User One']);

        $this->assertCount(1, $objects);
        $this->assertInstanceOf(TestDto::class, $objects[0]);
        $this->assertEquals('User One', $objects[0]->name);
    }

    public function testFindObjectWithoutHydratorReturnsStdClass(): void
    {
        $obj = $this->repo->findObject(1);

        $this->assertInstanceOf(\stdClass::class, $obj);
        $this->assertEquals(1, $obj->id);
    }

    protected function tearDown(): void
    {
        if (isset($this->pdo)) {
             $this->pdo->exec("DROP TABLE IF EXISTS test_users");
        }
    }
}
