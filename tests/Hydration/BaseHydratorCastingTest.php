<?php

declare(strict_types=1);

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     Maatify.dev Data Repository
 * @Project     maatify/data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-27 13:20:00
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

namespace Maatify\DataRepository\Tests\Hydration;

use Maatify\DataRepository\Hydration\AutoCaster;
use Maatify\DataRepository\Hydration\BaseHydrator;
use PHPUnit\Framework\TestCase;

class CastingTestEntity
{
    public int $id;
    public bool $is_active;
    public string $name;
}

class BaseHydratorCastingTest extends TestCase
{
    private BaseHydrator $hydrator;

    protected function setUp(): void
    {
        $this->hydrator = new class extends BaseHydrator {
            public object $lastInstance;

            protected function createInstance(): object
            {
                $this->lastInstance = new CastingTestEntity();
                return $this->lastInstance;
            }

            protected function getCastingDefinitions(): array
            {
                return [
                    'id' => AutoCaster::TYPE_INT,
                    'is_active' => AutoCaster::TYPE_BOOL,
                ];
            }
        };
    }

    public function testAutoCastingIntegration(): void
    {
        $data = [
            'id' => '100',
            'is_active' => '1',
            'name' => 'Test',
        ];

        /** @var CastingTestEntity $result */
        $result = $this->hydrator->hydrate($data);

        $this->assertSame(100, $result->id);
        $this->assertTrue($result->is_active);
        $this->assertSame('Test', $result->name);
    }
}
