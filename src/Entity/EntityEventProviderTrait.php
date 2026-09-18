<?php

namespace App\Entity;


use App\EventListener\Entity\EntityChangeListener;

trait EntityEventProviderTrait
{
    public function getPostPersistEvent(EntityChangeListener $listener): void { }

    public function getPostUpdateEvent(EntityChangeListener $listener, array $changeSet): void { }

    public function getPostRemoveEvent(EntityChangeListener $listener): void { }
}
