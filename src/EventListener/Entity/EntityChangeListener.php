<?php

namespace App\EventListener\Entity;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[AsDoctrineListener(event: Events::postFlush)]
class EntityChangeListener
{
    /**
     * @var array<int, object>
     */
    private array $events = [];

    public function __construct(
        private MessageBusInterface $bus,
        private ?TokenStorageInterface $storage = null
    ) {
    }

    public function postPersist(object $entity): void
    {
        if ($entity instanceof EntityEventProviderInterface)
        {
            $entity->getPostPersistEvent($this);
        }
    }

    // Передаем $args в сущность
    public function postUpdate(object $entity, PostUpdateEventArgs $args): void
    {
        if ($entity instanceof EntityEventProviderInterface)
        {
            $em = $args->getObjectManager();
            $uow = $em->getUnitOfWork();
            $changeSet = $uow->getEntityChangeSet($entity);
            $entity->getPostUpdateEvent($this, $changeSet);
        }
    }

    public function preRemove(object $entity): void
    {
        if ($entity instanceof EntityEventProviderInterface)
        {
            $entity->getPostRemoveEvent($this);
        }
    }

    public function getUser(): ?UserInterface
    {
        $token = $this->storage?->getToken();
        if ($token instanceof TokenInterface)
        {
            return $token->getUser();
        }
        return null;
    }

    public function addEvent(object $event): void
    {
        $this->events[] = $event;
    }

    public function postFlush(PostFlushEventArgs $args): void
    {
        if (empty($this->events))
        {
            return;
        }

        $eventsToSend = $this->events;
        $this->events = [];

        foreach ($eventsToSend as $event)
        {
            $this->bus->dispatch($event);
        }
    }
}
