<?php


namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

   /**
     * @ORM\Entity
     * @ORM\Table(name="user")
     */
class User
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
     * @ORM\Column(type="string")
     */
    private $surname;


    ## Vazby

    /**
     * @ORM\OneToMany(targetEntity="RoleUser", mappedBy="user")
     * @ORM\JoinTable(name="user_role")
     */
    private $roles;

    public function __construct()
    {
        $this->roles = new ArrayCollection();
    }

    ## Getre Setre

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
     * @return string
     */
    public function getSurname()
    {
        return $this->surname;
    }

    /**
     * @param string $surname
     */
    public function setSurname($surname)
    {
        $this->surname = $surname;
    }

    /**
     * @return ArrayCollection|RoleRule[]
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @param Role $role
     */
    public function addRole($role)
    {
        if($this->roles->contains($role)) {
            return;
        }

        $this->roles->add($role);
        $role->addUser($this);
    }

    /**
     * @param Role $role
     */
    public function removeRole($role)
    {
        if(!$this->roles->contains($role)) {
            return;
        }

        $this->roles->removeElement($role);
        $role->removeUser($this);
    }

}
