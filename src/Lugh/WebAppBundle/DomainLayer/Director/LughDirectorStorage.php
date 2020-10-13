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
class LughDirectorStorage extends LughDirector {
     
    public function resetStack()
    {
        $this->builder->resetStack();
    }
    
    public function addStack($element)
    {
        $this->builder->addStack($element);
    }
    
    public function save($item, $restrictionUser = true)
    {
        $this->builder->save($item, $restrictionUser);
    }
    
    public function saveAttachment($item, $restrictionUser = true, $attachments = array())
    {
        $this->builder->saveAttachment($item, $restrictionUser, $attachments);
    }
    
    public function saveStack()
    {
        $this->builder->saveStack();
    }
    public function getOffer($id)
    {
        return $this->builder->getOffer($id);
    }
    public function getInitiative($id)
    {
        return $this->builder->getInitiative($id);
    }
    public function getRequest($id)
    {
        return $this->builder->getRequest($id);
    }
    public function getProposal($id)
    {
        return $this->builder->getProposal($id);
    }
    public function getThread($id)
    {
        return $this->builder->getThread($id);
    }
    public function getQuestion($id)
    {
        return $this->builder->getQuestion($id);
    }
    public function getDesertion($id)
    {
        return $this->builder->getDesertion($id);
    }
    public function getItemAccionista($id)
    {
        return $this->builder->getItemAccionista($id);
    }
    public function getAdhesionOffer($id)
    {
        return $this->builder->getAdhesionOffer($id);
    }
    public function getAdhesionInitiative($id)
    {
        return $this->builder->getAdhesionInitiative($id);
    }
    public function getAdhesionRequest($id)
    {
        return $this->builder->getAdhesionRequest($id);
    }
    public function getAdhesionProposal($id)
    {
        return $this->builder->getAdhesionProposal($id);
    }
    public function getAccionista($id)
    {
        return $this->builder->getAccionista($id);
    }
    public function getAccionistaByDocument($documentNum)
    {
        return $this->builder->getAccionistaByDocument($documentNum);
    }
    public function getUser($id)
    {
        return $this->builder->getUser($id);
    }
    public function getUserByUserName($username) {
        return $this->builder->getUserByUserName($username);
    }
    public function getUserByEMail($email) {
        return $this->builder->getUserByEMail($email);
    }
    public function getUserByCert($cert) 
    {
        return $this->builder->getUserByCert($cert);
    }
    public function getOffers()
    {
        return $this->builder->getOffers();
    }
    public function getInitiatives()
    {
        return $this->builder->getInitiatives();
    }
    public function getRequests()
    {
        return $this->builder->getRequests();
    }
    public function getProposals()
    {
        return $this->builder->getProposals();
    }
    public function getThreads()
    {
        return $this->builder->getThreads();
    }
    public function getQuestions()
    {
        return $this->builder->getQuestions();
    }
    public function getDesertions()
    {
        return $this->builder->getDesertions();
    }
    public function getItemAccionistas()
    {
        return $this->builder->getItemAccionistas();
    }
    public function getAdhesionOffers()
    {
        return $this->builder->getAdhesionOffers();
    }
    public function getAdhesionInitiatives()
    {
        return $this->builder->getAdhesionInitiatives();
    }
    public function getAdhesionRequests()
    {
        return $this->builder->getAdhesionRequests();
    }
    public function getAdhesionProposals()
    {
        return $this->builder->getAdhesionProposals();
    }
    public function getAccionistas()
    {
        return $this->builder->getAccionistas();
    }
    
    public function getOffersByState($state)
    {        
        return $this->builder->getOffersByState($state);    
        
    }
    public function getInitiativesByState($state)
    {        
        return $this->builder->getInitiativesByState($state);    
        
    }
    public function getRequestsByState($state) {
        return $this->builder->getRequestsByState($state);
    }

    public function getProposalsByState($state) {
        return $this->builder->getProposalsByState($state);
    }

    public function getThreadsByState($state) {
        return $this->builder->getThreadsByState($state);
    }
    
    public function getQuestionsByState($state) {
        return $this->builder->getQuestionsByState($state);
    }

    public function getItemAccionistasByState($state) {
        return $this->builder->getItemAccionistasByState($state);
    }

    public function getAdhesionOffersByState($state) {
        return $this->builder->getAdhesionOffersByState($state);
    }

    public function getAdhesionInitiativesByState($state) {
        return $this->builder->getAdhesionInitiativesByState($state);
    }

    public function getAdhesionRequestsByState($state) {
        return $this->builder->getAdhesionRequestsByState($state);
    }

    public function getAdhesionProposalsByState($state) {
        return $this->builder->getAdhesionProposalsByState($state);
    }
    
    public function getPunto($id) {
        return $this->builder->getPunto($id);
    }
    
    public function getPuntos() {
        return $this->builder->getPuntos();
    }
    
    public function getAdminPuntos() {
        return $this->builder->getAdminPuntos();
    }
    
    public function getCobsaPuntos() {
        return $this->builder->getCobsaPuntos();
    }
   
    public function getOpcionesVoto($id) {
        return $this->builder->getOpcionesVoto($id);
    }

    public function getOpcionesVotos() {
        return $this->builder->getOpcionesVotos();
    }
    
    public function getVoto($id) {
        return $this->builder->getVoto($id);
    }

    public function getVotos() {
        return $this->builder->getVotos();
    }
    public function getAccion($id) {
        return $this->builder->getAccion($id);
    }

    public function getAccions() {
        return $this->builder->getAccions();
    }

