<?php

namespace Lugh\WebAppBundle\DomainLayer\State;

use Lugh\WebAppBundle\DomainLayer\State\StateClass;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Restrictions
 *
 * @author a.navarro
 */
class Restrictions {
    
    static function getContainer()
    {
        global $kernel;
        if ('AppCache' == get_class($kernel)) {
             $kernel = $kernel->getKernel();
        }
        return $kernel->getContainer();
    }
    
    static function selfAdhesion($item, $adhesion)
    {
        return $adhesion->getAccionista() == $item->getAutor();
    }
    static function selfState($item, $state)
    {
        return $item->getState() == $state;
    }
    static function multipleAdhesion($item, $adhesion)
    {
        foreach ($item->getAdhesions() as $ad) {
            if ($ad->getAccionista() == $adhesion->getAccionista())
            {
                return true;
            }
        }
        return false;
    }
    
    static function hasUserPermitedChangeState($item, $state)
    {/* @TODO: Apps */
        $user = self::getContainer()->get('security.context')->getToken()->getUser();
        foreach($user->getRoles() as $role){
            if($role == "ROLE_ADMIN" || $role == "ROLE_SUPER_ADMIN" || $role == "ROLE_CUSTOMER"){
                return true;
            }
        }
        if (self::getApp($item) != '' && !$user->getAccionista()->getApps()[lcfirst(self::getApp($item))])
        {
            return false; 
        }
        if (
                self::getAutor($item)->getUser() == $user && 
                method_exists($item, 'getState') && 
                $item->getState() == StateClass::stateRetornate &&
                $state == StateClass::actionPending
            )
        {
            return true;
        }
        if (
                self::getAutor($item)->getUser() == $user && 
                method_exists($item, 'getState') && 
                ($item->getState() == StateClass::statePublic || $item->getState() == StateClass::statePending) &&
                $state == StateClass::actionRetornate
            )
        {
            return true;
        }
        if (self::hasUserPermitedChangeStateAdhesion($item, $state, $user))
        {
            return true;
        }
        
        return false;
    }
    
    static private function hasUserPermitedChangeStateAdhesion($item, $state, $user)
    {
        $getMethod = self::getItemMethod($item);
        
        if (
                $getMethod != '' &&  
                $item->{$getMethod}()->getAutor() == $user->getAccionista())
        {
            return true;
        }
        if (
                $getMethod != '' &&
                self::hasUserPermissionAdhesion($item, StateClass::actionGet) && 
                method_exists($item, 'getState') && 
                $item->getState() == StateClass::stateRetornate &&
                $state == StateClass::actionPending
            )
        {
            return true;
        }
        if (
                $getMethod != '' &&
                self::hasUserPermissionAdhesion($item, StateClass::actionGet) && 
                method_exists($item, 'getState') && 
                ($item->getState() == StateClass::statePublic || $item->getState() == StateClass::statePending) &&
                $state == StateClass::actionRetornate
            )
        {
            return true;
        }
        
        return false;
    }
    
    static function hasUserPermission($item, $action)
    {/* @TODO: Apps */
        $user = self::getContainer()->get('security.context')->getToken()->getUser();
        $security = self::getContainer()->get('security.context');
        $stateRestricted = array(
            StateClass::actionCreate,
            StateClass::actionGet,
            StateClass::actionDelete
        );

        if (!$security->isGranted('ROLE_CUSTOMER'))
        {
            if (in_array($action, $stateRestricted))
            {
                if ($action==StateClass::actionDelete)
                {
                    return false;
                }
                if (self::getAutor($item) != $user->getAccionista() && method_exists($item, 'getState') && $item->getState() != StateClass::statePublic)
                {
                    return false;
                }
                if (!method_exists($item, 'getState') && self::getAutor($item) != $user->getAccionista())
                {
                    return false;
                }
                if (self::getApp($item) != '' && !$user->getAccionista()->getApps()[strtolower(self::getApp($item))])
                {
                    return false; 
                }
            }
            if ($action== StateClass::actionStore)
            {
                if (self::getApp($item) != '' && !$user->getAccionista()->getApps()[strtolower(self::getApp($item))])
                {
                    return false;
                }
            }
        }
        
        return true;
    }

