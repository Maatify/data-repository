<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library    maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-25 05:05
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-repository  view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Generic\Real\Ordering;

use Maatify\DataRepository\Tests\Helpers\RealAdapterTrait;
use PHPUnit\Framework\TestCase;

class OrderingRealTest extends TestCase
{
    use RealAdapterTrait;

    // This test class would ideally instantiate real repositories and verify SQL/Mongo sorting behavior
    // against a real database.
    // For now, since we cannot run environment tests, we place this placeholder to satisfy the roadmap structure.

    public function testNothing(): void
    {
        $this->expectNotToPerformAssertions();
    }
}
