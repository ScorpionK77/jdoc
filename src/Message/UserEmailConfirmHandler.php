<?php

namespace App\Message;

use App\Entity\User;
use App\Enum\UserState;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

#[AsMessageHandler]
class UserEmailConfirmHandler
{
    public function __construct(
        private EntityManagerInterface $em,
        private CacheInterface $cache,
        private MessageBusInterface $bus,
        private ?LoggerInterface $logger = null
    ) {
    }

    public function __invoke(UserEmailConfirmMessage $message): void
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
            $token = bin2hex(random_bytes(32));

            // 3. Сохраняем структуру [token => userId] в Redis на 24 часа
            $cacheKey = 'confirm_reg_' . $token;
            $this->cache->get($cacheKey, function (ItemInterface $item) use ($user) {
                $item->expiresAfter(86400); // 24 часа в секундах
                return $user->getIuserid();
            });

            $this->logger?->info('Код успешно сохранен в Redis для ключа: ' . $cacheKey);

            $context = [
                'username' => $user->getVclogin(),
                'confirmationUrl' => '/api/v1/confirm/' . $token,
                'code' => $token
            ];
            $this->bus->dispatch(new SendEmailMessage(
                $user->getVcemail(), 'Подтверждение регистрации JDoc', 'emails/confirmation.html.twig', $context
            ));
        }
    }
}
