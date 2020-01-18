<?php


namespace AppBundle\Services;


use AppBundle\Entity\Action;
use AppBundle\Entity\Resource;
use AppBundle\Entity\RoleUser;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class AccessControlList implements AccessControlInterface
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function isAllowed(Action $action, Resource $resource, User $user)
    {
        $result = $this->em->getRepository(RoleUser::class)->findAllAllowedEntry(
            $action->getId(),
            $user->getId(),
            $resource->getId()
        );

        return !($result == null or (boolean) $result["allowed"] == false);
    }
}