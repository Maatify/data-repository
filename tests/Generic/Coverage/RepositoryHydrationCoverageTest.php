<?php

declare(strict_types=1);

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     Maatify.dev Data Repository
 * @Project     maatify/data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-30 11:00:00
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

namespace Maatify\DataRepository\Tests\Generic\Coverage;

use Maatify\Common\Pagination\DTO\PaginationDTO;
use Maatify\Common\Pagination\DTO\PaginationResultDTO;
use Maatify\DataRepository\Generic\Support\RepositoryHydrationTrait;
use Maatify\DataRepository\Hydration\HydratorInterface;
use Maatify\DataRepository\Pagination\HydratedPaginationCollection;
use PHPUnit\Framework\TestCase;

class TestHydrationRepo
{
    use RepositoryHydrationTrait;

    public ?HydratorInterface $hydrator = null;

    // Mock data for find/findBy/paginateBy
    public ?array $findResult = null;
    public array $findByResult = [];
    public ?PaginationResultDTO $paginateByResult = null;

    public function setHydrator(HydratorInterface $hydrator): void
    {
        $this->hydrator = $hydrator;
    }

    public function find(int|string $id): ?array
    {
        return $this->findResult;
    }

    public function findBy(array $filters): array
    {
        return $this->findByResult;
    }

    public function paginateBy(array $filters, int $page = 1, int $perPage = 10, ?array $orderBy = null): PaginationResultDTO
    {
        return $this->paginateByResult ?? new PaginationResultDTO(
            [],
            new PaginationDTO($page, $perPage, 0, false, false)
        );
    }
}

class TestObject
{
    public int $id;
    public string $name;
}

class RepositoryHydrationCoverageTest extends TestCase
{
    private TestHydrationRepo $repo;

    protected function setUp(): void
    {
        $this->repo = new TestHydrationRepo();
    }

    public function testFindObjectReturnsNullWhenNotFound(): void
    {
        $this->repo->findResult = null;
        $this->assertNull($this->repo->findObject(1));
    }

    public function testFindObjectWithoutHydrator(): void
    {
        $this->repo->findResult = ['id' => 1, 'name' => 'test'];
        $result = $this->repo->findObject(1);
        $this->assertInstanceOf(\stdClass::class, $result);
        $this->assertEquals(1, $result->id);
    }

    public function testFindObjectWithHydrator(): void
    {
        $this->repo->findResult = ['id' => 1, 'name' => 'test'];
        $hydrator = $this->createMock(HydratorInterface::class);
        $obj = new TestObject();
        $obj->id = 1;
        $obj->name = 'test';
        $hydrator->method('hydrate')->willReturn($obj);

        $this->repo->setHydrator($hydrator);

        $result = $this->repo->findObject(1);
        $this->assertInstanceOf(TestObject::class, $result);
        $this->assertEquals(1, $result->id);
    }

    public function testFindObjectsByWithoutHydrator(): void
    {
        $this->repo->findByResult = [['id' => 1], ['id' => 2]];
        $results = $this->repo->findObjectsBy([]);

        $this->assertCount(2, $results);
        $this->assertInstanceOf(\stdClass::class, $results[0]);
        $this->assertEquals(1, $results[0]->id);
    }

    public function testFindObjectsByWithHydrator(): void
    {
        $this->repo->findByResult = [['id' => 1]];
        $hydrator = $this->createMock(HydratorInterface::class);
        $obj = new TestObject();
        $hydrator->method('hydrateAll')->willReturn([$obj]);

        $this->repo->setHydrator($hydrator);

        $results = $this->repo->findObjectsBy([]);
        $this->assertCount(1, $results);
        $this->assertInstanceOf(TestObject::class, $results[0]);
    }

    public function testPaginateObjectsDelegatesToPaginateObjectsBy(): void
    {
        // Setup pagination result
        $pagination = new PaginationDTO(1, 10, 1, false, false);
        $this->repo->paginateByResult = new PaginationResultDTO([['id' => 1]], $pagination);

        $result = $this->repo->paginateObjects(1, 10);

        $this->assertInstanceOf(HydratedPaginationCollection::class, $result);
        $this->assertCount(1, $result->data);
    }

    public function testPaginateObjectsByWithoutHydrator(): void
    {
        $pagination = new PaginationDTO(1, 10, 1, false, false);
        $this->repo->paginateByResult = new PaginationResultDTO([['id' => 1, 'val' => 'a']], $pagination);

        $result = $this->repo->paginateObjectsBy(['val' => 'a']);

        $this->assertInstanceOf(HydratedPaginationCollection::class, $result);
        $this->assertCount(1, $result->data);
        $this->assertInstanceOf(\stdClass::class, $result->data[0]);
        $this->assertEquals(1, $result->data[0]->id);
        $this->assertSame($pagination, $result->pagination);
    }

    public function testPaginateObjectsByWithHydrator(): void
    {
        $pagination = new PaginationDTO(1, 10, 1, false, false);
        $this->repo->paginateByResult = new PaginationResultDTO([['id' => 1]], $pagination);

        $hydrator = $this->createMock(HydratorInterface::class);
        $obj = new TestObject();
        $hydrator->method('hydrateAll')->willReturn([$obj]);

        $this->repo->setHydrator($hydrator);

        $result = $this->repo->paginateObjectsBy([]);

        $this->assertInstanceOf(HydratedPaginationCollection::class, $result);
        $this->assertCount(1, $result->data);
        $this->assertInstanceOf(TestObject::class, $result->data[0]);
    }
}
