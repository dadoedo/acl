<?php


namespace AppBundle\Services;


use AppBundle\Entity\Action;
use AppBundle\Entity\Resource;
use AppBundle\Entity\User;

interface AccessControlInterface
{
    public function isAllowed(Action $action, Resource $resource, User $user);
}