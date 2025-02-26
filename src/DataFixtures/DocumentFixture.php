<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use App\Entity\Document;
use App\Entity\User;
use App\Doctrine\Types\DocumentState;

class DocumentFixture extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        // вносим 5 документов черновиков и 5 опубликованных
        foreach ([DocumentState::STATUS_DRAFT, DocumentState::STATUS_PUBLISHED] as $state)
        {
            for ($i = 0; $i < 5; $i++)
            {
                $document = new Document();
                $document->setUser($this->getReference(UserFixture::USER_REFERENCE, User::class));
                $document->setState($state);
                if ($state == DocumentState::STATUS_PUBLISHED)
                {
                    // для опубликованных добавим тело документа
                    $document->setPayload([
                        'pkey'      => 'ibuildingid',
                        'pageSize'  =>	10,
                        'perm' => [
                            'canAdd' => true,
                            'canEdit' => true,
                            'canDelete' => true,
                        ]
                    ]);
                }
                $manager->persist($document);
            }
        }
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixture::class,
        ];
    }
}
