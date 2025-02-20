<?php

namespace App\Model;

class PaginationResponse
{
    public function __construct(private int $page = 1, private int $perPage = 10, private int $total = 0)
    {
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getPerPage(): int
    {
        return $this->perPage;
    }

    public function getTotal(): int
    {
        return $this->total;
    }
}