<?php
namespace Lugh\WebAppBundle\DomainLayer\LughBuilder;
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
abstract class LughBuilder extends Builder{
    
    protected $mailer;
    
    
    public function __construct($container) {
        parent::__construct($container);
        $this->mailer = $this->get('mailer.builder');
    }
    
    abstract function buildOffer();
    abstract function buildInitiative();
    abstract function buildRequest();
    abstract function buildProposal();
    abstract function buildThread();
    abstract function buildQuestion();
    abstract function buildDesertion();
    abstract function buildItemAccionista();
    abstract function buildAdhesionOffer();
    abstract function buildAdhesionInitiative();
    abstract function buildAdhesionRequest();
    abstract function buildAdhesionProposal();
    abstract function buildAccionista();
    abstract function buildRegistro();
    abstract function buildMessage();
    abstract function buildPunto();
    abstract function buildOpcionesVoto();
    abstract function buildVoto();
    abstract function buildAv();
    abstract function buildVotoPunto();
    abstract function buildDelegacion();
    abstract function buildDelegado();
    abstract function buildAnulacion();
    abstract function buildAnulacionAv();
    abstract function buildAccionRechazada();
    abstract function buildUser();
    abstract function buildDocument();
    abstract function buildLogMail();
    abstract function buildParametro();
    abstract function buildCommunique();
    abstract function buildVotoSerie();
    abstract function buildTipoVoto();
    abstract function buildAppVoto();
    abstract function buildAppForo();
    abstract function buildAppDerecho();
    abstract function buildAppAV();
    abstract function buildLive();
    abstract function buildAccesoVoto();
    abstract function buildAccesoForo();
    abstract function buildAccesoDerecho();
    abstract function buildAccesoAV();
}

?>
