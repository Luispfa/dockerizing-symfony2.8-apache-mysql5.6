<?php

namespace Lugh\WebAppBundle\Entity;
use Symfony\Component\Config\Definition\Exception\Exception;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\Expose;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\Accessor;
use JMS\Serializer\Annotation\Type;

use Doctrine\ORM\Mapping as ORM;

/**
 * ItemAccionista
 *
 * @ORM\Table()
 * @ORM\Entity @ORM\HasLifecycleCallbacks
 * @ExclusionPolicy("all")
 */
class ItemAccionista extends Item
{
    const nameClass = 'ItemAccionista';
    /**
     * @ORM\Column(name="id", type="string", length=36)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $id;
    
    /**
     * @ORM\OneToMany(targetEntity="Message", mappedBy="itemAccionista", cascade={"persist", "remove"})
     * @Expose
     * @Type("array") 
     * @Accessor(getter="getMessages")
     * @ORM\OrderBy({"dateTime" = "DESC"})
     */
    private $messages;
    
    /**
     * @ORM\OneToOne(targetEntity="Accionista", inversedBy="itemAccionista", cascade={"persist"})
     * @ORM\JoinColumn(name="autor_id", referencedColumnName="id", nullable=false)
     * @Expose
     */
    private $autor;
    
    
    

    public function __construct() {
        $this->setItemState($this->getState());
    }
        
    /** 
     * @ORM\PostLoad 
     */
    public function doStateOnPostLoad()
    {
        $this->setItemState($this->getState());
    }
    public function pendiente($comments = null) {
        $ret = $this->itemState->pendiente($this, $comments);
        $this->uniqueRole($this->getAutor()->getUser(), 'ROLE_USER_PEN');
        $this->getAutor()->getUser()->setEnabled(true);
        return $ret;
    }
    public function publica($comments = null) {
        $ret =  $this->itemState->publica($this, $comments);
        $this->uniqueRole($this->getAutor()->getUser(), $this->userCert($this->getAutor()->getUser())); //ROLE_USER_FULL
        $this->getAutor()->getUser()->setEnabled(true);
        return $ret;
    }
    public function retorna($comments = null) {
        $ret =  $this->itemState->retorna($this, $comments);
        $this->getAutor()->getUser()->setEnabled(true);
        $this->uniqueRole($this->getAutor()->getUser(), 'ROLE_USER_RET');
        return $ret;
    }
    public function rechaza($comments = null) {
        $ret =  $this->itemState->rechaza($this, $comments);
        $this->getAutor()->getUser()->setEnabled(false);
        $this->uniqueRole($this->getAutor()->getUser());
        return $ret;
    }
    
    private function uniqueRole($user, $role = null)
    {
        $roles = $user->getRoles();
        foreach ($roles as $r) {
           $user->removeRole($r); 
        }
        if ($role != null)
        {
            $user->addRole($role);
        }
    }
    
    private function userCert($user)
    {
        if ($user->getCert() != null && $user->getCert() != '')
        {
            return 'ROLE_USER_CERT';
        }
        return 'ROLE_USER_FULL';
    }
    
    private function restrictions($accionista)
    {
        $behavior = $this->getContainer()->get('lugh.server')->getBehavior();
        return $behavior->accionistaRegister($accionista);
    }
    
    public function preSave()
    {
        if ($this->getId() == null)
        {
            $this->restrictions($this->autor);
        }
    }

    /**
     * Get id
     *
     * @return string 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Add messages
     *
     * @param \Lugh\WebAppBundle\Entity\Message $messages
     * @return ItemAccionista
     */
    public function addMessage(\Lugh\WebAppBundle\Entity\Message $messages)
    {
        $messages->setItemAccionista($this);
        $this->messages[] = $messages;

        return $this;
    }

    /**
     * Remove messages
     *
     * @param \Lugh\WebAppBundle\Entity\Message $messages
     */
    public function removeMessage(\Lugh\WebAppBundle\Entity\Message $messages)
    {
        $this->messages->removeElement($messages);
    }

    /**
     * Get messages
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getMessages()
    {
        return $this->messages;
    }
    

    /**
     * Set autor
     *
     * @param \Lugh\WebAppBundle\Entity\Accionista $autor
     * @return ItemAccionista
     */
    public function setAutor(\Lugh\WebAppBundle\Entity\Accionista $autor)
    {
        $autor->setItemAccionista($this);
        $this->autor = $autor;

        return $this;
    }

    /**
     * Get autor
     *
     * @return \Lugh\WebAppBundle\Entity\Accionista 
     */
    public function getAutor()
    {
        return $this->autor;
    }
}
