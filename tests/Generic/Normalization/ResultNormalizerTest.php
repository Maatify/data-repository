<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-29 02:07
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Generic\Normalization;

use Maatify\DataRepository\Generic\Support\ResultNormalizer;
use PHPUnit\Framework\TestCase;

class ResultNormalizerTest extends TestCase
{
    private bool $hasMongo;

    protected function setUp(): void
    {
        $this->hasMongo = class_exists(\MongoDB\BSON\ObjectId::class);
    }

    public function testNormalizeNull(): void
    {
        $this->assertNull(ResultNormalizer::normalize(null));
    }

    public function testNormalizeBasicRow(): void
    {
        $row = ['id' => 123, 'name' => 'Test'];
        $normalized = ResultNormalizer::normalize($row);

        $this->assertSame(123, $normalized['id']);
        $this->assertSame('Test', $normalized['name']);
    }

    public function testNormalizeMongoIdMappingAndRemoval(): void
    {
        // Simulate ObjectId if missing
        $mongoIdVal = '507f1f77bcf86cd799439011';
        $objectId = $this->createMockObjectId($mongoIdVal);

        // Case 1: Default behavior (remove _id if it matches mapped id)
        $row = ['_id' => $objectId, 'name' => 'MongoDoc'];
        $normalized = ResultNormalizer::normalize($row);

        $this->assertArrayHasKey('id', $normalized);
        $this->assertSame($mongoIdVal, $normalized['id']);
        $this->assertArrayNotHasKey('_id', $normalized); // Default: keepMongoId = false

        // Case 2: Configured to keep _id
        $normalizedKeep = ResultNormalizer::create()
            ->keepMongoId(true)
            ->normalizeRow($row);

        $this->assertArrayHasKey('id', $normalizedKeep);
        $this->assertArrayHasKey('_id', $normalizedKeep);
        $this->assertSame($mongoIdVal, $normalizedKeep['id']);
        $this->assertSame($mongoIdVal, $normalizedKeep['_id']);
    }

    public function testNormalizeWithConfigStatic(): void
    {
        $mongoIdVal = '507f1f77bcf86cd799439011';
        $objectId = $this->createMockObjectId($mongoIdVal);
        $row = ['_id' => $objectId];

        // normalizeWithConfig(row, keepMongoId=true)
        $normalized = ResultNormalizer::normalizeWithConfig($row, true);

        $this->assertArrayHasKey('id', $normalized);
        $this->assertArrayHasKey('_id', $normalized);
    }

    public function testRecursiveNormalization(): void
    {
        $mongoIdVal = '507f1f77bcf86cd799439011';
        $objectId = $this->createMockObjectId($mongoIdVal);

        $row = [
            'id' => 1,
            'meta' => [
                'created_by' => $objectId,
                'details' => [
                    'ref_id' => $objectId
                ]
            ],
            'tags' => [$objectId, 'tag2']
        ];

        // Recursive = false (default)
        $normDefault = ResultNormalizer::normalize($row);
        // Should NOT convert nested objectId
        $this->assertEquals($objectId, $normDefault['meta']['created_by']);

        // Recursive = true
        $normRecursive = ResultNormalizer::create()
            ->recursive(true)
            ->normalizeRow($row);

        $this->assertSame($mongoIdVal, $normRecursive['meta']['created_by']);
        $this->assertSame($mongoIdVal, $normRecursive['meta']['details']['ref_id']);
        $this->assertSame($mongoIdVal, $normRecursive['tags'][0]);
    }

    public function testStrictIdTypes(): void
    {
        // Valid ID formats
        $validRow = ['id' => '507f1f77bcf86cd799439011', 'code' => 123];
        $this->assertSame($validRow, ResultNormalizer::normalize($validRow));

        // Note: The current implementation of ResultNormalizer basically treats "strict" as "pass through unless invalid format AND explicitly an ID type".
        // But since it doesn't know which fields are IDs, it checks "isIdValue".
        // 'Bad' strings are just treated as strings.

        $badRow = ['name' => 'John Doe'];
        // "John Doe" !looksLikeIdString -> skipped by isIdValue -> passed as string.
        $this->assertSame($badRow, ResultNormalizer::normalize($badRow));
    }

    public function testStringableObjects(): void
    {
        $obj = new class {
            public function __toString()
            {
                return 'string-representation';
            }
        };

        $row = ['data' => $obj];
        $normalized = ResultNormalizer::normalize($row);

        $this->assertSame('string-representation', $normalized['data']);
    }

    public function testNormalizeAll(): void
    {
        $rows = [
            ['id' => 1],
            ['id' => 2],
        ];

        $normalized = ResultNormalizer::normalizeAll($rows);

        $this->assertCount(2, $normalized);
        $this->assertSame(1, $normalized[0]['id']);
    }

    public function testFluentApi(): void
    {
        $normalizer = ResultNormalizer::create()
            ->keepMongoId()
            ->recursive()
            ->strictIdTypes();

        $this->assertInstanceOf(ResultNormalizer::class, $normalizer);
    }

    private function createMockObjectId(string $hex): object
    {
        if ($this->hasMongo) {
            return new \MongoDB\BSON\ObjectId($hex);
        }

        // Return an anonymous class acting like ObjectId
        return new class($hex) {
            private string $hex;
            public function __construct(string $hex) { $this->hex = $hex; }
            public function __toString() { return $this->hex; }
        };
    }
}
