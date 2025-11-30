<?php

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Pagination\Hydrated;

use Maatify\Common\Pagination\DTO\PaginationDTO;
use Maatify\Common\Pagination\DTO\PaginationResultDTO;
use Maatify\DataRepository\Generic\Support\RepositoryHydrationTrait;
use Maatify\DataRepository\Hydration\HydratorInterface;
use Maatify\DataRepository\Pagination\HydratedPaginationCollection;
use PHPUnit\Framework\TestCase;

class TestEntity
{
    public int $id;
    public string $name;
    public bool $hydrated = false;
}

class TestHydratedRepository
{
    use RepositoryHydrationTrait;

    public ?HydratorInterface $hydrator = null;

    /**
     * @param int|string $id
     * @return array<string, mixed>|null
     */
    public function find(int|string $id): ?array
    {
        return ['id' => (int)$id, 'name' => 'Item ' . $id];
    }

    /**
     * @param array<string, mixed> $filters
     * @param array<string, string>|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @return array<int, array<string, mixed>>
     */
    public function findBy(array $filters, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        return [
            ['id' => 1, 'name' => 'Item 1'],
        ];
    }

    /**
     * @param array<string, mixed> $filters
     * @param int $page
     * @param int $perPage
     * @param array<string, string>|null $orderBy
     * @return PaginationResultDTO
     */
    public function paginateBy(array $filters, int $page = 1, int $perPage = 10, ?array $orderBy = null): PaginationResultDTO
    {
        // Return dummy data matching the request
        $data = [
            ['id' => 1, 'name' => 'Item 1'],
            ['id' => 2, 'name' => 'Item 2'],
        ];

        // If limit is small, slice it (dummy logic)
        if ($perPage === 1) {
            $data = array_slice($data, 0, 1);
        }

        // PaginationDTO constructor:
        // __construct(int $total, int $page, int $perPage, int $lastPage, bool $hasNext = false, bool $hasPrev = false)
        // Adjusting args to match expected types:
        $meta = new PaginationDTO(2, $page, $perPage, 1, false, false);
        return new PaginationResultDTO($data, $meta);
    }
}

class HydratedPaginationTest extends TestCase
{
    public function testPaginateObjectsReturnsHydratedCollection(): void
    {
        $repo = new TestHydratedRepository();

        // Mock Hydrator
        $hydrator = $this->createMock(HydratorInterface::class);

        $entity1 = new TestEntity();
        $entity1->id = 1;
        $entity1->name = 'Item 1';
        $entity1->hydrated = true;

        $entity2 = new TestEntity();
        $entity2->id = 2;
        $entity2->name = 'Item 2';
        $entity2->hydrated = true;

        $hydrator->method('hydrateAll')
            ->willReturn([$entity1, $entity2]);

        $repo->hydrator = $hydrator;

        $result = $repo->paginateObjects(1, 10);

        $this->assertInstanceOf(HydratedPaginationCollection::class, $result);
        $this->assertCount(2, $result->data);

        $first = $result->data[0];
        $this->assertInstanceOf(TestEntity::class, $first);
        $this->assertTrue($first->hydrated);
        $this->assertInstanceOf(PaginationDTO::class, $result->pagination);
    }

    public function testPaginateObjectsByPassesFilters(): void
    {
        $repo = new TestHydratedRepository();

        // No hydrator set, should fallback to stdClass casting
        $result = $repo->paginateObjectsBy(['name' => 'test'], 1, 1);

        $this->assertInstanceOf(HydratedPaginationCollection::class, $result);
        $this->assertCount(1, $result->data); // mocked to slice 1

        $first = $result->data[0];
        /** @var object{id: int} $first */
        $this->assertEquals(1, $first->id);
    }
}
