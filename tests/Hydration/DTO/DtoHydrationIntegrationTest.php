<?php

declare(strict_types=1);

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     Maatify.dev Data Repository
 * @Project     maatify/data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-27 14:40:00
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

namespace Maatify\DataRepository\Tests\Hydration\DTO;

use DateTimeImmutable;
use Maatify\DataRepository\Hydration\BaseHydrator;
use Maatify\DataRepository\Hydration\HydrationContext;
use Maatify\DataRepository\Hydration\MappingProfile;
use Maatify\DataRepository\Hydration\Transformers\DateTimeTransformer;
use PHPUnit\Framework\TestCase;

class UserDto
{
    public int $id;
    public string $fullName;
    public ?DateTimeImmutable $createdAt = null;
    public string $status;
}

class UserHydrator extends BaseHydrator
{
    protected function createInstance(): object
    {
        return new UserDto();
    }
}

class DtoHydrationIntegrationTest extends TestCase
{
    public function testHydrationWithProfile(): void
    {
        $data = [
            'user_id' => 123,
            'f_name' => 'John Doe',
            'created_ts' => '2023-01-01 10:00:00',
        ];

        $profile = new MappingProfile();
        $profile->addMap('user_id', 'id')
            ->addMap('f_name', 'fullName')
            ->addMap('created_ts', 'createdAt')
            ->addDefault('status', 'active')
            ->addTransformer('created_ts', new DateTimeTransformer());

        $context = new HydrationContext();
        $context->setProfile($profile);

        $hydrator = new UserHydrator();
        /** @var UserDto $dto */
        $dto = $hydrator->hydrate($data, $context);

        $this->assertInstanceOf(UserDto::class, $dto);
        $this->assertEquals(123, $dto->id);
        $this->assertEquals('John Doe', $dto->fullName);
        $this->assertEquals('active', $dto->status);
        $this->assertInstanceOf(DateTimeImmutable::class, $dto->createdAt);
        $this->assertEquals('2023-01-01 10:00:00', $dto->createdAt->format('Y-m-d H:i:s'));
    }
}
