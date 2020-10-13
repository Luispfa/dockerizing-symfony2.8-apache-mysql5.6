<?php
namespace Lugh\WebAppBundle\DomainLayer\Storage;
use Lugh\WebAppBundle\DomainLayer\Builder\Builder;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Storage
 *
 * @author a.navarro
 */
abstract class LughStorage extends Builder{
    
    protected $stack = array();
    
    protected $em;
    
    protected $mailer;
    
    public function __construct($container) {
        parent::__construct($container);
        $this->em = $this->get('doctrine')->getManager();
        $this->mailer = $this->get('mailer.builder');
    }
    
    abstract function getOffer($id);
    abstract function getOffers();
    abstract function getInitiative($id);
    abstract function getInitiatives();
    abstract function getRequest($id);
    abstract function getRequests();
    abstract function getProposal($id);
    abstract function getProposals();
    abstract function getThread($id);
    abstract function getThreads();
    abstract function getQuestion($id);
    abstract function getQuestions();
    abstract function getDesertion($id);
    abstract function getDesertions();
    abstract function getItemAccionista($id);
    abstract function getItemAccionistas();
    abstract function getAdhesionOffer($id);
    abstract function getAdhesionOffers();
    abstract function getAdhesionInitiative($id);
    abstract function getAdhesionInitiatives();
    abstract function getAdhesionRequest($id);
    abstract function getAdhesionRequests();
    abstract function getAdhesionProposal($id);
    abstract function getAdhesionProposals();
    abstract function getAccionista($id);
    abstract function getAccionistaByDocument($documentNum);
    abstract function getAccionistas();
    abstract function getUser($id);
    abstract function getUserByUserName($username);
    abstract function getUserByEMail($email);
    abstract function getUserByCert($cert);
    abstract function getOffersByState($state);
    abstract function getInitiativesByState($state);
    abstract function getRequestsByState($state);
    abstract function getProposalsByState($state);
    abstract function getThreadsByState($state);
    abstract function getQuestionsByState($state);
    abstract function getItemAccionistasByState($state);
    abstract function getAdhesionOffersByState($state);
    abstract function getAdhesionInitiativesByState($state);
    abstract function getAdhesionRequestsByState($state);
    abstract function getAdhesionProposalsByState($state);
    abstract function getPunto($id);
    abstract function getPuntos();
    abstract function getAdminPuntos();
    abstract function getCobsaPuntos();
    abstract function getTipoPuntos($tipo);
    abstract function getOpcionesVoto($id);
    abstract function getOpcionesVotos();
    abstract function getVoto($id);
    abstract function getVotos();
    abstract function getLastVotos();
    abstract function getAv($id);
    abstract function getAvs();
    abstract function getLastAvs();
    abstract function getAccion($id);
    abstract function getAccions();
    abstract function getAccionsNoFile();
    abstract function getLastAccions();
    abstract function getLastAccionsAv();
    abstract function getLastAccionsVe();
    abstract function getDelegacion($id);
    abstract function getDelegacionToken($id, $token);
    abstract function getDelegaciones();
    abstract function getLastDelegaciones();
    abstract function getDelegado($id);
    abstract function getDelegadoByDocument($doc);
    abstract function getDelegados();
    abstract function getDirectors();
    abstract function getSecretarys();
    abstract function getAnulacion($id);
    abstract function getAnulaciones();
    abstract function getLastAnulaciones();
    abstract function getAnulacionAv($id);
    abstract function getAnulacionesAv();
    abstract function getLastAnulacionesAv();
    abstract function getAccionRechazada($id);
    abstract function getAccionesRechazadas();
    abstract function getDocumentsByToken($token);
    abstract function getDocument($id);
    abstract function getParametros();
    abstract function getParametro($id);
    abstract function getCommunique($id);
    abstract function getCommuniques();
    abstract function getLogMails();
    abstract function getLogMail($id);
    abstract function getLogMailsUser($user);
    abstract function getLogMailsUserWorkflow($user, $wf, $direction='in');
    abstract function getMessage($id);
    abstract function getTipoVotobyTipoSerie($tipo, $serie);
    abstract function getGrupoOpcionesVoto($id);    
    abstract function getApps();
    abstract function getAdminTipoVotos();
    abstract function getTipoVoto($id);
    abstract function getTipoVotos();
    abstract function getLivesByAccionista($accionista);
    abstract function getAccionistasRequestAV();
    abstract function getAccionistasAcreditados();
    abstract function getLive($id);
    abstract function getLiveByEventAndSession($event_id, $session_id);
    abstract function getJunta();
    abstract function getJuntas();
    abstract function getRegistroByReferencia($referencia);
    abstract function getAccesos();
    abstract function getAcceso($id);
    abstract function getLastAccesos();
    abstract function getLastAccesosAv();
}