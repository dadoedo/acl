<?php


namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="role")
 */
class Role
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="RoleRule", mappedBy="role")
     */
    private $rules;

    /**
     * @ORM\OneToMany(targetEntity="RoleUser", mappedBy="user")
     */
    private $users;

    public function __construct()
    {
        $this->rules = new ArrayCollection();
        $this->users = new ArrayCollection();
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return ArrayCollection|Rule[]
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * @param Rule $rule
     */
    public function addRule($rule)
    {
        if($this->rules->contains($rule)) {
            return;
        }

        $this->rules->add($rule);
        $rule->addRole($this);
    }

    /**
     * @param Rule $rule
     */
    public function removeRule($rule)
    {
        if(!$this->rules->contains($rule)) {
            return;
        }

        $this->rules->removeElement($rule);
        $rule->removeRole($this);
    }

    /**
     * @return ArrayCollection|User[]
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @param User $user
     */
    public function addUser($user)
    {
        if($this->users->contains($user)) {
            return;
        }

        $this->users->add($user);
        $user->addRole($this);
    }

    /**
     * @param User $user
     */
    public function removeUser($user)
    {
        if(!$this->users->contains($user)) {
            return;
        }

        $this->users->removeElement($user);
        $user->removeRole($this);
    }
}
