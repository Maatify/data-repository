<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Liberary    maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-25 03:14
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-repository  view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Generic\Real;

use Maatify\DataAdapters\Adapters\MySQLDbalAdapter;
use Maatify\DataRepository\Generic\GenericMySQLRepository;
use Maatify\DataRepository\Tests\Helpers\RealAdapterTrait;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('integration')]
class GenericDBALRepositoryRealTest extends TestCase
{
    use RealAdapterTrait;

    public function testCrudOperationsViaDbal(): void
    {
        if (! class_exists(MySQLDbalAdapter::class) || ! class_exists(\Doctrine\DBAL\Connection::class)) {
            $this->markTestSkipped('DBAL Adapter or Doctrine not installed');
        }

        $config = $this->getRealConfig();
        $adapter = new MySQLDbalAdapter($config, 'main');

        try {
            $adapter->connect();
        } catch (\Exception $e) {
            $this->markTestSkipped('Could not connect to Real MySQL via DBAL: ' . $e->getMessage());
        }

        // DBAL Connection -> Native PDO extraction check
        // The GenericMySQLRepository::getPdo() method should handle this automatically.
        $repo = new class ($adapter) extends GenericMySQLRepository {
            protected string $tableName = 'generic_dbal_test';
        };

        // Prepare Table using raw PDO to ensure clean state
        /** @var \Doctrine\DBAL\Connection $dbal */
        $dbal = $adapter->getDriver();
        $dbal->executeStatement('CREATE TABLE IF NOT EXISTS generic_dbal_test (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(255))');
        $dbal->executeStatement('TRUNCATE TABLE generic_dbal_test');

        // 1. Insert (Checks lastInsertId via DBAL wrapping)
        $id = $repo->insert(['name' => 'DBAL Test']);
        $this->assertIsNumeric($id);

        // 2. Find (Checks fetch behavior)
        $row = $repo->find($id);
        $this->assertNotNull($row);
        $this->assertArrayHasKey('name', $row);
        $this->assertEquals('DBAL Test', $row['name']);

        // 3. Update
        $repo->update($id, ['name' => 'DBAL Updated']);
        $row = $repo->find($id);
        $this->assertNotNull($row);
        $this->assertArrayHasKey('name', $row);
        $this->assertEquals('DBAL Updated', $row['name']);

        // 4. Delete
        $repo->delete($id);
        $this->assertNull($repo->find($id));

        // Cleanup
        $dbal->executeStatement('DROP TABLE generic_dbal_test');
    }
}
