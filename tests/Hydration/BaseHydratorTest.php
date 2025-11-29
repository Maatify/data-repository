<?php

declare(strict_types=1);

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     Maatify.dev Data Repository
 * @Project     maatify/data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-27 12:15:00
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

namespace Maatify\DataRepository\Tests\Hydration;

use Maatify\DataRepository\Hydration\BaseHydrator;
use Maatify\DataRepository\Hydration\HydrationContext;
use PHPUnit\Framework\TestCase;

// Helper class for strong typing in tests
class TestPerson
{
    public string $name = '';
    public int $age = 0;
    public bool $processed = false;
}

class BaseHydratorTest extends TestCase
{
    private BaseHydrator $hydrator;

    protected function setUp(): void
    {
        $this->hydrator = new class extends BaseHydrator {
            protected function createInstance(): object
            {
                return new TestPerson();
            }

            protected function onPrepare(array $data): array
            {
                if (isset($data['name']) && is_string($data['name'])) {
                    $data['name'] = trim($data['name']);
                }
                return $data;
            }

            protected function onCast(array $data): array
            {
                if (isset($data['age']) && is_numeric($data['age'])) {
                    $data['age'] = (int)$data['age'];
                }
                return $data;
            }

            protected function onComplete(object $instance): void
            {
                if ($instance instanceof TestPerson) {
                    $instance->processed = true;
                }
            }
        };
    }

    public function testHydrateSimpleFlow(): void
    {
        $data = ['name' => '  John Doe  ', 'age' => '30'];
        /** @var TestPerson $result */
        $result = $this->hydrator->hydrate($data);

        $this->assertInstanceOf(TestPerson::class, $result);
        $this->assertEquals('John Doe', $result->name);
        $this->assertEquals(30, $result->age);
        $this->assertTrue($result->processed);
    }

    public function testHydrateAll(): void
    {
        $dataset = [
            ['name' => 'A', 'age' => '20'],
            ['name' => 'B', 'age' => '25'],
        ];

        $results = $this->hydrator->hydrateAll($dataset);

        $this->assertCount(2, $results);

        /** @var TestPerson $first */
        $first = $results[0];
        /** @var TestPerson $second */
        $second = $results[1];

        $this->assertEquals('A', $first->name);
        $this->assertEquals('B', $second->name);
        $this->assertEquals(20, $first->age);
        $this->assertEquals(25, $second->age);
    }

    public function testCustomStagesContext(): void
    {
        $context = new HydrationContext();
        $context->setStages([
            HydrationContext::STAGE_PREPARE,
            HydrationContext::STAGE_MAP,
            // Skip CAST, VALIDATE, COMPLETE
        ]);

        $data = ['name' => ' Jane '];
        /** @var TestPerson $result */
        $result = $this->hydrator->hydrate($data, $context);

        $this->assertEquals('Jane', $result->name);
        $this->assertFalse($result->processed); // COMPLETE skipped
    }
}
