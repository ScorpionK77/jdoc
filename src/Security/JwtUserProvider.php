<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

class JwtUserProvider implements UserProviderInterface
{
    public function __construct(private UserRepository $userRepository)
    {
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        return $user;
    }

    public function supportsClass(string $class): bool
    {
        return User::class === $class || is_subclass_of($class, User::class);
    }

    public function loadUserByIdentifier(string $identifier, array $payload = []): UserInterface
    {
        return $this->getUser(!empty($payload) ? 'iuserid' : 'vclogin', $identifier);
    }

    private function getUser(string $key, mixed $value): UserInterface
    {
        $user = $this->userRepository->findOneBy([$key => $value]);
        if (null === $user)
        {
            $e = new UserNotFoundException('User with id '.json_encode($value).' not found.');
            $e->setUserIdentifier(json_encode($value));
            throw $e;
        }
        return $user;
    }
}