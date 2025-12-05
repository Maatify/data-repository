<?php

declare(strict_types=1);

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     Maatify.dev Data Repository
 * @Project     maatify/data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-27 15:30:00
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

namespace Maatify\DataRepository\Tests\Hydration\DTO;

use Maatify\DataRepository\Hydration\BaseHydrator;
use Maatify\DataRepository\Hydration\HydrationContext;
use Maatify\DataRepository\Hydration\MappingProfile;
use Maatify\DataRepository\Hydration\Transformers\JsonTransformer;
use PHPUnit\Framework\TestCase;

class UserDto
{
    public int $id;
    public string $username;
    public string $role;
    /** @var array<string, mixed>|null */
    public ?array $preferences;
}

/**
 * @extends BaseHydrator<UserDto>
 */
class UserHydrator extends BaseHydrator
{
    protected function createInstance(): object
    {
        return new UserDto();
    }
}

class DtoHydrationIntegrationTest extends TestCase
{
    public function testHydrateWithMappingProfile(): void
    {
        // 1. Setup Profile
        $profile = new MappingProfile();
        $profile->forSource('user_id')->mapTo('id')
            ->forSource('login_name')->mapTo('username')
            ->forSource('role')->withDefault('guest')
            ->forSource('prefs')->mapTo('preferences')->transformWith(new JsonTransformer());

        // 2. Setup Context
        $context = new HydrationContext();
        $context->setProfile($profile);

        // 3. Setup Data
        $data = [
            'user_id' => 10,
            'login_name' => 'admin_user',
            'prefs' => '{"theme":"dark"}',
        ];

        // 4. Execute
        $hydrator = new UserHydrator();
        /** @var UserDto $result */
        $result = $hydrator->hydrate($data, $context);

        // 5. Verify
        $this->assertInstanceOf(UserDto::class, $result);
        $this->assertEquals(10, $result->id);
        $this->assertEquals('admin_user', $result->username);
        $this->assertEquals('guest', $result->role); // Default value
        $this->assertEquals(['theme' => 'dark'], $result->preferences); // Transformed JSON
    }
}
