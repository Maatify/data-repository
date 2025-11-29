<?php

declare(strict_types=1);

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     Maatify.dev Data Repository
 * @Project     maatify/data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-27 14:30:00
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

namespace Maatify\DataRepository\Tests\Hydration\DTO;

use Maatify\DataRepository\Hydration\MappingProfile;
use Maatify\DataRepository\Hydration\Transformers\JsonTransformer;
use PHPUnit\Framework\TestCase;

class MappingProfileTest extends TestCase
{
    public function testMappingConfiguration(): void
    {
        $profile = new MappingProfile();
        $profile->addMap('user_id', 'id')
            ->addMap('user_name', 'name');

        $this->assertEquals(['user_id' => 'id', 'user_name' => 'name'], $profile->getMapping());
    }

    public function testTransformerConfiguration(): void
    {
        $profile = new MappingProfile();
        $transformer = new JsonTransformer();
        $profile->addTransformer('meta', $transformer);

        $this->assertSame($transformer, $profile->getTransformer('meta'));
        $this->assertNull($profile->getTransformer('unknown'));
    }

    public function testDefaultValues(): void
    {
        $profile = new MappingProfile();
        $profile->addDefault('status', 'active');

        $this->assertEquals(['status' => 'active'], $profile->getDefaults());
    }
}
