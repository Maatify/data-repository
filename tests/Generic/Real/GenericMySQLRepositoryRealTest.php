<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Liberary    maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-25 03:10
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-repository  view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Generic\Real;

use Maatify\DataAdapters\Adapters\MySQLAdapter;
use Maatify\DataRepository\Generic\GenericMySQLRepository;
use Maatify\DataRepository\Tests\Helpers\RealAdapterTrait;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('integration')]
class GenericMySQLRepositoryRealTest extends TestCase
{
    use RealAdapterTrait;

    public function testCrudOperationsOnRealDb(): void
    {
        if (!class_exists(MySQLAdapter::class)) {
            $this->markTestSkipped('Real Adapter not installed');
        }

        $config = $this->getRealConfig();
        $adapter = new MySQLAdapter($config, 'main');

        try {
            $adapter->connect();
            /** @var \PDO $pdo */
            $pdo = $adapter->getDriver();

            // Ensure Database Exists and is Selected
            $pdo->exec('CREATE DATABASE IF NOT EXISTS maatify_dev');
            $pdo->exec('USE maatify_dev');

            // Setup Table
            $pdo->exec('CREATE TABLE IF NOT EXISTS generic_test (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(255))');
            $pdo->exec('TRUNCATE TABLE generic_test');
        } catch (\Exception $e) {
            $this->markTestSkipped('Could not connect/setup Real MySQL: ' . $e->getMessage());
        }

        $repo = new class ($adapter) extends GenericMySQLRepository {
            protected string $tableName = 'generic_test';
        };

        // 1. Insert
        $id = $repo->insert(['name' => 'Real Test']);
        $this->assertIsNumeric($id);

        // 2. Find
        $row = $repo->find($id);
        $this->assertNotNull($row);
        $this->assertArrayHasKey('name', $row);
        $this->assertEquals('Real Test', $row['name']);

        // 3. Update
        $repo->update($id, ['name' => 'Updated']);
        $row = $repo->find($id);
        $this->assertNotNull($row);
        $this->assertArrayHasKey('name', $row);
        $this->assertEquals('Updated', $row['name']);

        // 4. Delete
        $repo->delete($id);
        $this->assertNull($repo->find($id));

        // Teardown
        $pdo->exec('DROP TABLE generic_test');
    }
}
