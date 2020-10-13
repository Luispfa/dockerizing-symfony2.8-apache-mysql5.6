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
class LughDirectorBuilder extends LughDirector {
    public function buildOffer() {
        return $this->builder->buildOffer();
    }
    
    public function buildInitiative() {
        return $this->builder->buildInitiative();
    }
    public function buildAdhesionInitiative() {
        return $this->builder->buildAdhesionInitiative();
    }

    public function buildAdhesionOffer() {
        return $this->builder->buildAdhesionOffer();
    }

    public function buildAdhesionProposal() {
        return $this->builder->buildAdhesionProposal();
    }

    public function buildAdhesionRequest() {
        return $this->builder->buildAdhesionRequest();
    }

    public function buildItemAccionista() {
        return $this->builder->buildItemAccionista();
    }

    public function buildProposal() {
        return $this->builder->buildProposal();
    }

    public function buildRequest() {
        return $this->builder->buildRequest();
    }

    public function buildThread() {
        return $this->builder->buildThread();
    }
    
    public function buildQuestion() {
        return $this->builder->buildQuestion();
    }   

    public function buildDesertion() {
        return $this->builder->buildDesertion();
    }    
    
    public function buildAccionista() {
        return $this->builder->buildAccionista();
    }
    
    public function buildRegistro() {
        return $this->builder->buildRegistro();
    }
    
    public function buildMessage() {
        return $this->builder->buildMessage();
    }
    
    public function buildPunto() {
        return $this->builder->buildPunto();
    }
    
    public function buildOpcionesVoto() {
        return $this->builder->buildOpcionesVoto();
    }
    
    public function buildVoto() {
        return $this->builder->buildVoto();
    }
    
    public function buildVotoPunto() {
        return $this->builder->buildVotoPunto();
    }
    
    public function buildDelegacion() {
        return $this->builder->buildDelegacion();
    }
    
    public function buildDelegado() {
        return $this->builder->buildDelegado();
    }
    
    public function buildAnulacion() {
        return $this->builder->buildAnulacion();
    }
    
    public function buildAnulacionAv() {
        return $this->builder->buildAnulacionAv();
    }
    
    public function buildAccionRechazada() {
        return $this->builder->buildAccionRechazada();
    }
    
    public function buildUser() {
        return $this->builder->buildUser();
    }
    
    public function buildDocument() {
        return $this->builder->buildDocument();
    }
    
    public function buildLogMail() {
        return $this->builder->buildLogMail();
    }
    
    public function buildParametro() {
        return $this->builder->buildParametro();
    }
    
    public function buildCommunique() {
        return $this->builder->buildCommunique();
    }
    
    public function buildVotoSerie() {
        return $this->builder->buildVotoSerie();
    }
    
    public function buildTipoVoto() {
        return $this->builder->buildTipoVoto();
    }
    
    public function buildAppVoto() {
         return $this->builder->buildAppVoto();
    }
    
    public function buildAppForo() {
         return $this->builder->buildAppForo();
    }
    
    public function buildAppDerecho() {
         return $this->builder->buildAppDerecho();
    }
    
    public function buildAppAV() {
         return $this->builder->buildAppAV();
    }
    
    public function buildAccesoVoto() {
         return $this->builder->buildAccesoVoto();
    }
    
    public function buildAccesoForo() {
         return $this->builder->buildAccesoForo();
    }
    
    public function buildAccesoDerecho() {
         return $this->builder->buildAccesoDerecho();
    }
    
    public function buildAccesoAV() {
         return $this->builder->buildAccesoAV();
    }

    public function buildVotoAbsAdicional() {
        return $this->builder->buildVotoAbsAdicional();
    }
    
    public function buildAV() {
        return $this->builder->buildAV();
    }
    
    public function buildLive() {
        return $this->builder->buildLive();
    }
    

}

?>
