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
 * AppVoto
 *
 * @ORM\Entity @ORM\HasLifecycleCallbacks
 * @ExclusionPolicy("all")
 */
class AppVoto extends App
{
    const nameClass = 'AppVoto';

    public function __construct() {
        $this->setAppState($this->getState());
    }
        
    
    public function pendiente($comments = null) {
        return $this->appState->pendiente($this, $comments);
    }
    public function publica($comments = null) {
        return $this->appState->publica($this, $comments);
    }
    public function retorna($comments = null) {
        return $this->appState->retorna($this, $comments);
    }
    public function rechaza($comments = null) {
        return $this->appState->rechaza($this, $comments);
    }
    
    public function preSave() {
        parent::preSave();
    }

    public function getAppClass() {
        return 'Voto';
    }

//    public function preSave()
//    {
//        if ($this->getId() == null)
//        {
//            $this->restrictions($this->accionista);
//        }
//    }


    /**
     * @ORM\PrePersist
     */
    public function doStateOnPrePersist()
    {
        // Add your code here
    }

    /**
     * @ORM\PostLoad
     */
    public function doStateOnPostLoad()
    {
        // Add your code here
    }
}