    public function getAccionsNoFile() {
        return $this->builder->getAccionsNoFile();
    }
    
    public function getDelegacion($id) {
        return $this->builder->getDelegacion($id);
    }
    
    public function getDelegacionToken($id, $token) {
        return $this->builder->getDelegacionToken($id, $token);
    }

    public function getDelegaciones() {
        return $this->builder->getDelegaciones();
    }

    public function getDelegado($id) {
        return $this->builder->getDelegado($id);
    }

    public function getDelegadoByDocument($doc) {
        return $this->builder->getDelegadoByDocument($doc);
    }

    public function getDelegados() {
        return $this->builder->getDelegados();
    }

    public function getDirectors() {
        return $this->builder->getDirectors();
    }
    
    public function getSecretarys() {
        return $this->builder->getSecretarys();
    }
    
    public function getAnulacion($id) {
        return $this->builder->getAnulacion($id);
    }

    public function getAnulaciones() {
        return $this->builder->getAnulaciones();
    }
    
    public function getLasAnulaciones() {
        return $this->builder->getLastAnulaciones();
    }
    
    public function getAnulacionAv($id) {
        return $this->builder->getAnulacionAv($id);
    }

    public function getAnulacionesAv() {
        return $this->builder->getAnulacionesAv();
    }
    
    public function getLasAnulacionesAv() {
        return $this->builder->getLastAnulacionesAv();
    }
    
    public function getDocumentsByToken($token) {
        return $this->builder->getDocumentsByToken($token);
    }
    
    public function getDocument($id) {
        return $this->builder->getDocument($id);
    }
    
    public function getParametros() {
        return $this->builder->getParametros();
    }
    
    public function getParametro($key) {
        return $this->builder->getParametro($key);
    }
    
    public function getCommuniques() {
        return $this->builder->getCommuniques();
    }
    
    public function getCommunique($key) {
        return $this->builder->getCommunique($key);
    }
    
    public function getLogMails(){
        return $this->builder->getLogMails();
    }
    
    public function getLogMail($id){
        return $this->builder->getLogMail($id);
    }
    
    public function getLogMailsUser($user){
        return $this->builder->getLogMailsUser($user);
    }
    
    public function getLogMailsUserWorkflow($user, $wf, $direction='in'){
        return $this->builder->getLogMailsUserWorkflow($user, $wf, $direction);
    }
    
    public function getMessage($id){
        return $this->builder->getMessage($id);
    }
    
    public function getTipoVotobyTipoSerie($tipo, $serie) {
        return $this->builder->getTipoVotobyTipoSerie($tipo, $serie);
    }
    
    public function getGrupoOpcionesVoto($id) {
        return $this->builder->getGrupoOpcionesVoto($id);
    }

    public function getApps() {
        return $this->builder->getApps();
    }
    
    public function getAccionRechazada($id) {
        return $this->builder->getAccionRechazada($id);
    }

    public function getAccionesRechazadas() {
        return $this->builder->getAccionesRechazadas();
    }
    
    public function getLastVotos() {
        return $this->builder->getLastVotos();
    }
    
    public function getLastDelegaciones() {
        return $this->builder->getLastDelegaciones();
    }
    
    public function getLastAccions() {
        return $this->builder->getLastAccions();
    }
    
    public function getLastAccionsVe() {
        return $this->builder->getLastAccionsVe();
    }
    
    public function getLastAccionsAv() {
        return $this->builder->getLastAccionsAv();
    }
    
    public function getTipoPuntos($tipo) {
        return $this->builder->getTipoPuntos($tipo);
    }
    
    public function getAdminTipoVotos() {
        return $this->builder->getAdminTipoVotos();
    }

    public function getTipoVoto($id) {
        return $this->builder->getTipoVoto($id);
    }

    public function getTipoVotos() {
        return $this->builder->getTipoVotos();
    }
    
    public function getAbsAdicionals() {
        return $this->builder->getAbsAdicionals();
    }
    
    public function getTipoAbsAdicional($tipoVoto) {
        return $this->builder->getTipoAbsAdicional($tipoVoto);
    }

    public function getAbsAdicional($id) {
        return $this->builder->getAbsAdicional($id);
    }
    
    public function getAv($id) {
        return $this->builder->getAv($id);
    }

    public function getAvs() {
        return $this->builder->getAvs();
    }

    public function getLastAvs() {
        return $this->builder->getLastAvs();
    }
    
    public function getLivesByAccionista($accionista) {
        return $this->builder->getLivesByAccionista($accionista);
    }
    
    public function getAccionistasRequestAV() {
        return $this->builder->getAccionistasRequestAV();
    }
    
    public function getAccionistasAcreditados() {
        return $this->builder->getAccionistasAcreditados();
    }
    
    public function getLive($id) {
        return $this->builder->getLive($id);
    }
    
    public function getLiveByEventAndSession($event_id, $session_id) {
        return $this->builder->getLiveByEventAndSession($event_id, $session_id);
    }
    
    public function getJunta() {
        return $this->builder->getJunta();
    }
    
    public function getJuntas() {
        return $this->builder->getJuntas();
    }
    
    public function getRegistroByReferencia($referencia) {
        return $this->builder->getRegistroByReferencia($referencia);
    }
    
    public function getLastAccesos() {
        return $this->builder->getLastAccesos();
    }
    
    public function getLastAccesosAv() {
        return $this->builder->getLastAccesosAv();
    }
    
    public function getAcceso($id) {
        return $this->builder->getAcceso($id);
    }

    public function getAccesos() {
        return $this->builder->getAccesos();
    }
}

?>
