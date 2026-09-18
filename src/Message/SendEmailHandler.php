<?php

namespace App\Message;

use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SendEmailHandler
{
    /**
     * @param array<string, mixed> $options
     * @param MailerInterface $mailer
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        #[Autowire('%mailer%')]
        protected array $options,
        private MailerInterface        $mailer,
        private ?LoggerInterface       $logger = null
    ) {
    }

    public function __invoke(SendEmailMessage $command): void
    {
        $context = $command->context;
        $context['message_signature'] = $this->options['message_signature'] ?? '';

        try {
            $email = (new TemplatedEmail())
                ->from($this->options['support_mail'])
                ->to($command->email)
                ->subject($command->subject)
                ->htmlTemplate($command->template)
                ->context($context);

            $this->mailer->send($email);
        } catch (\Throwable $exception) {
            $this->logger?->error(sprintf('Notification %d failed: %s', $command->email, $exception->getMessage()));
            // Пробрасываем ошибку, чтобы сработал встроенный механизм retry очереди
            throw $exception;
        }
    }
}