    static public function hasQuestionPermission($item, $action){

        if($action == StateClass::actionStore && $item::nameClass == 'Question'){
            $storage = self::getContainer()->get('lugh.server')->getStorage();
            $junta = $storage->getJunta();

            $max = self::getContainer()->get('lugh.parameters')->getByKey('Config.Av.maxquestions', '-1');
            $num_questions = count($item->getAutor()->getQuestions());

            if($junta->getPreguntasEnabled() === true && ($max == '-1' || $num_questions < $max)){
                return true;
            }
            else {
                return false;
            }
        }
        return true;
    }
    
    static public function hasDesertionPermission($item, $action){

        if($action == StateClass::actionStore && $item::nameClass == 'Desertion'){
            $storage = self::getContainer()->get('lugh.server')->getStorage();
            $junta = $storage->getJunta();

            if($junta->getAbandonoEnabled() === true) {
                return true;
            }
            else {
                return false;
            }
        }
        return true;
    }
    
    static public function hasAvPermission($item, $action){

        if($action == StateClass::actionStore && $item::nameClass == 'Av'){
            $storage = self::getContainer()->get('lugh.server')->getStorage();
            $junta = $storage->getJunta();

            if($junta->getVotacionEnabled() === true){
                return true;
            }
            else {
                return false;
            }
        }
        return true;
    }

    
    static public function derechoMaxThreads(){
        
        $max = self::getContainer()->get('lugh.parameters')->getByKey('Config.derecho.maxquestions', '-1');
        
        if($max > 0){
        
            $user = self::getContainer()->get('security.context')->getToken()->getUser();
            if($user->getAccionista() != null){
                $num_threads = count($user->getAccionista()->getThreads());

                if($num_threads >= $max){
                    return false;
                }
            }
            else{
                return true;
            }
        
        }
        return true;
        
    }
    
    static function proposalsAllowed(){
        
        $allowed = self::getContainer()->get('lugh.parameters')->getByKey('Config.foro.allowproposals', '1');
        
        if($allowed == '1'){
            return true;
        }
        else{
            return false;
        }
        
    }
    
    
    static private function getItemMethod($item)
    {
        $itemMethod = array(
            'getProposal',
            'getOffer',
            'getRequest',
            'getInitiative'
        );
        
        $getMethod = '';
        foreach ($itemMethod as $method) {
            if (method_exists($item, $method))
            {
                $getMethod = $method;
            }
        }
        return $getMethod;
    }
    
    static function hasUserPermissionAdhesion($item, $action)
    {
        $user = self::getContainer()->get('security.context')->getToken()->getUser();
        $security = self::getContainer()->get('security.context');
        $stateRestricted = array(
            StateClass::actionCreate,
            StateClass::actionGet,
            StateClass::actionDelete
        );
        
        $getMethod = self::getItemMethod($item);
        
        if (!$security->isGranted('ROLE_CUSTOMER'))
        {
            if (in_array($action, $stateRestricted))
            {
                if ($action==StateClass::actionDelete)
                {
                    return false;
                }
                if ($getMethod == '' || (self::getAutor($item) != $user->getAccionista() && $item->{$getMethod}()->getAutor() != $user->getAccionista()))
                {
                    return false;
                }
                /*if ($getMethod == '' || (self::getAutor($item) != $user->getAccionista() && $item->{$getMethod}()->getAutor() == $user->getAccionista() && $item->getState() != StateClass::statePublic))
                {
                    return false;
                }*/
            }
        }
        
        return true;
    }
    
    static function hasUserPermissionEnable($item, $action)
    {
        $user = self::getContainer()->get('security.context')->getToken()->getUser();
        $security = self::getContainer()->get('security.context');
        $stateRestricted = array(
            StateClass::actionCreate,
            StateClass::actionGet,
            StateClass::actionDelete
        );
        
        if (!$security->isGranted('ROLE_CUSTOMER'))
        {
            if (in_array($action, $stateRestricted))
            {
                if ($action==StateClass::actionDelete)
                {
                    return false;
                }
                return $item->getEnabled();
            }
        }
        
        return true;
    }
    
    static function hasUserOwnerMessage($message, $action)
    {
        $user = self::getContainer()->get('security.context')->getToken()->getUser();
        $security = self::getContainer()->get('security.context');
        $stateRestricted = array(
            StateClass::actionCreate,
            StateClass::actionGet,
            StateClass::actionDelete
        );
        
        if (!$security->isGranted('ROLE_CUSTOMER'))
        {
            if (in_array($action, $stateRestricted))
            {
                if ($action==StateClass::actionDelete)
                {
                    return false;
                }
                if (self::getAutor($message->getItem()) != $user->getAccionista())
                {
                    return false;
                }
            }
        }
        
        return true;
    }
    
