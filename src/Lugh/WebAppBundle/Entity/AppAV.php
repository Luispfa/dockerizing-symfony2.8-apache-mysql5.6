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
 * AppAV
 *
 * @ORM\Entity
 * @ExclusionPolicy("all")
 */
class AppAV extends App
{
    const nameClass = 'AppAV';
    
    /**
     * @ORM\OneToMany(targetEntity="AppAVLive", mappedBy="av")
     * @Expose
     **/
    private $lives;

    public function __construct() {
        $this->setAppState($this->getState());
    }
        
    public function pendiente($comments = null) {
        return $this->appState->pendiente($this, $comments);
    }
    public function publica($comments = null) {
        $behavior = $this->getContainer()->get('lugh.server')->getBehavior();
        $behavior->annulationVoto($this->getAccionista());
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
        return 'AV';
    }


    /**
     * Add lives
     *
     * @param \Lugh\WebAppBundle\Entity\AppAVLive $lives
     * @return AppAV
     */
    public function addLife(\Lugh\WebAppBundle\Entity\AppAVLive $lives)
    {
        $this->lives[] = $lives;

        return $this;
    }

    /**
     * Remove lives
     *
     * @param \Lugh\WebAppBundle\Entity\AppAVLive $lives
     */
    public function removeLife(\Lugh\WebAppBundle\Entity\AppAVLive $lives)
    {
        $this->lives->removeElement($lives);
    }

    /**
     * Get lives
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getLives()
    {
        return $this->lives;
    }
}
