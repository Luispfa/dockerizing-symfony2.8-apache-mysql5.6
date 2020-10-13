<?php
namespace Lugh\WebAppBundle\DomainLayer\LughBuilder;
use Lugh\WebAppBundle\DomainLayer\Builder\Builder;
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
use Lugh\WebAppBundle\Entity\Question;
use Lugh\WebAppBundle\Entity\Accionista;
use Lugh\WebAppBundle\Entity\Message;
use Lugh\WebAppBundle\Entity\PuntoDia;
use Lugh\WebAppBundle\Entity\OpcionesVoto;
use Lugh\WebAppBundle\Entity\VotoPunto;
use Lugh\WebAppBundle\Entity\Voto;
use Lugh\WebAppBundle\Entity\Delegacion;
use Lugh\WebAppBundle\Entity\Delegado;
use Lugh\WebAppBundle\Entity\User;
use Lugh\WebAppBundle\Entity\Document;
use Lugh\WebAppBundle\Entity\LogMail;
use Lugh\WebAppBundle\Entity\Parametros;
use Lugh\WebAppBundle\Entity\Communique;
use Lugh\WebAppBundle\Entity\VotoSerie;
use Lugh\WebAppBundle\Entity\AppVoto;
use Lugh\WebAppBundle\Entity\AppForo;
use Lugh\WebAppBundle\Entity\AppDerecho;
use Lugh\WebAppBundle\Entity\AppAV;
use Lugh\WebAppBundle\Entity\Av;
use Lugh\WebAppBundle\Entity\AnulacionAv;
use Lugh\WebAppBundle\Entity\Live;



/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of LughBuilderProd
 *
 * @author a.navarro
 */
class LughBuilderTest extends LughBuilder{
    
    public function buildOffer() {
        return new Offer();
    }

    public function buildInitiative() {
        return new Initiative();
    }

    public function buildAdhesionInitiative() {
        return new AdhesionInitiative();
    }

    public function buildAdhesionOffer() {
        return new AdhesionOffer();
    }

    public function buildAdhesionProposal() {
        return new AdhesionProposal();
    }

    public function buildAdhesionRequest() {
        return new AdhesionRequest();
    }

    public function buildItemAccionista() {
        return new ItemAccionista();
    }

    public function buildProposal() {
        return new Proposal();
    }

    public function buildRequest() {
        return new Request();
    }

    public function buildThread() {
        return new Thread();
    }
    
    public function buildQuestion() {
        return new Question();
    }
    
    public function buildDesertion() {
        return new Desertion();
    }
    
    public function buildAccionista() {
        new Accionista();
    }

    public function buildMessage() {
        new Message();
    }
    
    public function buildPunto() {
        return new PuntoDia();
    }
    
    public function buildOpcionesVoto() {
        return new OpcionesVoto(); 
    }
    
    public function buildVoto() {
        return new Voto();
    }

    public function buildVotoPunto() {
        return new VotoPunto();
    }
    
    public function buildDelegacion() {
        return new Delegacion();
    }
    
    public function buildDelegado() {
        return new Delegado();
    }
    
    public function buildAnulacion() {
        return new Anulacion();
    }
    
    public function buildAnulacionAv() {
        return new AnulacionAv();
    }
    
    public function buildAccionRechazada() {
        return new AccionRechazada();
    }

    public function buildUser() {
        return new User();
    }

    public function buildDocument() {
        return new Document();
    }
    public function buildLogMail() {
        return new LogMail();
    }
    public function buildParametro() {
        return new Parametros();
    }

    public function buildCommunique() {
        return new Communique();
    }

    public function buildVotoSerie() {
        return new VotoSerie();
    }
    
    public function buildTipoVoto() {
        return new TipoVoto();
    }
    
    public function buildAppVoto() {
        return new AppVoto();
    }
    
    public function buildAppForo() {
        return new AppForo();
    }
    
    public function buildAppDerecho() {
        return new AppDerecho();
    }
    
    public function buildAppAV() {
        return new AppAV();
    }
    
    public function buildAV() {
        return new Av();
    }
    
    public function buildLive() {
        return new Live();
    }

}

?>


