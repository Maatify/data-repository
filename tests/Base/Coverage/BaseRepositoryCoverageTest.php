<?php

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Base;

use Maatify\Common\Contracts\Adapter\AdapterInterface;
use Maatify\DataRepository\Base\BaseRepository;
use Maatify\DataRepository\Generic\GenericRedisRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

#[CoversClass(BaseRepository::class)]
class BaseRepositoryCoverageTest extends TestCase
{
    private BaseRepository $repository;
    private AdapterInterface $adapter;

    protected function setUp(): void
    {
        $this->adapter = $this->createMock(AdapterInterface::class);
        $this->repository = new class($this->adapter) extends GenericRedisRepository {
            protected function validateAdapter(): void
            {
                // Do nothing
            }
        };
    }

    public function testGetAndSetTableName(): void
    {
        $tableName = 'test_table';

        $setTableNameMethod = new ReflectionMethod(BaseRepository::class, 'setTableName');
        $setTableNameMethod->setAccessible(true);
        $setTableNameMethod->invoke($this->repository, $tableName);

        $this->assertEquals($tableName, $this->repository->getTableName());
    }

    public function testGetDriver(): void
    {
        $driver = new \stdClass();
        $this->adapter->method('getDriver')->willReturn($driver);

        $getDriverMethod = new ReflectionMethod(BaseRepository::class, 'getDriver');
        $getDriverMethod->setAccessible(true);

        $this->assertSame($driver, $getDriverMethod->invoke($this->repository));
    }
}
