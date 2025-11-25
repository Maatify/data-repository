<?php

declare(strict_types=1);

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-25 00:00:00
 * @see         https://www.maatify.dev
 * @link        https://github.com/Maatify/data-repository
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

namespace Maatify\DataRepository\Tests\Smoke;

use Maatify\DataRepository\Resolver\RepositoryResolver;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('integration')]
class RealSmokeTest extends TestCase
{
    public function testResolverArchitectureValid(): void
    {
        // In Phase 1, we validate architecture without necessarily connecting to live DBs yet,
        // ensuring strict typing and exception handling works.
        $resolver = new RepositoryResolver();
        $this->assertFalse($resolver->hasAdapter('non_existent'));
    }
}
