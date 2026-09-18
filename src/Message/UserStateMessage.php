<?php

namespace App\Message;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('sync')]
class UserStateMessage
{
    public function __construct(
        private int $userId
    ) {
    }

    public function getUserId(): int
    {
        return $this->userId;
    }
}
