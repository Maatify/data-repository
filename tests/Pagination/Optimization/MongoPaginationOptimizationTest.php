<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library    maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-27 18:40
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Pagination\Optimization;

use Maatify\DataRepository\Generic\GenericMongoRepository;
use PHPUnit\Framework\TestCase;
use MongoDB\Collection;

class MongoPaginationOptimizationTest extends TestCase
{
    public function testPaginateUsesSkipAndLimit(): void
    {
        $collection = $this->createMock(Collection::class);

        // Expect countDocuments
        $collection->method('countDocuments')->willReturn(100);

        // Expect find with correct options
        $collection->expects($this->once())
            ->method('find')
            ->with(
                $this->anything(), // filters
                $this->callback(function (mixed $options) {
                    if (! is_array($options)) {
                        return false;
                    }
                    // Verify Optimization: Options must contain limit and skip
                    return isset($options['limit']) && $options['limit'] === 10
                        && isset($options['skip']) && $options['skip'] === 20;
                })
            )
            ->willReturn(new \ArrayIterator([])); // Return empty iterator

        $mockAdapter = $this->createMock(\Maatify\Common\Contracts\Adapter\AdapterInterface::class);

        $repo = new class($mockAdapter, $collection) extends GenericMongoRepository {
            protected string $collectionName = 'test_collection';

            public function __construct(\Maatify\Common\Contracts\Adapter\AdapterInterface $adapter, private Collection $collection)
            {
                parent::__construct($adapter);
            }

            protected function getCollection(string $name): object
            {
                return $this->collection;
            }

            protected function getDriver(): object
            {
                return $this->collection; // Mock
            }
        };

        // Request Page 3, 10 items per page (Offset = 20)
        $repo->paginate(3, 10);
    }
}
