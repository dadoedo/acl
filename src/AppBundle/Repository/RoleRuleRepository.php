<?php


namespace AppBundle\Repository;


use Doctrine\ORM\EntityRepository;

class RoleRuleRepository extends EntityRepository
{
    public function deleteAllWithRule($role_id)
    {
        die($role_id);
        return $this->createQueryBuilder('rr')
            ->delete()
            ->andWhere('rr.role = :roleId')
            ->setParameter('roleId', $role_id)
            ->getQuery()
            ->execute();
    }
}