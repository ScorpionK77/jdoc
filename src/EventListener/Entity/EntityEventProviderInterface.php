<?php

namespace App\EventListener\Entity;

use Doctrine\ORM\PersistentCollection;

interface EntityEventProviderInterface
{
    public function getPostPersistEvent(EntityChangeListener $listener): void;

    /**
     * @template T of object
     *
     * @param EntityChangeListener $listener
     * @param array<string, PersistentCollection<array-key, T>|list<mixed>> $changeSet
     * @return void
     */
    public function getPostUpdateEvent(EntityChangeListener $listener, array $changeSet): void;

    public function getPostRemoveEvent(EntityChangeListener $listener): void;
}
