<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\User;

class UserFixture extends Fixture
{
    public const USER_REFERENCE = 'current_user';

    public function __construct(private UserPasswordHasherInterface $hasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setVclogin('test');

        $password = $this->hasher->hashPassword($user, 'test');
        $user->setVcpassword($password);

        $manager->persist($user);
        $manager->flush();

        $this->addReference(self::USER_REFERENCE, $user);
    }
}
