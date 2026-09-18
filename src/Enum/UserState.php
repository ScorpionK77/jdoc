<?php

namespace App\Enum;

enum UserState: int
{
    case PENDING_APPROVAL  = 1;   // Ожидает подтверждения
    case APPROVED          = 2;   // Подтвержден
    case BLOCKED           = 3;   // Заблокирован

    public function label(): string
    {
        return match($this) {
            self::PENDING_APPROVAL => 'Ожидает подтверждения',
            self::APPROVED         => 'Подтвержден',
            self::BLOCKED          => 'Заблокирован',
        };
    }
}
