<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Liberary    maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-27 19:39
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Generic\Support;

use Maatify\DataRepository\Generic\Support\MongoOps;
use MongoDB\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MongoOpsTest extends TestCase
{
    /** @var Collection&MockObject */
    private Collection|MockObject $collectionMock;

    protected function setUp(): void
    {
        if (! class_exists(Collection::class)) {
            $this->markTestSkipped('MongoDB library not installed');
        }

        /** @var Collection&MockObject $mock */
        $mock = $this->createMock(Collection::class);
        $this->collectionMock = $mock;
    }

    public function testConstructorStoresCollectionInstance(): void
    {
        $ops = new MongoOps($this->collectionMock);

        $this->assertSame($this->collectionMock, $ops->getCollection());
    }

    public function testGetCollectionReturnsSameInstance(): void
    {
        $ops = new MongoOps($this->collectionMock);

        $first = $ops->getCollection();
        $second = $ops->getCollection();

        $this->assertSame($first, $second);
    }

    public function testAcceptsGenericObject(): void
    {
        $obj = new class () {
            public function select(): string
            {
                return 'ok';
            }
        };

        $ops = new MongoOps($obj);

        $this->assertSame($obj, $ops->getCollection());
        $this->assertEquals('ok', $ops->getCollection()->select());
    }

    public function testRejectsScalarInputs(): void
    {
        $this->expectException(\TypeError::class);

        /** @phpstan-ignore-next-line intentionally wrong */
        new MongoOps('not-object');
    }

    public function testRejectsArrayInputs(): void
    {
        $this->expectException(\TypeError::class);

        /** @phpstan-ignore-next-line intentionally wrong */
        new MongoOps(['invalid']);
    }
}