    static function hasUserOwnerItem($item, $action)
    {
        $user = self::getContainer()->get('security.context')->getToken()->getUser();
        $security = self::getContainer()->get('security.context');
        $stateRestricted = array(
            StateClass::actionCreate,
            StateClass::actionGet,
            StateClass::actionDelete
        );
        
        if (!$security->isGranted('ROLE_CUSTOMER'))
        {
            if (in_array($action, $stateRestricted))
            {
                if ($action==StateClass::actionDelete)
                {
                    return false;
                }
                if (self::getAutor($item) != $user->getAccionista())
                {
                    return false;
                }
            }
        }
        
        return true;
    }
    
    static function hasUserOwnerDocument($document, $action)
    {
        $user = self::getContainer()->get('security.context')->getToken()->getUser();
        $security = self::getContainer()->get('security.context');
        $stateRestricted = array(
            StateClass::actionCreate,
            StateClass::actionGet,
            StateClass::actionDelete
        );
        
        if (!$security->isGranted('ROLE_CUSTOMER'))
        {
            if (in_array($action, $stateRestricted))
            {
                if ($action==StateClass::actionDelete && $document->getOwner() != $user)
                {
                    return false;
                }
            }
        }
        
        return true;
    }
    
    static function hasUserPermissionWriteMessage($item, $action)
    {
        $user = self::getContainer()->get('security.context')->getToken()->getUser();
        $security = self::getContainer()->get('security.context');
        $stateRestricted = array(
            StateClass::actionCreate,
            StateClass::actionGet,
            StateClass::actionDelete
        );

        if (!$security->isGranted('ROLE_CUSTOMER'))
        {
            if (in_array($action, $stateRestricted))
            {
                if ($action==StateClass::actionCreate)
                {
                    if($item->getAutor()->getUser()->getID() !== $user->getID() && $item->getState() != StateClass::statePublic){
                        return false;
                    }

                    if (method_exists($item, 'getLocked') && $item->getLocked())
                    {
                        return false;
                    }

                    if ($item->getAutor()->getUser()->getID() == $user->getID() && 
                            $item->getState() != StateClass::stateRetornate && 
                            $item->getState() != StateClass::statePublic)
                    {
                        return false;
                    }
                }
                if ($action==StateClass::actionDelete)
                {
                    return false;
                }
                
            }
        }
        
        return true;
    }
    
    static function hasUserPermissionReadMail($item, $action)
    {
        $user = self::getContainer()->get('security.context')->getToken()->getUser();
        $security = self::getContainer()->get('security.context');
        $stateRestricted = array(
            StateClass::actionCreate,
            StateClass::actionGet,
            StateClass::actionDelete
        );
        
        if (!$security->isGranted('ROLE_CUSTOMER'))
        {
            if (in_array($action, $stateRestricted))
            {
                if ($action==StateClass::actionDelete)
                {
                    return false;
                }
                if ($item->getUserdest() != $user && $item->getUserfrom() != $user)
                {
                    return false;
                }
            }
        }
        return true;
    }
    
    static function filterUserPermission($itemCl, $action)
    {
        $elements = array();
        $itemCollection = $itemCl != null ? $itemCl : array();
        foreach ($itemCollection as $item) {
            if (self::hasUserPermission($item, $action))
            {
                $elements[] = $item;
            }
        }
        return $elements;
    }
    
    static function filterUserPermissionAdhesion($itemCl, $action)
    {
        $elements = array();
        $itemCollection = $itemCl != null ? $itemCl : array();
        foreach ($itemCollection as $item) {
            if (self::hasUserPermissionAdhesion($item, $action))
            {
                $elements[] = $item;
            }
        }
        return $elements;
    }
    
    static function filterUserPermissionEnable($itemCl, $action)
    {
        $elements = array();
        $itemCollection = $itemCl != null ? $itemCl : array();
        foreach ($itemCollection as $item) {
            if (self::hasUserPermissionEnable($item, $action))
            {
                $elements[] = $item;
            }
        }
        return $elements;
    }
    
    static function filterUserOwnerMessage($itemCl, $action)
    {
        $elements = array();
        $itemCollection = $itemCl != null ? $itemCl : array();
        foreach ($itemCollection as $item) {
            if (self::hasUserOwnerMessage($item, $action))
            {
                $elements[] = $item;
            }
        }
        return $elements;
    }
    
