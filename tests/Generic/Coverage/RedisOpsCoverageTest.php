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
    public function testKeysDelegation(): void
    {
        $mock = $this->createMock(Client::class);
        $mock->expects($this->once())
            ->method('keys')
            ->with('*')
            ->willReturn(['key1', 'key2']);

        $ops = new RedisOps($mock);
        $result = $ops->keys('*');
        $this->assertEquals(['key1', 'key2'], $result);
    }

    public function testDelDelegation(): void
    {
        $mock = $this->createMock(Client::class);
        $mock->expects($this->once())
            ->method('del')
            ->with('key1')
            ->willReturn(1);

        $ops = new RedisOps($mock);
        $result = $ops->del('key1');
        $this->assertEquals(1, $result);
    }
}
