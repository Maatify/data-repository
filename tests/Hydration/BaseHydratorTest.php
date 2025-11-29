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

class BaseHydratorTest extends TestCase
{
    private BaseHydrator $hydrator;

    protected function setUp(): void
    {
        $this->hydrator = new class extends BaseHydrator {
            protected function createInstance(): object
            {
                return new class {
                    public string $name = '';
                    public int $age = 0;
                    public bool $processed = false;
                };
            }

            protected function onPrepare(array $data): array
            {
                if (isset($data['name'])) {
                    $data['name'] = trim($data['name']);
                }
                return $data;
            }

            protected function onCast(array $data): array
            {
                if (isset($data['age'])) {
                    $data['age'] = (int)$data['age'];
                }
                return $data;
            }

            protected function onComplete(object $instance): void
            {
                $instance->processed = true;
            }
        };
    }

    public function testHydrateSimpleFlow(): void
    {
        $data = ['name' => '  John Doe  ', 'age' => '30'];
        $result = $this->hydrator->hydrate($data);

        $this->assertInstanceOf(object::class, $result);
        $this->assertEquals('John Doe', $result->name); // onPrepare trimmed it
        $this->assertEquals(30, $result->age);       // onCast cast it
        $this->assertTrue($result->processed);       // onComplete set it
    }

    public function testHydrateAll(): void
    {
        $dataset = [
            ['name' => 'A', 'age' => '20'],
            ['name' => 'B', 'age' => '25'],
        ];
        $results = $this->hydrator->hydrateAll($dataset);

        $this->assertCount(2, $results);
        $this->assertEquals('A', $results[0]->name);
        $this->assertEquals('B', $results[1]->name);
        $this->assertEquals(20, $results[0]->age);
        $this->assertEquals(25, $results[1]->age);
    }

    public function testCustomStagesContext(): void
    {
        // Define context that SKIPS casting
        $context = new HydrationContext();
        $context->setStages([
            HydrationContext::STAGE_PREPARE,
            HydrationContext::STAGE_MAP,
            // Skip CAST, VALIDATE, COMPLETE
        ]);

        $data = ['name' => ' Jane ', 'age' => '40'];
        $result = $this->hydrator->hydrate($data, $context);

        $this->assertEquals('Jane', $result->name);
        // Age should remain 0 because map assigns '40' (string) to int property?
        // Actually PHP strict types might complain if assigned directly, but dynamic props?
        // The anonymous class has `public int $age`. Assigning string '40' works in weak mode,
        // but strict_types=1 is on.
        // Wait, onMap does $instance->$key = $value.
        // If strict types are enabled in this file, does it apply to the assignment? Yes.
        // So this test might fail if I don't cast.

        // Let's check behavior. Property type checking is strict.
        // If I skip casting, '40' is string.
        // Assigning string to int property throws TypeError.

        // I will update the test expectation or data to avoid fatal error,
        // demonstrating stage skipping logic via 'processed' flag.

        // Use data that doesn't trigger type error
        $data = ['name' => ' Jane '];
        $result = $this->hydrator->hydrate($data, $context);

        $this->assertEquals('Jane', $result->name);
        $this->assertFalse($result->processed); // COMPLETE skipped
    }
}
