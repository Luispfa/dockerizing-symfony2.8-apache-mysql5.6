<?php
namespace Lugh\WebAppBundle\DomainLayer\Behavior;
use Lugh\WebAppBundle\DomainLayer\Builder\Builder;
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of LughBuilder
 *
 * @author a.navarro
 */
abstract class Behavior extends Builder{
    
    protected $mailer;
    
    
    public function __construct($container) {
        parent::__construct($container);
        $this->mailer = $this->get('mailer.builder');
    }
    
    abstract function selfAdhesion($item,$adhesion);
    abstract function multipleAdhesion($item,$adhesion);
    abstract function hasUserPermission($item, $action);
    abstract function filterUserPermission($itemCollection, $action);
    abstract function filterUserPermissionAdhesion($itemCollection, $action);
    abstract function hasUserPermissionWriteMessage($item, $action);
    abstract function filterUserOwnerMessage($itemCollection, $action);
    abstract function hasUserPermissionItem($item, $action);
    abstract function hasUserPermissionDocument($item, $action);
    abstract function filterUserOwnerItems($itemCollection, $action);
    abstract function annulationPermitted($item, $lastItem);
    abstract function annulationVoto($accionista);
    abstract function putPendingAppAv($item);
    abstract function delegationNoDelegado($item);
    abstract function delegationNoVoteInTime();
    abstract function accionistaRegister($accionista);
    abstract function noContent($content);
    abstract function formatString($string);
    abstract function filterItemsInTime($itemCollection, $state = 'get');
    abstract function maxDelegation($item);
    abstract function maxVotos($votacion, $voto);
    abstract function addVotos($accion, $votacion);
    abstract function hasSubpunto($punto);
    abstract function getVotoSerieDecrypt($votacionesSerie);
    abstract function restrictionOpcionVoto($punto, $opcionVoto);
    abstract function delegationCreate($delegation);
    abstract function userExist($username);
    abstract function emailExist($email, $usermail);
    abstract function documentNumExist($documentNum, $newDocumentNum);
    abstract function getDefaultState($item);
    abstract function createAccionista($userjson, $accionistajson, $cert = null);
    abstract function regrantAccionista($user, $userElement, $accionistaElement, $bodyMessage = false);
    abstract function regrantVoto($user, $userElement, $accionistaElement, $bodyMessage = false);
    abstract function regrantForo($user, $userElement, $accionistaElement, $bodyMessage = false);
    abstract function regrantDerecho($user, $userElement, $accionistaElement, $bodyMessage = false);
    abstract function regrantAv($user, $userElement, $accionistaElement, $bodyMessage = false);
    abstract function setAcitveLiveAccionista($accionista, $idLive, $active);
    abstract function setJuntaStateEnabled($junta, $state, $enabled);
    abstract function getJuntaStateEnabled($junta, $state, $toState);
    abstract function setAccionistaAcreditado($accionista, $acreditado);
    abstract function mailerAction($item, $state, $extra = '', $external = array());

}

?>
