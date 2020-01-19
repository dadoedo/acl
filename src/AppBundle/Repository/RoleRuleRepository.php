<?php


namespace AppBundle\Repository;


use AppBundle\Entity\RoleRule;
use Doctrine\ORM\EntityRepository;

class RoleRuleRepository extends EntityRepository
{
    public function deleteAllWithRule($ruleId)
    {
        return $this->createQueryBuilder('rr')
            ->delete()
            ->andWhere('rr.rule = :ruleId')
            ->setParameter('ruleId', $ruleId)
            ->getQuery()
            ->execute();
    }
}