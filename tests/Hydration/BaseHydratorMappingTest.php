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

use Maatify\DataRepository\Hydration\BaseHydrator;
use Maatify\DataRepository\Hydration\HydrationContext;
use Maatify\DataRepository\Hydration\MappingProfile;
use Maatify\DataRepository\Hydration\TransformerInterface;
use PHPUnit\Framework\TestCase;

class MappedPerson
{
    public string $fullName = '';
    public int $yearsOld = 0;
    public string $status = '';
    public string $role = 'guest';
}

/**
 * @extends BaseHydrator<MappedPerson>
 */
class MappedPersonHydrator extends BaseHydrator
{
    protected function createInstance(): object
    {
        return new MappedPerson();
    }
}

class BaseHydratorMappingTest extends TestCase
{
    private MappedPersonHydrator $hydrator;

    protected function setUp(): void
    {
        $this->hydrator = new MappedPersonHydrator();
    }

    public function testMappingProfileApplication(): void
    {
        $data = [
            'name' => 'John Doe',
            'age' => 30,
            'state' => 'active',
            // 'role' is missing
        ];

        $profile = new MappingProfile();
        $profile->forSource('name')->mapTo('fullName');
        $profile->forSource('age')->mapTo('yearsOld');

        $upperCaseTransformer = new class implements TransformerInterface {
            public function transform(mixed $value): mixed
            {
                return strtoupper((string)$value);
            }
        };

        $profile->forSource('state')->mapTo('status')->transformWith($upperCaseTransformer);

        // Default value for role
        $profile->forSource('role')->withDefault('admin');

        $context = new HydrationContext();
        $context->setProfile($profile);

        /** @var MappedPerson $result */
        $result = $this->hydrator->hydrate($data, $context);

        $this->assertEquals('John Doe', $result->fullName);
        $this->assertEquals(30, $result->yearsOld);
        $this->assertEquals('ACTIVE', $result->status);
        $this->assertEquals('admin', $result->role);
    }

    public function testMappingWithoutSourceKeyInInput(): void
    {
        // Testing that if source key is missing, but default exists, it sets default
        $data = [];

        $profile = new MappingProfile();
        $profile->forSource('name')->mapTo('fullName')->withDefault('Unknown');

        $context = new HydrationContext();
        $context->setProfile($profile);

        /** @var MappedPerson $result */
        $result = $this->hydrator->hydrate($data, $context);

        $this->assertEquals('Unknown', $result->fullName);
    }
}
