<?php


namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\RoleRuleRepository")
 * @ORM\Table(name="role_rule")
 */
class RoleRule
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Role", inversedBy="rules")
     * @ORM\JoinColumn(nullable=false)
     */
    private $role;

    /**
     * @ORM\ManyToOne(targetEntity="Rule", inversedBy="roles")
     * @ORM\JoinColumn(nullable=false)
     */
    private $rule;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    private $allowed = true;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Role
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @param Role $role
     */
    public function setRole($role)
    {
        $this->role = $role;
    }

    /**
     * @return Rule
     */
    public function getRule()
    {
        return $this->rule;
    }

    /**
     * @param Rule $rule
     */
    public function setRule($rule)
    {
        $this->rule = $rule;
    }

    /**
     * @return boolean
     */
    public function getAllowed()
    {
        return $this->allowed;
    }

    /**
     * @param boolean $allowed
     */
    public function setAllowed($allowed)
    {
        $this->allowed = $allowed;
    }

}