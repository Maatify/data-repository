<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Liberary    maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-25 03:08
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-repository  view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Generic\Fake;

use Maatify\Common\Contracts\Adapter\AdapterInterface;
use Maatify\DataRepository\Generic\GenericMySQLRepository;
use PHPUnit\Framework\TestCase;

class GenericMySQLRepositoryFakeTest extends TestCase
{
    /**
     * @var GenericMySQLRepository&object
     * @phpstan-var GenericMySQLRepository&object
     */
    private GenericMySQLRepository $repo;

    protected function setUp(): void
    {
        // 1. Use Real In-Memory PDO (SQLite)
        // This validates the SQL generation logic of GenericMySQLRepository
        $pdo = new \PDO('sqlite::memory:');
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        // Create table compatible with Generic Logic
        $pdo->exec('CREATE TABLE users (id INTEGER PRIMARY KEY, name TEXT, role TEXT)');

        // 2. Mock Adapter to return this PDO
        $adapter = $this->createMock(AdapterInterface::class);
        $adapter->method('getDriver')->willReturn($pdo);

        // 3. Instantiate Repo
        $this->repo = new class ($adapter) extends GenericMySQLRepository {
            protected string $tableName = 'users';

            public function exposeMysqlOps(): \Maatify\DataRepository\Generic\Support\MysqlOps
            {
                return $this->getMysqlOps();
            }
        };

        // 4. Seed Data using SQL directly (bypassing Repo to verify Repo reads correctly)
        // OR use Repo insert to verify insert logic too.
        $this->repo->insert(['id' => 1, 'name' => 'John', 'role' => 'admin']);
        $this->repo->insert(['id' => 2, 'name' => 'Jane', 'role' => 'user']);
    }

    public function testFind(): void
    {
        $result = $this->repo->find(1);
        $this->assertNotNull($result);
        $this->assertArrayHasKey('name', $result);
        $this->assertEquals('John', $result['name']);

        $this->assertNull($this->repo->find(999));
    }

    public function testFindBy(): void
    {
        $admins = $this->repo->findBy(['role' => 'admin']);
        $this->assertCount(1, $admins);
        $this->assertEquals('John', $admins[0]['name']);

        // Test sorting (SQLite syntax compatible)
        $all = $this->repo->findBy([], ['id' => 'DESC']);
        $this->assertCount(2, $all);
        $this->assertEquals(2, $all[0]['id']);
    }

    public function testInsert(): void
    {
        $id = $this->repo->insert(['id' => 3, 'name' => 'New User', 'role' => 'guest']);
        $this->assertEquals(3, $id);

        $result = $this->repo->find(3);
        $this->assertNotNull($result);
        $this->assertEquals('New User', $result['name']);
    }

    public function testUpdate(): void
    {
        $updated = $this->repo->update(1, ['name' => 'Johnny']);
        $this->assertTrue($updated);

        $user = $this->repo->find(1);
        $this->assertNotNull($user);
        $this->assertArrayHasKey('name', $user);
        $this->assertEquals('Johnny', $user['name']);
    }

    public function testDelete(): void
    {
        $deleted = $this->repo->delete(1);
        $this->assertTrue($deleted);
        $this->assertNull($this->repo->find(1));
    }

    public function testFindOneByAndFindAll(): void
    {
        $first = $this->repo->findOneBy(['role' => 'user']);
        $this->assertNotNull($first);
        $this->assertSame('Jane', $first['name']);

        $all = $this->repo->findAll();
        $this->assertCount(2, $all);
    }

    public function testCountAndUpdateEmptyPayload(): void
    {
        $this->assertSame(2, $this->repo->count());
        $this->assertSame(1, $this->repo->count(['role' => 'admin']));

        $this->assertFalse($this->repo->update(5, []));
    }

    public function testFindBySkipsInvalidColumns(): void
    {
        $results = $this->repo->findBy(['role' => 'admin', 'invalid column' => 'x']);

        $this->assertCount(1, $results);
        $this->assertSame('John', $results[0]['name']);
    }

    public function testMysqlOpsIsMemoized(): void
    {
        /** @phpstan-ignore-next-line anonymous subclass exposes exposeMysqlOps() only in this test */
        $first = $this->repo->exposeMysqlOps();
        /** @phpstan-ignore-next-line anonymous subclass exposes exposeMysqlOps() only in this test */
        $second = $this->repo->exposeMysqlOps();

        $this->assertSame($first, $second);
        /** @phpstan-ignore-next-line phpstan cannot see that $first is MysqlOps from anonymous subclass */
        $this->assertInstanceOf(\PDO::class, $first->getDriver());
        /** @phpstan-ignore-next-line phpstan cannot see that both ops instances expose getDriver() */
        $this->assertSame($first->getDriver(), $second->getDriver());
    }
}