    static function filterUserOwnerItems($itemCl, $action)
    {
        $elements = array();
        $itemCollection = $itemCl != null ? $itemCl : array();
        foreach ($itemCollection as $item) {
            if (self::hasUserOwnerItem($item, $action))
            {
                $elements[] = $item;
            }
        }
        return $elements;
    }
    
    static function filterUserReadMail($itemCl, $action)
    {
        $elements = array();
        $itemCollection = $itemCl != null ? $itemCl : array();
        foreach ($itemCollection as $item) {
            if (self::hasUserPermissionReadMail($item, $action))
            {
                $elements[] = $item;
            }
        }
        return $elements;
    }
    static function parameterInTime($nameClass, $state)
    {
        if (true !== $timeState = self::getContainer()->get('lugh.parameters')->getByKey($nameClass . '.time.' . $state, true)) 
        {
            if (!self::getContainer()->get('lugh.Time')->inTime($timeState))
            {
                return false;
            }
        }
        return true;
    }
    
    static function inTime($item, $state)
    { /* @TODO: Apps */
        $security = self::getContainer()->get('security.context');
        //$user = self::getContainer()->get('security.context')->getToken()->getUser();
        $apps = self::getContainer()->get('lugh.Time')->appInTime();
        if (self::getApp($item) != '' &&  !$apps[strtolower(self::getApp($item))])           
            {return false;}
        if ($security->isGranted('ROLE_CUSTOMER'))                      
            {return true; }
        if (!self::parameterInTime($item::nameClass, $state))           
            {return false;}
        if (!self::parameterInTime($item::nameClass, 'global'))         
            {return false;}
        if (!self::parameterInTime(self::getApp($item), 'activate'))    
            {return false;}
            
        return true;
    }
    
    static function delegationVoteInTime()
    {
        
        if (true !== $timeState =self::getContainer()->get('lugh.parameters')->getByKey('Delegation.time.vote', true))
        {
            if (strtotime($timeState) < time()) 
            {
                return false;
            }
        }
        return true;
    }
    
    static function filterInTime($itemCollection, $state)
    {
        $elements = array();
        foreach ($itemCollection as $item) {
            if (self::inTime($item, $state))
            {
                $elements[] = $item;
            }
        }
        return $elements;
    }

    static private function getAutor($item)
    {
        if (method_exists($item,'getAutor'))
        {
            return $item->getAutor();
        }
        elseif (method_exists($item, 'getAccionista'))
        {
            return $item->getAccionista();
        }
        elseif (method_exists($item, 'getOwner'))
        {
            return $item->getOwner()->getAccionista();
        }
        elseif (defined(get_class($item) . '::nameClass') && $item::nameClass == 'Accionista')
        {
            return $item;
        }
        return null;
    }
    
    static private function getApp($item)
    {
        if (defined(get_class($item) . '::appClass'))
        {
            return $item::appClass;
        }
        return '';
    }
    
    static function filterPuntos($puntos)
    {
        $puntosCollection = array();
        foreach ($puntos as $punto) {
            if ($punto->getParent() == null)
            {
                $puntosCollection[] = $punto;
            }
        }
        return $puntosCollection;
    }
    
    static function filterSubPuntosRetirados($puntos)
    {
        self::subpuntosFilterRecursive($puntos);
        return $puntos;
    }
    
    static function subpuntosFilterRecursive($puntos)
    {
        foreach ($puntos as $punto) {
            $subpuntos = $punto->getSubpuntosFilter();
            if ($subpuntos != false)
            {
                self::subpuntosFilterRecursive($subpuntos);
            }
            $punto->setSubpuntos($subpuntos);
        }
        
    }
    
    static function annulationPermitted($item, $lastItem)
    {
        if ($item::nameClass == 'Anulacion')
        {
            return !($lastItem::nameClass == 'Anulacion');
        }
        if ($item::nameClass == 'AccionRechazada')
        {
            return ($lastItem::nameClass == 'Delegacion');
        }
        return true;
    }
    
    static function delegationNoDelegado($delegacion)
    {
        return $delegacion->getDelegado() == null;
    }
    
