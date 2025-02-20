<?php

namespace App\Model;

use App\Entity\Document;

class DocumentListResponse
{
    public function __construct(private array $items, private PaginationResponse $pagination)
    {
    }

    /**
     * @return Document[]
     */
    public function getDocument(): array
    {
        return $this->items;
    }

    public function getPagination(): PaginationResponse
    {
        return $this->pagination;
    }
}