<?php

namespace App\Doctrine\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class DocumentState extends Type
{
    const STATUS_DRAFT = 'draft';
    const STATUS_PUBLISHED = 'published';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return "ENUM('" . self::STATUS_DRAFT ."', '".  self::STATUS_PUBLISHED ."')";
    }

    public function getName(): string
    {
        return 'document_state';
    }
}