    static function delegationMax($delegado)
    {
        $cantDel = 0;
        $paramDel = self::getContainer()->get('lugh.parameters')->getByKey('Config.numDelegaciones.max', INF);
        foreach ($delegado->getAccionistasDelegacion() as $accionista) {
            if (self::getContainer()->get('security.context')->getToken()->getUser()->getAccionista() != $accionista)
            {
                $lastAccion = $accionista->findLastAccion();
                if ($lastAccion::nameClass != 'Anulacion' && $lastAccion::nameClass != 'AccionRechazada')
                {
                    $cantDel ++;
                }
            }
        }
        return $delegado->getHasDelegationLimit() ? $cantDel >= $delegado->getMaxDelegations() : $cantDel >= $paramDel;
    }
    
    static function votoMax($votacion, $voto)
    {
        if ($votacion != null)
        {
            $tipo = $voto->getPunto()->getTipoVoto();
            if (is_a($votacion, 'Doctrine\Common\Collections\ArrayCollection') && $votacion->count() > 0)
            {
                $votacion = $votacion->toArray();
            }
            elseif(is_a($votacion, 'Doctrine\Common\Collections\ArrayCollection') && $votacion->count() == 0)
            {
                $votacion = array();
            }
            $votos = array_filter($votacion, function($element)use($tipo){return $element->getPunto()->getTipoVoto() == $tipo;});
            if (count($votos) >= $tipo->getMaxVotos())
            {
                return true;
            }
        }
        return false;

    }
    static function votoSerieMaxMin($votacion, $tipo)
    {
        $votos = array_filter($votacion, function($element)use($tipo){return $element->getPunto()->getTipoVoto() == $tipo;});
        if (count($votos) > $tipo->getMaxVotos() || count($votos) < $tipo->getMinVotos())
        {
            return true;
        }
        return false;

    }
    static function votoMin($votacion, $tipo)
    {
        $votos = array_filter($votacion, function($element)use($tipo){return $element->getPunto()->getTipoVoto() == $tipo;});
        if (count($votos) > $tipo->getMaxVotos() || count($votos) < $tipo->getMinVotos())
        {
            return true;
        }
        return false;

    }
    
    static function isCustomer()
    {
        $security = self::getContainer()->get('security.context');
        return $security->isGranted('ROLE_CUSTOMER');
        
    }
    
    static function hasSubpunto($punto)
    {
        $subpuntos = $punto->getSubpuntosFilter();
        if ($subpuntos != null && $subpuntos->count() > 0)
        {
            return true;
        }
        return false;
    }
    
    static function duplicateVoto($votacion, $voto)
    {
        foreach ($votacion as $key => $element) {
            if ($element->getPunto()==$voto->getPunto()) {
                return true;
            }
        }
        return false;
    }
    
    static function lastItem($item)
    {
        return $item->findAccionAnterior() == $item;
    }
    
