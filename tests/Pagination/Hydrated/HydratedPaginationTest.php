<?php

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Pagination\Hydrated;

use Maatify\Common\Pagination\DTO\PaginationDTO;
use Maatify\Common\Pagination\DTO\PaginationResultDTO;
use Maatify\DataRepository\Generic\Support\RepositoryHydrationTrait;
use Maatify\DataRepository\Hydration\HydratorInterface;
use Maatify\DataRepository\Pagination\HydratedPaginationCollection;
use PHPUnit\Framework\TestCase;

class TestHydratedRepository
{
    use RepositoryHydrationTrait;

    public ?HydratorInterface $hydrator = null;

    // Simulate generic repository paginateBy method
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

        $meta = new PaginationDTO($page, $perPage, 2, 1); // Mock meta
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
        $hydrator->method('hydrateAll')
            ->willReturn([
                (object)['id' => 1, 'name' => 'Item 1', 'hydrated' => true],
                (object)['id' => 2, 'name' => 'Item 2', 'hydrated' => true],
            ]);

        $repo->hydrator = $hydrator;

        $result = $repo->paginateObjects(1, 10);

        $this->assertInstanceOf(HydratedPaginationCollection::class, $result);
        $this->assertCount(2, $result->data);
        $this->assertTrue($result->data[0]->hydrated);
        $this->assertInstanceOf(PaginationDTO::class, $result->pagination);
    }

    public function testPaginateObjectsByPassesFilters(): void
    {
        $repo = new TestHydratedRepository();

        // No hydrator set, should fallback to stdClass casting
        $result = $repo->paginateObjectsBy(['name' => 'test'], 1, 1);

        $this->assertInstanceOf(HydratedPaginationCollection::class, $result);
        $this->assertCount(1, $result->data); // mocked to slice 1
        $this->assertEquals(1, $result->data[0]->id);
    }
}
