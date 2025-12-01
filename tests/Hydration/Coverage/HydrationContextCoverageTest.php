<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-27 12:00:00
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Hydration\Coverage;

use Maatify\DataRepository\Hydration\HydrationContext;
use Maatify\DataRepository\Hydration\MappingProfile;
use PHPUnit\Framework\TestCase;

class HydrationContextCoverageTest extends TestCase
{
    public function testSettersAndGetters(): void
    {
        $context = new HydrationContext();

        // Default stages
        $this->assertEquals([
            HydrationContext::STAGE_PREPARE,
            HydrationContext::STAGE_CAST,
            HydrationContext::STAGE_MAP,
            HydrationContext::STAGE_VALIDATE,
            HydrationContext::STAGE_COMPLETE,
        ], $context->getStages());

        // Set stages
        $newStages = ['a', 'b'];
        $this->assertSame($context, $context->setStages($newStages));
        $this->assertEquals($newStages, $context->getStages());

        // Meta
        $this->assertNull($context->getMeta('missing'));
        $this->assertSame($context, $context->addMeta('key', 'value'));
        $this->assertEquals('value', $context->getMeta('key'));

        // Profile
        $this->assertNull($context->getProfile());
        $profile = new MappingProfile();
        $this->assertSame($context, $context->setProfile($profile));
        $this->assertSame($profile, $context->getProfile());
    }
}
