<?php
namespace Lugh\WebAppBundle\DomainLayer\Behavior;
use Lugh\WebAppBundle\Entity\Offer;
use Lugh\WebAppBundle\Entity\Initiative;
use Lugh\WebAppBundle\Entity\AdhesionInitiative;
use Lugh\WebAppBundle\Entity\AdhesionOffer;
use Lugh\WebAppBundle\Entity\AdhesionProposal;
use Lugh\WebAppBundle\Entity\AdhesionRequest;
use Lugh\WebAppBundle\Entity\ItemAccionista;
use Lugh\WebAppBundle\Entity\Proposal;
use Lugh\WebAppBundle\Entity\Request;
use Lugh\WebAppBundle\Entity\Thread;
use Lugh\WebAppBundle\Entity\Accionista;


/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of LughBuilderProd
 *
 * @author a.navarro
 */
class BehaviorTest extends BehaviorProd{
    
    public function noContent($content) 
    {
        return $content;
    }
    
}

?>
