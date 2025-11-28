<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Liberary    maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-27 19:54
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Generic\Runtime;

use Maatify\DataRepository\Generic\GenericMongoRepository;
use MongoDB\BSON\ObjectId;
use PHPUnit\Framework\TestCase;
use Maatify\Common\Contracts\Adapter\AdapterInterface;
use ReflectionException;

/**
 * Tests for buildIdFilter() private method in GenericMongoRepository
 */
class GenericMongoRepositoryBuildIdFilterTest extends TestCase
{
    /**
     * @var GenericMongoRepository&object
     */
    private GenericMongoRepository $repo;

    protected function setUp(): void
    {
        /** @var \MongoDB\Database&\PHPUnit\Framework\MockObject\MockObject $db */
        $db = $this->createMock(\MongoDB\Database::class);

        /** @var \MongoDB\Client&\PHPUnit\Framework\MockObject\MockObject $client */
        $client = $this->createMock(\MongoDB\Client::class);

        $adapter = new class ($db, $client) implements AdapterInterface {
            public function __construct(
                private \MongoDB\Database $db,
                private \MongoDB\Client $client
            ) {
            }

            /** @return \MongoDB\Database */
            public function getDriver(): mixed
            {
                return $this->db;
            }

            /** @return \MongoDB\Client */
            public function getConnection(): mixed
            {
                return $this->client;
            }

            public function getType(): string
            {
                return 'mongo';
            }
            public function connect(): void
            {
            }
            public function isConnected(): bool
            {
                return true;
            }
            public function disconnect(): void
            {
            }
            public function debugConfig(): object
            {
                return (object) [];
            }
            public function healthCheck(): bool
            {
                return true;
            }
        };

        $this->repo = new class ($adapter) extends GenericMongoRepository {
            protected string $collectionName = 'dummy';
        };
    }

    /**
     * @return array<string, mixed>
     * @throws ReflectionException
     */
    private function invokeBuildIdFilter(int|string $id): array
    {
        $ref = new \ReflectionClass($this->repo);
        $method = $ref->getMethod('buildIdFilter');
        $method->setAccessible(true);

        /** @var array<string, mixed> $result */
        $result = $method->invoke($this->repo, $id);

        return $result;
    }

    public function testBuildIdFilterWithInt(): void
    {
        $result = $this->invokeBuildIdFilter(123);
        $this->assertSame(['_id' => 123], $result);
    }

    public function testBuildIdFilterWithShortString(): void
    {
        $result = $this->invokeBuildIdFilter('abc');
        $this->assertSame(['_id' => 'abc'], $result);
    }

    public function testBuildIdFilterWith24CharNonHex(): void
    {
        $id = 'zzzzzzzzzzzzzzzzzzzzzzzz'; // 24 chars but not hex
        $result = $this->invokeBuildIdFilter($id);
        $this->assertSame(['_id' => $id], $result);
    }

    public function testBuildIdFilterWithValidHex24Char(): void
    {
        $id = '507f1f77bcf86cd799439011';
        $result = $this->invokeBuildIdFilter($id);

        $this->assertArrayHasKey('_id', $result);
        $this->assertInstanceOf(ObjectId::class, $result['_id']);
        $this->assertSame($id, $result['_id']->__toString());
    }

    public function testBuildIdFilterWithUpperCaseHex(): void
    {
        $id = '507F1F77BCF86CD799439011';

        $result = $this->invokeBuildIdFilter($id);

        $this->assertInstanceOf(ObjectId::class, $result['_id']);
        $this->assertSame(strtolower($id), $result['_id']->__toString());
    }

    public function testBuildIdFilterRejectsMixedCharacters(): void
    {
        $id = '507f1f77bcf86cd79943901@'; // invalid char @

        $result = $this->invokeBuildIdFilter($id);

        $this->assertSame(['_id' => $id], $result);
    }

    public function testBuildIdFilterHexWithLeadingZeros(): void
    {
        $id = '000000000000000000000abc'; // valid hex

        $result = $this->invokeBuildIdFilter($id);

        $this->assertInstanceOf(ObjectId::class, $result['_id']);
        $this->assertSame($id, $result['_id']->__toString());
    }
}
