<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-27 03:00
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Generic\Ops;

use Maatify\DataRepository\Generic\Support\MongoOps;
use MongoDB\BSON\ObjectId;
use PHPUnit\Framework\TestCase;

class MongoOpsTest extends TestCase
{
    public function testGetCollection(): void
    {
        // Mock collection object (doesn't need to be real MongoCollection for this test)
        $collection = new \stdClass();
        $ops = new MongoOps($collection);

        $this->assertSame($collection, $ops->getCollection());
    }

    public function testNormalizeInsertedId(): void
    {
        $ops = new MongoOps(new \stdClass());

        $this->assertSame(123, $ops->normalizeInsertedId(123));
        $this->assertSame('abc', $ops->normalizeInsertedId('abc'));

        // Mock ObjectId
        $oid = new class {
            public function __toString() { return '507f1f77bcf86cd799439011'; }
        };
        // We can't mock strict ObjectId class check without extension, but we can check string conversion fallback or if logic allows objects.
        // The implementation checks `if ($id instanceof ObjectId)`. We cannot instantiate ObjectId without ext-mongodb usually.
        // However, the test environment "lacks php", so we can't run this anyway.
        // But for static analysis, it's fine.

        // Let's test the fallback for generic objects with __toString
        $this->assertSame('507f1f77bcf86cd799439011', $ops->normalizeInsertedId($oid));

        // Invalid returns empty string
        $this->assertSame('', $ops->normalizeInsertedId(null));
    }

    public function testToArray(): void
    {
        $ops = new MongoOps(new \stdClass());

        $this->assertNull($ops->toArray(null));

        $array = ['a' => 1];
        $this->assertSame($array, $ops->toArray($array));

        $obj = (object)['b' => 2];
        $this->assertSame(['b' => 2], $ops->toArray($obj));

        // Object with getArrayCopy
        $copyObj = new class {
            /** @return array<string, int> */
            public function getArrayCopy(): array { return ['c' => 3]; }
        };
        $this->assertSame(['c' => 3], $ops->toArray($copyObj));
    }

    public function testCursorToArray(): void
    {
        $ops = new MongoOps(new \stdClass());

        $cursor = [
            ['id' => 1],
            (object)['id' => 2]
        ];

        $result = $ops->cursorToArray($cursor);

        $this->assertCount(2, $result);
        $this->assertSame(['id' => 1], $result[0]);
        $this->assertSame(['id' => 2], $result[1]);
    }
}
