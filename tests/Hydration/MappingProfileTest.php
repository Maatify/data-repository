<?php

declare(strict_types=1);

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     Maatify.dev Data Repository
 * @Project     maatify/data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-27
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

namespace Maatify\DataRepository\Tests\Hydration;

use Maatify\DataRepository\Hydration\MappingProfile;
use Maatify\DataRepository\Hydration\TransformerInterface;
use PHPUnit\Framework\TestCase;

class MappingProfileTest extends TestCase
{
    public function testFluentMapping(): void
    {
        $profile = new MappingProfile();
        $profile->forSource('user_name')->mapTo('name');
        $profile->forSource('user_age')->mapTo('age');

        $mapping = $profile->getMapping();
        $this->assertArrayHasKey('user_name', $mapping);
        $this->assertEquals('name', $mapping['user_name']);
        $this->assertArrayHasKey('user_age', $mapping);
        $this->assertEquals('age', $mapping['user_age']);
    }

    public function testFluentTransformation(): void
    {
        $transformer = new class implements TransformerInterface {
            public function transform(mixed $value): mixed
            {
                if (is_scalar($value) || $value instanceof \Stringable) {
                    return strtoupper((string)$value);
                }
                return $value;
            }
        };

        $profile = new MappingProfile();
        $profile->forSource('status')->transformWith($transformer);

        $transformers = [];
        // Reflection to inspect private property or assume getTransformer exists?
        // Method getTransformer exists.
        $this->assertSame($transformer, $profile->getTransformer('status'));
        $this->assertNull($profile->getTransformer('other'));
    }

    public function testFluentDefaults(): void
    {
        $profile = new MappingProfile();

        // Case 1: Default for mapped property
        $profile->forSource('missing_field')->mapTo('target_field')->withDefault('default_value');

        // Case 2: Default for unmapped property (source=dest)
        $profile->forSource('direct_field')->withDefault(100);

        $defaults = $profile->getDefaults();

        $this->assertArrayHasKey('target_field', $defaults);
        $this->assertEquals('default_value', $defaults['target_field']);

        $this->assertArrayHasKey('direct_field', $defaults);
        $this->assertEquals(100, $defaults['direct_field']);
    }

    public function testSwitchingSources(): void
    {
        $profile = new MappingProfile();
        $profile->forSource('a')->mapTo('A')
                ->forSource('b')->mapTo('B');

        $mapping = $profile->getMapping();
        $this->assertEquals('A', $mapping['a']);
        $this->assertEquals('B', $mapping['b']);
    }
}
