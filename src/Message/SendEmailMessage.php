<?php

namespace App\Message;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('async')]
class SendEmailMessage
{
    /**
     * @param string $email
     * @param string $subject
     * @param string $template
     * @param array<string, mixed> $context
     */
    public function __construct(
        public string $email,
        public string $subject,
        public string $template,
        public array $context
    ) {
    }
}
