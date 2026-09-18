<?php

namespace App\Security;

use App\Entity\User;
use App\Enum\UserState;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        $exception = null;
        if ($user instanceof User)
        {
            if ($user->getIstateid() == UserState::BLOCKED)
            {
                $exception = new Exception\DisabledException('Ваш аккаунт заблокирован');
            } else if ($user->getIstateid() == UserState::PENDING_APPROVAL)
            {
                $exception = new Exception\AccountExpiredException('Регистрация не подтверждена');
            }
        }
        if ($exception)
        {
            // может от туда чего возьмем...
            $exception->setUser($user);
            throw $exception;
        }
    }

    public function checkPostAuth(UserInterface $user, ?TokenInterface $token = null): void
    {
    }
}