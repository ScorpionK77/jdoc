<?php

namespace App\Message;

use App\Doctrine\Types\DocumentState;
use App\Entity\Document;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class DocumentPublishedHandler
{
    public function __construct(
        private EntityManagerInterface $em,
        private MessageBusInterface $bus,
        private ?LoggerInterface $logger = null
    ) {
    }

    public function __invoke(DocumentPublishedMessage $message): void
    {
        $docId = $message->docId;
        if (empty($docId))
        {
            return;
        }
        $repository = $this->em->getRepository(Document::class);
        /** @var Document|null $user */
        $doc = $repository->find($docId);
        if (empty($doc))
        {
            $this->logger?->error(sprintf('Документ %s не найден.', $docId));
            throw new UnrecoverableMessageHandlingException(sprintf('Документ %s не найден.', $docId));
        }
        if ($doc->getState() == DocumentState::STATUS_PUBLISHED)
        {
            // получаем всех активных пользователей
            $userRepository = $this->em->getRepository(User::class);
            /** @var User $user */
            foreach ($userRepository->findApproved((int)$doc->getUser()->getUserIdentifier()) as $user)
            {
                $context = [
                    'username' => $user->getVclogin(),
                    'documentUrl' => '/api/v1/document/' . $docId,
                ];
                $this->bus->dispatch(new SendEmailMessage(
                    $user->getVcemail(), 'Новый документ JDoc', 'emails/document_published.html.twig', $context
                ));
            }
        }
    }
}
