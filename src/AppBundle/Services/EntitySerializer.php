<?php


namespace AppBundle\Services;


use AppBundle\Entity\Action;
use AppBundle\Entity\Resource;
use AppBundle\Entity\Role;
use AppBundle\Entity\Rule;
use AppBundle\Entity\User;

class EntitySerializer
{
    public function serializeAction(Action $action) {
        if (!$action) {return null;}
        return [
            'id' => $action->getId(),
            'name' => $action->getName(),
            'description' => $action->getDescription()
        ];
    }

    public function serializeRole(Role $role) {
        if (!$role) {return null;}
        return [
            'id' => $role->getId(),
            'name' => $role->getName(),
        ];
    }

    public function serializeRule(Rule $rule) {
        if (!$rule) {return null;}
        return [
          'id' => $rule->getId(),
          'name' => $rule->getName(),
          'description' => $rule->getDescription()
        ];
    }

    public function serializeResource(Resource $resource) {
        if (!$resource) {return null;}
        return [
            'id' => $resource->getId(),
            'name' => $resource->getName(),
        ];
    }

    public function serializeUser(User $user) {
        if (!$user) {return null;}
        return [
            'id' => $user->getId(),
            'name' => $user->getName(),
            'surname' => $user->getSurname(),
        ];
    }
}