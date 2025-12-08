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

use Maatify\Common\Pagination\DTO\PaginationDTO;
use Maatify\DataRepository\Pagination\PaginationContext;
use Maatify\DataRepository\Pagination\PaginationEntry;
use PHPUnit\Framework\TestCase;

class PaginationContextTest extends TestCase
{
    public function testEntryGetterSetter(): void
    {
        $context = new PaginationContext();
        $this->assertNull($context->getEntry());

        $entry = new PaginationEntry(1, 10);
        $context->setEntry($entry);

        $this->assertSame($entry, $context->getEntry());
    }

    public function testMetaGetterSetter(): void
    {
        $context = new PaginationContext();
        $this->assertNull($context->getMeta());

        $meta = new PaginationDTO(1, 10, 100, 10, true, false);
        $context->setMeta($meta);

        $this->assertSame($meta, $context->getMeta());
    }
}
