<?php

namespace App\Message;

use App\Entity\User;
use App\Enum\UserState;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class UserStateHandler
{
    public function __construct(
        private EntityManagerInterface $em,
        private MessageBusInterface $bus,
        private ?LoggerInterface $logger = null
    ) {
    }

    public function __invoke(UserStateMessage $message): void
    {
        $userId = $message->getUserId();
        if (empty($userId))
        {
            return;
        }
        $repository = $this->em->getRepository(User::class);
        // пока что просто подтвердим сразу
        /** @var User|null $user */
        $user = $repository->find($userId);
        if (empty($user))
        {
            $this->logger?->error(sprintf('Пользователь %s не найден.', $userId));
            throw new UnrecoverableMessageHandlingException(sprintf('Пользователь %s не найден.', $userId));
        }
        if ($user->getIstateid() == UserState::PENDING_APPROVAL)
        {
            $this->logger?->debug(sprintf('Пользователь %s PENDING_APPROVAL.', $userId));
            // требовать подтверждение мыла будет только если статус пользователя это требует
            $this->bus->dispatch(new UserEmailConfirmMessage($user->getIuserid()));
        } else if ($user->getIstateid() == UserState::BLOCKED)
        {
            // нужно отправить уведомление о блокировке
            $this->logger?->debug(sprintf('Пользователь %s BLOCKED.', $userId));
        }
    }
}
