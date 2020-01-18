<?php


namespace AppBundle\Repository;


use Doctrine\ORM\EntityRepository;

class RoleUserRepository extends EntityRepository
{
    public function findAllAllowedEntry($actionId, $userId, $resourceId)
    {
        return $this->createQueryBuilder('ru')
            ->andWhere('ru.user = :userId')
            ->andWhere('r.resource = :resourceId')
            ->andWhere('r.action = :actionId')
            ->join('ru.role', 'role')
            ->join('role.rules', 'rr')
            ->join('rr.rule','r')
            ->setParameters([
                'userId' => $userId,
                'resourceId' => $resourceId,
                'actionId' => $actionId,
            ])
            ->addSelect('rr.allowed as allowed')
            ->getQuery()
            ->getOneOrNullResult();
    }
}