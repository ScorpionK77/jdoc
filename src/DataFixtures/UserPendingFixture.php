<?php

namespace App\DataFixtures;

use App\Enum\UserState;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\User;

class UserPendingFixture extends Fixture
{
    public const USER_REFERENCE = 'test_user';

    public function __construct(private UserPasswordHasherInterface $hasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setVclogin('test_user')
            ->setVcemail('test_user@mail.ru')
            ->setIstateid(UserState::PENDING_APPROVAL);

        $password = $this->hasher->hashPassword($user, 'SecurePassword123');
        $user->setVcpassword($password);

        $manager->persist($user);
        $manager->flush();

        $this->addReference(self::USER_REFERENCE, $user);
    }
}
