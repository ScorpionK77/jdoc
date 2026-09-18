<?php

namespace App\Message;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('async')]
class DocumentPublishedMessage
{
    public function __construct(
        public readonly int $docId
    ) {
    }
}