<?php

namespace Maatify\Common\Pagination;

class PaginationDTO
{
    public int $pages;

    public function __construct(
        public int $page,
        public int $total,
        public int $limit
    ) {
        $this->pages = (int)ceil($total / $limit);
    }
}