    static function restrictionOpcionVoto($punto, $opcionVoto) 
    {
        foreach ($punto->getGruposOV()->getOpcionesVoto()->toArray() as $key => $element) {
            if ($element==$opcionVoto) {
                return true;
            }
        }
    }
    
    
    # Pasa a estado Pendiente
    static function AccionistaRequestOfferAdhesionsPending($item)
    {
        $accionista = self::getAutor($item);
        switch ($item::nameClass) {
            case 'Offer':
                foreach ($accionista->getOffers() as $offer) {
                    if ($offer->getId() != $item->getId())
                    {
                        return false;
                    }
                }
                foreach ($accionista->getRequests() as $request) {
                    if ($request->getState() == StateClass::statePending || $request->getState() == StateClass::statePublic)
                    {
                        return false;
                    }
                }
                foreach ($accionista->getAdhesionsOffers() as $adhesionOffer) {
                    if ($adhesionOffer->getState() == StateClass::statePending || $adhesionOffer->getState() == StateClass::statePublic)
                    {
                        return false;
                    }
                }

                break;
            case 'Request':
                foreach ($accionista->getOffers() as $offer) {
                    if ($offer->getState() == StateClass::statePending || $offer->getState() == StateClass::statePublic)
                    {
                        return false;
                    }
                }
                foreach ($accionista->getRequests() as $request) {
                    if ($request->getId() != $item->getId())
                    {
                        return false;
                    }
                }
                foreach ($accionista->getAdhesionsOffers() as $adhesionOffer) {
                    if ($adhesionOffer->getState() == StateClass::statePending || $adhesionOffer->getState() == StateClass::statePublic)
                    {
                        return false;
                    }
                }
                foreach ($accionista->getAdhesionsRequests() as $adhesionRequest) {
                    if ($adhesionRequest->getState() == StateClass::statePending || $adhesionRequest->getState() == StateClass::statePublic)
                    {
                        return false;
                    }
                }

                break;
            case 'AdhesionOffer':
                foreach ($accionista->getOffers() as $offer) {
                    if ($offer->getState() == StateClass::statePending || $offer->getState() == StateClass::statePublic)
                    {
                        return false;
                    }
                }
                foreach ($accionista->getRequests() as $request) {
                    if ($request->getState() == StateClass::statePending || $request->getState() == StateClass::statePublic)
                    {
                        return false;
                    }
                }
                foreach ($accionista->getAdhesionsOffers() as $adhesionOffer) {
                    if ($adhesionOffer->getId()!= $item->getId() && $adhesionOffer->getState() == StateClass::statePublic)
                    {
                        return false;
                    }
                }
                foreach ($accionista->getAdhesionsRequests() as $adhesionRequest) {
                    if ($adhesionRequest->getState() == StateClass::statePending || $adhesionRequest->getState() == StateClass::statePublic)
                    {
                        return false;
                    }
                }

                break;
            case 'AdhesionRequest':
                foreach ($accionista->getRequests() as $request) {
                    if ($request->getState() == StateClass::statePending || $request->getState() == StateClass::statePublic)
                    {
                        return false;
                    }
                }
                foreach ($accionista->getAdhesionsOffers() as $adhesionOffer) {
                    if ($adhesionOffer->getState() == StateClass::statePending || $adhesionOffer->getState() == StateClass::statePublic)
                    {
                        return false;
                    }
                }
                break;

            default:
                break;
        }
        
        return true;
    }
    
    # Pasa a estado Publico
    static function AccionistaRequestOfferAdhesionsPublic($item)
    {
        $accionista = self::getAutor($item);
        switch ($item::nameClass) {
            case 'Offer': 
                foreach ($accionista->getOffers() as $offer) {
                    if ($offer->getId() != $item->getId())
                    {
                        return false;
                    }
                }
                foreach ($accionista->getRequests() as $request) {
                    if ($request->getState() == StateClass::statePending || $request->getState() == StateClass::statePublic)
                    {
                        return false;
                    }
                }
                foreach ($accionista->getAdhesionsOffers() as $adhesionOffer) {
                    if ($adhesionOffer->getState() == StateClass::statePending || $adhesionOffer->getState() == StateClass::statePublic)
                    {
                        return false;
                    }
                }

                break;
            case 'Request':
                foreach ($accionista->getOffers() as $offer) {
                    if ($offer->getState() == StateClass::statePending || $offer->getState() == StateClass::statePublic)
                    {
                        return false;
                    }
                }
                foreach ($accionista->getRequests() as $request) {
                    if ($request->getId() != $item->getId())
                    {
                        return false;
                    }
                }
                foreach ($accionista->getAdhesionsOffers() as $adhesionOffer) {
                    if ($adhesionOffer->getState() == StateClass::statePending || $adhesionOffer->getState() == StateClass::statePublic)
                    {
                        return false;
                    }
                }
                foreach ($accionista->getAdhesionsRequests() as $adhesionRequest) {
                    if ($adhesionRequest->getState() == StateClass::statePending || $adhesionRequest->getState() == StateClass::statePublic)
                    {
                        return false;
                    }
                }
                
                break;
            case 'AdhesionOffer':
                foreach ($accionista->getOffers() as $offer) {
                    if ($offer->getState() == StateClass::statePending || $offer->getState() == StateClass::statePublic)
                    {
                        return false;
                    }
                }
                foreach ($accionista->getRequests() as $request) {
                    if ($request->getState() == StateClass::statePending || $request->getState() == StateClass::statePublic)
                    {
                        return false;
                    }
                }
                foreach ($accionista->getAdhesionsOffers() as $adhesionOffer) {
                    if ($adhesionOffer->getId() != $item->getId() && $adhesionOffer->getState() == StateClass::statePublic)
                    {
                        return false;
                    }
                }
                foreach ($accionista->getAdhesionsRequests() as $adhesionRequest) {
                    if ($adhesionRequest->getState() == StateClass::statePending || $adhesionRequest->getState() == StateClass::statePublic)
                    {
                        return false;
                    }
                }
                
                break;
            case 'AdhesionRequest':
                foreach ($accionista->getRequests() as $request) {
                    if ($request->getState() == StateClass::statePending || $request->getState() == StateClass::statePublic)
                    {
                        return false;
                    }
                }
                foreach ($accionista->getAdhesionsOffers() as $adhesionOffer) {
                    if ($adhesionOffer->getState() == StateClass::statePending || $adhesionOffer->getState() == StateClass::statePublic)
                    {
                        return false;
                    }
                }
                break;

            default:
                break;
        }
        
        return true;
    }
    
