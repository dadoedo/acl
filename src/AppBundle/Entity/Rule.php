<?php


namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="rule")
 */
class Rule
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
    private $description;

    ## Vazby

    /**
     * @ORM\OneToMany(targetEntity="RoleRule", mappedBy="rule")
     */
    private $roles;

    /**
     * @ORM\ManyToOne(targetEntity="Resource")
     */
    private $resource;

    /**
     * @ORM\ManyToOne(targetEntity="Action")
     */
    private $action;


    public function __construct()
    {
        $this->roles = new ArrayCollection();
    }

    ## Get & Set

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
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
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
        $role->addRule($this);
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
        $role->removeRule($this);
    }

    /**
     * @return mixed
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @param mixed $resource
     */
    public function setResource($resource)
    {
        $this->resource = $resource;
    }

    /**
     * @return mixed
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param mixed $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }
}
