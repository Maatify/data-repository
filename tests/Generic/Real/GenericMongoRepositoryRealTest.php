<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Liberary    maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-25 03:16
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-repository  view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Generic\Real;

use Maatify\DataAdapters\Adapters\MongoAdapter;
use Maatify\DataRepository\Generic\GenericMongoRepository;
use Maatify\DataRepository\Tests\Helpers\RealAdapterTrait;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('integration')]
class GenericMongoRepositoryRealTest extends TestCase
{
    use RealAdapterTrait;

    public function testCrudOperationsViaMongo(): void
    {
        if (!class_exists(MongoAdapter::class) || !class_exists(\MongoDB\Client::class)) {
            $this->markTestSkipped('MongoDB extension or Adapter not installed');
        }

        $config = $this->getRealConfig();
        $adapter = new MongoAdapter($config, 'main');

        try {
            $adapter->connect();

            // Attempt to ping or interact to verify connection before running logic
            /** @var \MongoDB\Database $db */
            $db = $adapter->getDriver();
            $db->command(['ping' => 1]);

            // Cleanup
            $db->selectCollection('generic_test')->drop();

        } catch (\Exception $e) {
            // Catches MongoDB\Driver\Exception\ConnectionTimeoutException and others
            $this->markTestSkipped('Could not connect to MongoDB: ' . $e->getMessage());
        }

        $repo = new class ($adapter) extends GenericMongoRepository {
            protected string $collectionName = 'generic_test';
        };

        // 1. Insert
        $id = $repo->insert(['name' => 'Mongo Real', 'active' => true]);
        $this->assertNotEmpty($id);

        // 2. Find
        $doc = $repo->find($id);
        $this->assertNotNull($doc);
        $this->assertEquals('Mongo Real', $doc['name']);

        // 3. Delete
        $repo->delete($id);
        $this->assertNull($repo->find($id));
    }
}