    # Pasa a estado Retornado
    static function AccionistaRequestOfferAdhesionsRetornate($item)
    {
        $accionista = self::getAutor($item);
        switch ($item::nameClass) {
            case 'Offer':
                foreach ($accionista->getOffers() as $offer) {
                    if ($offer->getId() != $item->getId())
                    {
                        return false;
                    }
                }
                
                break;
            case 'Request':
                foreach ($accionista->getRequests() as $request) {
                    if ($request->getId() != $item->getId())
                    {
                        return false;
                    }
                }

                break;
            case 'AdhesionOffer':

                break;
            case 'AdhesionRequest':
                
                break;

            default:
                break;
        }
        
        return true;
    }
    
    # Pasa a estado Rechazado
    static function AccionistaRequestOfferAdhesionsReject($item)
    {
        $accionista = self::getAutor($item);
        switch ($item::nameClass) {
            case 'Offer':
                foreach ($accionista->getOffers() as $offer) {
                    if ($offer->getId() != $item->getId())
                    {
                        return false;
                    }
                }
                
                break;
            case 'Request':
                foreach ($accionista->getRequests() as $request) {
                    if ($request->getId() != $item->getId())
                    {
                        return false;
                    }
                }

                break;
            case 'AdhesionOffer':

                break;
            case 'AdhesionRequest':
                
                break;

            default:
                break;
        }
        
        return true;
    }
    
    
    # Pasa a estado Pendiente
    static function ItemRequestOfferAdhesionsPending($item)
    {
        switch ($item::nameClass) {
            case 'Offer':
                
                break;
            case 'Request':

                break;
            case 'AdhesionOffer':
                if ($item->getOffer()->getState() != StateClass::statePending && $item->getOffer()->getState() != StateClass::statePublic)
                {
                    return false;
                }
                break;
            case 'AdhesionRequest':
                if ($item->getRequest()->getState() != StateClass::statePending && $item->getRequest()->getState() != StateClass::statePublic)
                {
                    return false;
                }
                break;

            default:
                break;
        }
        
        return true;
    }
    
    # Pasa a estado Publico
    static function ItemRequestOfferAdhesionsPublic($item)
    {
        switch ($item::nameClass) {
            case 'Offer':     
                foreach ($item->getAdhesions() as $adhesion) {
                    if ($adhesion->getState() == StateClass::statePublic)
                    {
                        return false;
                    }
                }
                break;
            case 'Request':
                foreach ($item->getAdhesions() as $adhesion) {
                    if ($adhesion->getState() == StateClass::statePublic)
                    {
                        return false;
                    }
                }
                break;
            case 'AdhesionOffer':
                if ($item->getOffer()->getState() != StateClass::statePublic)
                {
                    return false;
                }
                break;
            case 'AdhesionRequest':
                if ($item->getRequest()->getState() != StateClass::statePublic)
                {
                    return false;
                }
                foreach ($item->getRequest()->getAdhesions() as $adhesionRequest) {
                    if ($adhesionRequest->getState() == StateClass::statePublic)
                    {
                        return false;
                    }
                }
                break;

            default:
                break;
        }
        
        return true;
    }
    
    # Pasa a estado Retornado
    static function ItemRequestOfferAdhesionsRetornate($item)
    {
        switch ($item::nameClass) {
            case 'Offer':

                break;
            case 'Request':

                break;
            case 'AdhesionOffer':

                break;
            case 'AdhesionRequest':
                
                break;

            default:
                break;
        }
        
        return true;
    }
    
    # Pasa a estado Rechazado
    static function ItemRequestOfferAdhesionsReject($item)
    {
        switch ($item::nameClass) {
            case 'Offer':
                
                break;
            case 'Request':
                
                break;
            case 'AdhesionOffer':

                break;
            case 'AdhesionRequest':
                
                break;

            default:
                break;
        }
        
        return true;
    }
    

}

?>
