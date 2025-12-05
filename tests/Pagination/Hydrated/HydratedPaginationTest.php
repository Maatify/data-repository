<?php

declare(strict_types=1);

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     Maatify.dev Data Repository
 * @Project     maatify/data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-27 19:30:00
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

namespace Maatify\DataRepository\Tests\Pagination\Hydrated;

use Maatify\Common\Pagination\DTO\PaginationDTO;
use Maatify\Common\Pagination\DTO\PaginationResultDTO;
use Maatify\DataRepository\Generic\Support\RepositoryHydrationTrait;
use Maatify\DataRepository\Hydration\HydratorInterface;
use Maatify\DataRepository\Pagination\HydratedPaginationCollection;
use PHPUnit\Framework\TestCase;

/**
 * @template T of object
 */
class TestHydratedRepository
{
    /** @use RepositoryHydrationTrait<object> */
    use RepositoryHydrationTrait;

    /** @var HydratorInterface<object>|null */
    public ?HydratorInterface $hydrator = null;

    /** @var PaginationResultDTO|null */
    public ?PaginationResultDTO $paginateByResult = null;

    /**
     * @param HydratorInterface<object> $hydrator
     */
    public function setHydrator(HydratorInterface $hydrator): void
    {
        $this->hydrator = $hydrator;
    }

    /**
     * @param array<string, mixed> $filters
     * @param int $page
     * @param int $perPage
     * @param array<string, string>|null $orderBy
     */
    public function paginateBy(array $filters, int $page = 1, int $perPage = 10, ?array $orderBy = null): PaginationResultDTO
    {
        return $this->paginateByResult ?? new PaginationResultDTO([], new PaginationDTO(1, 10, 0, 0, false, false));
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(int|string $id): ?array
    {
        return null;
    }

    /**
     * @param array<string, mixed> $filters
     * @return array<int, array<string, mixed>>
     */
    public function findBy(array $filters): array
    {
        return [];
    }
}

class HydratedPaginationTest extends TestCase
{
    /** @var TestHydratedRepository<object> */
    private TestHydratedRepository $repo;

    protected function setUp(): void
    {
        $this->repo = new TestHydratedRepository();
    }

    public function testPaginateObjectsReturnsHydratedCollection(): void
    {
        // 1. Setup Mock Data
        $data = [['id' => 1], ['id' => 2]];
        $pagination = new PaginationDTO(1, 10, 2, 1, false, false);
        $this->repo->paginateByResult = new PaginationResultDTO($data, $pagination);

        // 2. Setup Hydrator
        $hydrator = $this->createMock(HydratorInterface::class);
        $obj1 = (object)['id' => 1, 'hydrated' => true];
        $obj2 = (object)['id' => 2, 'hydrated' => true];
        $hydrator->method('hydrateAll')->willReturn([$obj1, $obj2]);

        $this->repo->setHydrator($hydrator);

        // 3. Execute
        $result = $this->repo->paginateObjects(1, 10);

        // 4. Verify
        $this->assertInstanceOf(HydratedPaginationCollection::class, $result);
        $this->assertCount(2, $result->data);
        $this->assertTrue(property_exists($result->data[0], 'hydrated') ? $result->data[0]->hydrated : false);
        $this->assertEquals(2, $result->pagination->total);
    }

    public function testPaginateObjectsWithoutHydrator(): void
    {
        // 1. Setup Mock Data
        $data = [['id' => 1]];
        $pagination = new PaginationDTO(1, 10, 1, 1, false, false);
        $this->repo->paginateByResult = new PaginationResultDTO($data, $pagination);

        // 2. Execute without hydrator
        $result = $this->repo->paginateObjects(1, 10);

        // 3. Verify fallback to stdClass
        $this->assertInstanceOf(HydratedPaginationCollection::class, $result);
        $this->assertCount(1, $result->data);
        $this->assertInstanceOf(\stdClass::class, $result->data[0]);
        // @phpstan-ignore-next-line
        $this->assertEquals(1, $result->data[0]->id);
    }
}
