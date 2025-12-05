<?php

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Static;

use Maatify\Common\Contracts\Adapter\AdapterInterface;
use Maatify\DataRepository\Base\BaseRepository;
use Maatify\DataRepository\Generic\GenericMySQLRepository;
use Maatify\DataRepository\Hydration\HydratorInterface;
use Maatify\DataRepository\Pagination\HydratedPaginationCollection;
use PHPUnit\Framework\TestCase;

/**
 * Validates that classes are ready for Generics.
 * This test mainly serves as a syntax check and example usage for static analysis.
 */
class PhpStanGenericsTest extends TestCase
{
    public function testGenericSyntax(): void
    {
        // 1. Define a fake entity
        $entity = new class {
            public int $id = 1;
            public string $name = 'Test';
        };

        // 2. Mock a Generic Repository with explicit template type (simulated via anonymous class)
        // Since anonymous classes don't support generics easily in runtime reflection assertions without attributes,
        // we just verify instantiation logic.

        $repo = new class ($this->createMock(AdapterInterface::class)) extends GenericMySQLRepository {
            /** @var string */
            protected string $tableName = 'users';
        };

        $this->assertInstanceOf(GenericMySQLRepository::class, $repo);

        // 3. Verify HydratorInterface usage
        $hydrator = new class implements HydratorInterface {
            public function hydrate(array $data, ?\Maatify\DataRepository\Hydration\HydrationContext $context = null): object
            {
                return (object)$data;
            }

            public function hydrateAll(array $dataset, ?\Maatify\DataRepository\Hydration\HydrationContext $context = null): array
            {
                return array_map(fn($d) => (object)$d, $dataset);
            }
        };

        $this->assertInstanceOf(HydratorInterface::class, $hydrator);
    }
}
