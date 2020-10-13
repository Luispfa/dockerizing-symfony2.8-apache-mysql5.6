<?php
namespace Lugh\WebAppBundle\DomainLayer\Director;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of LughDirectorBuilder
 *
 * @author a.navarro
 */
class LughDirectorBehavior extends LughDirector {
    
    public function multipleAdhesion($item, $adhesion) {
        return $this->builder->multipleAdhesion($item, $adhesion);
    }

    public function selfAdhesion($item, $adhesion) {
        return $this->builder->selfAdhesion($item, $adhesion);
    }

    public function hasUserPermission($item, $action) {
        return $this->builder->hasUserPermission($item, $action);
    }
    public function filterUserPermission($itemCollection, $action) {
        return $this->builder->filterUserPermission($itemCollection, $action);
    }
    public function filterUserPermissionAdhesion($itemCollection, $action) {
        return $this->builder->filterUserPermissionAdhesion($itemCollection, $action);
    }
    public function filterUserOwnerItem($itemCollection, $action) {
        return $this->builder->filterUserOwnerItem($itemCollection, $action);
    }
    public function hasUserPermissionItem($item, $action) {
        return $this->builder->hasUserPermissionItem($item, $action);
    }
    public function hasUserPermissionWriteMessage($item, $action) {
        return $this->builder->hasUserPermissionWriteMessage($item, $action);
    }
    public function filterUserOwnerItems($itemCollection, $action) {
        return $this->builder->filterUserOwnerItems($itemCollection, $action);
    }
    public function hasUserPermissionDocument($item, $action) {
        return $this->builder->hasUserPermissionDocument($item, $action);
    }
    public function annulationPermitted($item, $lastItem) {
        return $this->builder->annulationPermitted($item, $lastItem);
    }
    public function annulationVoto($accionista) {
        return $this->builder->annulationVoto($accionista);
    }
    public function putPendingAppAv($item) {
        return $this->builder->putPendingAppAv($item);
    }
    public function delegationNoDelegado($item) {
        return $this->builder->delegationNoDelegado($item);
    }
    public function delegationNoVoteInTime() {
        return $this->builder->delegationNoVoteInTime();
    }
    public function accionistaRegister($accionista) {
        return $this->builder->accionistaRegister($accionista);
    }
    public function noContent($content)  {
        return $this->builder->noContent($content);
    }
    public function formatString($string)  {
        return $this->builder->formatString($string);
    }
    public function filterUserOwnerMessage($item, $action) {
        return $this->builder->filterUserOwnerMessage($item, $action);
    }
    public function filterItemsInTime($itemCollection, $state = 'get') {
        return $this->builder->filterItemsInTime($itemCollection, $state);
    }
    public function maxDelegation($item) {
         return $this->builder->maxDelegation($item);
    }
    public function maxVotos($votacion, $voto) {
        return $this->builder->maxVotos($votacion, $voto);
    }
    public function addVotos($accion, $votacion) {
        return $this->builder->addVotos($accion, $votacion);
    }
    public function hasSubpunto($punto) {
        return $this->builder->hasSubpunto($punto);
    }
    public function getVotoSerieDecrypt($votacionesSerie) {
        return $this->builder->getVotoSerieDecrypt($votacionesSerie);
    } 
    public function restrictionOpcionVoto($punto, $opcionVoto) {
        return $this->builder->restrictionOpcionVoto($punto, $opcionVoto);
    }
    public function delegationCreate($delegation) {
        return $this->builder->delegationCreate($delegation);
    }
    public function userExist($username) {
         return $this->builder->userExist($username);
    }
    public function emailExist($email, $usermail) {
        return $this->builder->emailExist($email, $usermail);
    }
    public function documentNumExist($documentNum, $newDocumentNum) {
        return $this->builder->documentNumExist($documentNum, $newDocumentNum);
    }
    public function getDefaultState($item) {
        return $this->builder->getDefaultState($item);
    }
    public function createAccionista($userjson, $accionistajson, $cert = null) {
        return $this->builder->createAccionista($userjson, $accionistajson, $cert);
    }
    public function regrantAccionista($user, $userElement, $accionistaElement, $bodyMessage = false) {
        return $this->builder->regrantAccionista($user, $userElement, $accionistaElement, $bodyMessage);
    }
    public function regrantVoto($user, $userElement, $accionistaElement, $bodyMessage = false) {
        return $this->builder->regrantVoto($user, $userElement, $accionistaElement, $bodyMessage);
    }
    public function regrantForo($user, $userElement, $accionistaElement, $bodyMessage = false) {
        return $this->builder->regrantForo($user, $userElement, $accionistaElement, $bodyMessage);
    }
    public function regrantDerecho($user, $userElement, $accionistaElement, $bodyMessage = false) {
        return $this->builder->regrantDerecho($user, $userElement, $accionistaElement, $bodyMessage);
    }
    public function regrantAv($user, $userElement, $accionistaElement, $bodyMessage = false) {
        return $this->builder->regrantAv($user, $userElement, $accionistaElement, $bodyMessage);
    }
    public function setAcitveLiveAccionista($accionista, $idLive, $active) {
        return $this->builder->setAcitveLiveAccionista($accionista, $idLive, $active);
    }
    public function setJuntaStateEnabled($junta, $state, $enabled) {
        return $this->builder->setJuntaStateEnabled($junta, $state, $enabled);
    }
    public function getJuntaStateEnabled($junta, $state, $toState) {
        return $this->builder->getJuntaStateEnabled($junta, $state, $toState);
    }
    public function setAccionistaAcreditado($accionista, $acreditado) {
        return $this->builder->setAccionistaAcreditado($accionista, $acreditado);
    }
    public function mailerAction($item, $state, $extra = '', $external = array()) {
        return $this->builder->mailerAction($item, $state, $extra, $external);
    }
}

?>
