<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-repository
 * @Project     maatify:data-repository
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-27
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/data-repository view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataRepository\Tests\Pagination;

use Maatify\DataRepository\Pagination\PaginationEntry;
use PHPUnit\Framework\TestCase;

class PaginationEntryTest extends TestCase
{
    public function testDefaults(): void
    {
        $entry = new PaginationEntry();

        // Defaults: page=1, perPage=10
        $this->assertEquals(1, $entry->getPage());
        $this->assertEquals(10, $entry->getPerPage());
        $this->assertEquals(0, $entry->getOffset());
    }

    public function testCustomValues(): void
    {
        $entry = new PaginationEntry(2, 20);

        $this->assertEquals(2, $entry->getPage());
        $this->assertEquals(20, $entry->getPerPage());
        // Offset = (page-1) * perPage = (2-1) * 20 = 20
        $this->assertEquals(20, $entry->getOffset());
    }

    public function testOffsetCalculation(): void
    {
        $entry = new PaginationEntry(3, 5);
        // (3-1) * 5 = 10
        $this->assertEquals(10, $entry->getOffset());
    }
}
