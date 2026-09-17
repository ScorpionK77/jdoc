<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use App\Entity\Document;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends Voter<string, Document>
 */
class DocumentVoter extends Voter
{
    const VIEW  = 'view';
    const ADD  = 'add';
    const EDIT = 'edit';
    const DELETE = 'delete';


    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, [self::VIEW, self::ADD, self::EDIT, self::DELETE]))
        {
            return false;
        }

        // only vote on `Document` objects
        if (!$subject instanceof Document)
        {
            return false;
        }
        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        if ($attribute == self::VIEW)
        {
            return $subject->getState() == 'published' || $this->isOwner($subject, $token);
        } elseif ($attribute == self::ADD)
        {
            return $token->getUser() instanceof UserInterface;
        } else
        {
            return $this->isOwner($subject, $token);
        }
    }

    private function isOwner(mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface)
        {
            return false;
        }

        return $subject->getUser()?->getUserIdentifier() === $user->getUserIdentifier();
    }
}