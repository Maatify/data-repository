<?php

declare(strict_types=1);

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     Maatify.dev Data Repository
 * @Project     maatify/data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-27 12:00:00
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

namespace Maatify\DataRepository\Tests\Hydration;

use Maatify\DataRepository\Hydration\HydrationContext;
use Maatify\DataRepository\Hydration\HydratorInterface;
use PHPUnit\Framework\TestCase;

class HydratorInterfaceTest extends TestCase
{
    public function testHydrationContextStages(): void
    {
        $context = new HydrationContext();
        $stages = $context->getStages();

        $this->assertContains(HydrationContext::STAGE_PREPARE, $stages);
        $this->assertContains(HydrationContext::STAGE_CAST, $stages);
        $this->assertContains(HydrationContext::STAGE_MAP, $stages);
        $this->assertContains(HydrationContext::STAGE_VALIDATE, $stages);
        $this->assertContains(HydrationContext::STAGE_COMPLETE, $stages);
    }

    public function testHydrationContextMeta(): void
    {
        $context = new HydrationContext();
        $context->addMeta('user_id', 123);

        $this->assertEquals(123, $context->getMeta('user_id'));
        $this->assertNull($context->getMeta('non_existent'));
    }

    public function testHydratorInterfaceMock(): void
    {
        $hydrator = $this->createMock(HydratorInterface::class);
        $context = new HydrationContext();
        $data = ['id' => 1, 'name' => 'Test'];
        $object = (object)$data;

        $hydrator->expects($this->once())
            ->method('hydrate')
            ->with($data, $context)
            ->willReturn($object);

        $result = $hydrator->hydrate($data, $context);
        $this->assertEquals($object, $result);
    }
}
