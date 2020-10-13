<?php
namespace Lugh\WebAppBundle\DomainLayer\State;
use Lugh\WebAppBundle\DomainLayer\State\Restrictions;
use Symfony\Component\Config\Definition\Exception\Exception;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Accionista
 *
 * @author a.navarro
 */
class RetornadoStateClass extends StateClass {
    
    public function pendiente($item, $comments = null) {
        $this->selfState($item, self::statePending);
        //$this->exceptionTime($item, self::actionPending);
        $this->restrictions($item, self::actionPending);
        $this->AccionistaChangeStatePending($item);
        $this->ItemChangeStatePending($item);
        return $this->setChanges($item, self::statePending, self::actionPending, $comments);
    }
    public function publica($item, $comments = null) {
        $this->selfState($item, self::statePublic);
        $this->restrictions($item, self::actionPublic);
        $this->AccionistaChangeStatePublic($item);
        $this->ItemChangeStatePublic($item);
        return $this->setChanges($item, self::statePublic, self::actionPublic, $comments);
    }
    public function retorna($item, $comments = null) {
        $this->selfState($item, self::stateRetornate);
        $this->restrictions($item, self::actionRetornate);
        $this->AccionistaChangeStateRetornate($item);
        $this->ItemChangeStateRetornate($item);
        return $this->setChanges($item, self::stateRetornate, self::actionRetornate, $comments);
    }
    public function rechaza($item, $comments = null) {
        $this->selfState($item, self::stateReject);
        $this->restrictions($item, self::actionReject);
        $this->AccionistaChangeStateReject($item);
        $this->ItemChangeStateReject($item);
        return $this->setChanges($item, self::stateReject, self::actionReject, $comments);
    }
    
    public function locked($item, $comments = null) {
        return $item;
    }

    public function unlocked($item, $comments = null) {
        return $item;
    }
    
    public function enable($item, $comments = null) {
        return $item;
    }

    public function disable($item, $comments = null) {
        return $item;
    }
    
    public function asistencia($junta) {
        return $junta;
    }

    public function configuracion($junta) {
        return $junta;
    }

    public function convocatoria($junta) {
        return $junta;
    }

    public function finalizado($junta) {
        return $junta;
    }

    public function prejunta($junta) {
        return $junta;
    }

    public function quorumcerrado($junta) {
        return $junta;
    }

    public function votacion($junta) {
        return $junta;
    }
    
    protected function restrictions($item, $state)
    {
        $this->exceptionUser($item, $state);
        $this->exceptionTime($item, $state);
    }
    
    protected function exceptionUser($item, $state)
    {
        if (!Restrictions::hasUserPermitedChangeState($item, $state))
        {
            throw new Exception("User not has permited change state");
        }
    }
    protected function exceptionTime($item, $state)
    {
        if (!Restrictions::inTime($item, $state))
        {
            throw new Exception("item not in time to change state " . $state);
        }
    }
    protected function selfState($item, $state)
    {
        if (Restrictions::selfState($item, $state))
        {
            throw new Exception("No change to self state");
        }
    }
    
    protected function AccionistaChangeStatePending($item)
    {
        if (Restrictions::AccionistaRequestOfferAdhesionsPending($item) == false)
        {
            throw new Exception("Restrictions for Shareholder on change State");
        }
    }
    protected function AccionistaChangeStatePublic($item)
    {
        if (Restrictions::AccionistaRequestOfferAdhesionsPublic($item) == false)
        {
            throw new Exception("Restrictions for Shareholder on change State");
        }
    }
    protected function AccionistaChangeStateRetornate($item)
    {
        if (Restrictions::AccionistaRequestOfferAdhesionsRetornate($item) == false)
        {
            throw new Exception("Restrictions for Shareholder on change State");
        }
    }
    protected function AccionistaChangeStateReject($item)
    {
        if (Restrictions::AccionistaRequestOfferAdhesionsReject($item) == false)
        {
            throw new Exception("Restrictions for Shareholder on change State");
        }
    }
    protected function ItemChangeStatePending($item)
    {
        if (Restrictions::ItemRequestOfferAdhesionsPending($item) == false)
        {
            throw new Exception("Restrictions for Item on change State");
        }
    }
    protected function ItemChangeStatePublic($item)
    {
        if (Restrictions::ItemRequestOfferAdhesionsPublic($item) == false)
        {
            throw new Exception("Restrictions for Item on change State");
        }
    }
    protected function ItemChangeStateRetornate($item)
    {
        if (Restrictions::ItemRequestOfferAdhesionsRetornate($item) == false)
        {
            throw new Exception("Restrictions for Item on change State");
        }
    }
    protected function ItemChangeStateReject($item)
    {
        if (Restrictions::ItemRequestOfferAdhesionsReject($item) == false)
        {
            throw new Exception("Restrictions for Item on change State");
        }
    }
    protected function setChanges($item, $state, $action, $comments)
    {
        $this->CascadeAdhesion($item, $state);
        $this->mailerState($item,$action, $this->getExternal($comments));
        return $item->setState($state);
    }
    
    protected function CascadeAdhesion($item, $state)
    {
        switch ($state) {
            case StateClass::stateNew:

                break;
            case StateClass::statePending:
                return $this->CascadeAdhesionPending($item);
                break;
            case StateClass::statePublic:
                return $this->CascadeAdhesionPublic($item);
                break;
            case StateClass::stateReject:
                return $this->CascadeAdhesionReject($item);
                break;
            case StateClass::stateRetornate:
                return $this->CascadeAdhesionRetornate($item);
                break;
            default:
                break;
        }
        return $item;     
    }
    protected function CascadeAdhesionPending($item)
    {
        switch ($item::nameClass) {
            case 'Offer':
            case 'Request':
            case 'Proposal':
            case 'Initiative':
                foreach ($item->getAdhesions() as $adhesion) {
                    if ($adhesion->getState() == StateClass::statePublic)
                    {
                        $adhesion->pendiente();
                    }
                }
                break;
            default:
                break;
        }
        return $item;
    }
    protected function CascadeAdhesionPublic($item)
    {
        return $item;
    }
    protected function CascadeAdhesionRetornate($item)
    {
        switch ($item::nameClass) {
            case 'Offer':
            case 'Request':
            case 'Proposal':
            case 'Initiative':
                foreach ($item->getAdhesions() as $adhesion) {
                    if ($adhesion->getState() == StateClass::statePending || $adhesion->getState() == StateClass::statePublic)
                    {
                        $adhesion->retorna();
                    }
                }
                break;
            default:
                break;
        }
        return $item;
    }
    protected function CascadeAdhesionReject($item)
    {
        switch ($item::nameClass) {
            case 'Offer':
            case 'Request':
            case 'Proposal':
            case 'Initiative':
                foreach ($item->getAdhesions() as $adhesion) {
                    if ($adhesion->getState() == StateClass::statePending || $adhesion->getState() == StateClass::statePublic || $adhesion->getState() == StateClass::stateRetornate)
                    {
                        $adhesion->rechaza();
                    }
                }
                break;
            default:
                break;
        }
        return $item;
    }
}

?>
