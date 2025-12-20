<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library    maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-25 09:20
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Generic\Coverage;

use Maatify\DataRepository\Generic\Support\RedisOps;
use PHPUnit\Framework\TestCase;
use Predis\Client;

class RedisOpsCoverageTest extends TestCase
{
    protected function setUp(): void
    {
        if (!class_exists(Client::class)) {
            $this->markTestSkipped('Predis\Client not found');
        }
    }

    public function testKeysDelegation(): void
    {
        $this->markTestSkipped('Predis uses magic methods for keys(), cannot mock cleanly in strict mode.');
    }

    public function testDelDelegation(): void
    {
        $this->markTestSkipped('Predis uses magic methods for del(), cannot mock cleanly in strict mode.');
    }
}
