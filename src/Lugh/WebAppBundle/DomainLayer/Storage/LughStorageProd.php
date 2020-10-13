<?php
namespace Lugh\WebAppBundle\DomainLayer\Storage;
use Lugh\WebAppBundle\DomainLayer\State\Restrictions;
use Symfony\Component\Config\Definition\Exception\Exception;
use Doctrine\DBAL\DBALException;
use Lugh\WebAppBundle\DomainLayer\State\StateClass;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Storage
 *
 * @author a.navarro
 */
class LughStorageProd extends LughStorage {
    
    public function resetStack()
    {
        $this->stack = array();
    }
    
    public function addStack($element)
    {
        $this->stack[] = $this->restrictionItem($element,StateClass::actionStore);
    }
    
    public function save($item, $restrictionUser = true)
    {
        try {
            $this->preSave($item);
            $itemSave = $restrictionUser ? $this->actionCreate($this->restrictionItem($item,StateClass::actionStore)) : $this->actionCreate($item);
            $this->setDateTime($item);
            $this->em->persist($itemSave);
            $this->em->flush();
            $this->mailer->workflow($item,StateClass::actionStore);
        } catch (DBALException $ex) {
            throw new Exception($ex->getMessage());
        }
        catch (Exception $exc) {
            throw new Exception($exc->getMessage());
        }
    }
    
    public function saveAttachment($item, $restrictionUser = true, $attachments = array())
    {
        try {
            $this->preSave($item);
            $itemSave = $restrictionUser ? $this->actionCreateAttachment($this->restrictionItem($item,StateClass::actionStore), $attachments) : $this->actionCreateAttachment($item, $attachments);
            $this->setDateTime($item);
            $this->em->persist($itemSave);
            $this->em->flush();
            $this->mailer->workflow($item,StateClass::actionStore, '', array(), array(), $attachments);
        } catch (DBALException $ex) {
            throw new Exception($ex->getMessage());
        }
        catch (Exception $exc) {
            throw new Exception($exc->getMessage());
        }
    }
    
    public function saveStack()
    {
        try {
            foreach ($this->stack as $stackElement) {
                $this->preSave($stackElement);
                $this->setDateTime($stackElement);
                $this->em->persist($this->actionCreate($this->restrictionItem($stackElement,StateClass::actionStore)));
                $this->mailer->workflow($stackElement,StateClass::actionStore);
            }
            
            $this->em->flush();
            $this->stack = array();
        } catch (DBALException $ex) {
            throw new Exception($ex->getMessage());
        }
        catch (Exception $exc) {
            throw new Exception($exc->getMessage());
        }
    }
    
    protected function actionCreate($item)
    {
        if ($item->getId()== null)
        {
            self::setDateTimeCreate($item);
            $this->mailer->workflow($item,StateClass::actionCreate);
        }
        return $item;
    }
    
    protected function actionCreateAttachment($item, $attachments)
    {
        if ($item->getId()== null)
        {
            self::setDateTimeCreate($item);
            $this->mailer->workflow($item,StateClass::actionCreate, '', array(), array(), $attachments);
        }
        return $item;
    }
    
    protected function preSave($item)
    {
        if (method_exists($item, 'preSave'))
        {
            call_user_func(array($item, 'preSave'));
        }
    }
    
    protected function setDateTime($item)
    {
        if (method_exists($item, 'setDateTime'))
        {
            call_user_func(array($item, 'setDateTime'),new \DateTime());
        }
    }

    protected function setDateTimeCreate($item)
    {
        if (method_exists($item, 'setDateTimeCreate'))
        {
            call_user_func(array($item, 'setDateTimeCreate'),new \DateTime());
        }
    }
    
    protected function getItem($item, $id)
    {
        $itemClass = 'Lugh\WebAppBundle\Entity\\' . $item;
        $ret = $this->em->getRepository($itemClass)->find($id);
        if ($ret == null)
        {
            throw new Exception("No Item");
        }
        return $ret;
    }
    protected function getItemByField($item, $fields, $throw = true)
    {
        $itemClass = 'Lugh\WebAppBundle\Entity\\' . $item;
        $ret = $this->em->getRepository($itemClass)->findOneBy($fields);
        if ($ret == null && $throw)
        {
            throw new Exception("No Item");
        }
        return $ret;
    }
    protected function getAllItemsByField($item, $fields = array(), $order = array())
    {
        $itemClass = 'Lugh\WebAppBundle\Entity\\' . $item;
        $ret =  $this->em->getRepository($itemClass)->findBy($fields, $order);
        if ($ret == null)
        {
            //throw new Exception("No Item");
        }
        return $ret;
    }
    protected function getAllItems($item)
    {
        $itemClass = 'Lugh\WebAppBundle\Entity\\' . $item;
        $ret =  $this->em->getRepository($itemClass)->findAll();
        if ($ret == null)
        {
            //throw new Exception("No Item");
        }
        return $ret;
    }
    protected function getAllItemsByFieldGroup($item, $fields = array(), $order = array(), $group = array())
    {
        $itemClass = 'Lugh\WebAppBundle\Entity\\' . $item;
        $ret =  $this->em->getRepository($itemClass)->findByGroup($fields, $order, $group);
        if ($ret == null)
        {
            //throw new Exception("No Item");
        }
        return $ret;
    }
    protected function getByNot($item, $fields = array(), $order = null)
    {
        $itemClass = 'Lugh\WebAppBundle\Entity\\' . $item;
        $ret =  $this->em->getRepository($itemClass)->findByNot($fields, $order);
        if ($ret == null)
        {
            //throw new Exception("No Item");
        }
        return $ret;
    }
    
    protected function getByYesNot($item, $fieldsYes = array(), $fieldsNo = array(), $order = null)
    {
        $itemClass = 'Lugh\WebAppBundle\Entity\\' . $item;
        $ret =  $this->em->getRepository($itemClass)->findByYesNot($fieldsYes, $fieldsNo, $order);
        if ($ret == null)
        {
            //throw new Exception("No Item");
        }
        return $ret;
    }
    protected function exceptionTime($item, $action = StateClass::actionGet)
    {

        if (!Restrictions::inTime($item, $action))
        {
            throw new Exception("Item not in time to " . $action);
        }
        return $item;
        
    }
    protected function filterTime($itemCollection, $action = StateClass::actionGet)
    {
        return Restrictions::filterInTime($itemCollection, $action);
    }
    protected function filterUserPermission($itemCollection, $action)
    {
        return Restrictions::filterUserPermission($itemCollection, $action);
    }
    
    protected function hasUserPermission($item, $action)
    {
        if (Restrictions::hasUserPermission($item, $action) == false)
        {
            throw new Exception("Item not permission to " . $action);
        }
    }
    protected function hasQuestionPermission($item, $action)
    {
        if (Restrictions::hasQuestionPermission($item, $action) == false)
        {
            throw new Exception("Question not permission to " . $action);
        }
    }
    protected function hasDesertionPermission($item, $action)
    {
        if (Restrictions::hasDesertionPermission($item, $action) == false)
        {
            throw new Exception("Desertion not permission to " . $action);
        }
    }
    
    protected function hasAvPermission($item, $action)
    {
        if (Restrictions::hasAvPermission($item, $action) == false)
        {
            throw new Exception("Vote not permission to " . $action);
        }
    }

    
    public function filterUserPermissionAdhesion($itemCollection, $action)
    {
        return Restrictions::filterUserPermissionAdhesion($itemCollection, $action);
    }
    
    protected function hasUserPermissionAdhesion($item, $action)
    {
        if (!Restrictions::hasUserPermissionAdhesion($item, $action))
        {
            throw new Exception("Adhesion not permission to " . $action);
        }
        
    }
    
    protected function hasUserPermissionEnable($item, $action)
    {
        if (!Restrictions::hasUserPermissionEnable($item, $action))
        {
            throw new Exception("Communique not permission to " . $action);
        }
        
    }
    protected function filterUserPermissionEnable($itemCollection, $action = StateClass::actionGet)
    {
        return Restrictions::filterUserPermissionEnable($itemCollection, $action);
    }
    
    protected function hasUserPermissionReadMail($item, $action = StateClass::actionGet)
    {
        if (!Restrictions::hasUserPermissionReadMail($item, $action))
        {
            throw new Exception("Mail not permission to " . $action);
        }
        return $item;
        
    }
    protected function DelegationToken($item, $token)
    {
        if ($item->getDelegado()->getToken() != $token)
        {
            throw new Exception("No Item");
        }
        return $item;
        
    }
    protected function filterUserPermissionReadMail($itemCollection, $action = StateClass::actionGet)
    {
        return Restrictions::filterUserReadMail($itemCollection, $action);
    }
    
    protected function restrictionItem($item, $action = StateClass::actionGet)
    {
        $this->exceptionTime($item, $action);
        $this->hasUserPermission($item, $action);
        $this->hasQuestionPermission($item, $action);
        $this->hasAvPermission($item, $action);
        $this->hasDesertionPermission($item, $action);
        return $item;
    }
    
    protected function restrictionCollectItem($itemCollection, $action = StateClass::actionGet)
    {
        $itemCollection = $this->filterTime($itemCollection, $action);
        $itemCollection = $this->filterUserPermission($itemCollection, $action);
        return $itemCollection;
    }
    
    protected function restrictionAdhesionItem($item, $action = StateClass::actionGet)
    {
        $this->exceptionTime($item, $action);
        $this->hasUserPermissionAdhesion($item, $action);
        return $item;
    }
    
    protected function restrictionAdhesionCollectItem($itemCollection, $action = StateClass::actionGet)
    {
        $itemCollection = $this->filterTime($itemCollection, $action);
        $itemCollection = $this->filterUserPermissionAdhesion($itemCollection, $action);
        return $itemCollection;
    }
    
    protected function restrictionEnableItem($item, $action = StateClass::actionGet)
    {
        $this->exceptionTime($item, $action);
        $this->hasUserPermissionEnable($item, $action);
        return $item;
    }
    
    protected function restrictionEnableCollectItem($itemCollection, $action = StateClass::actionGet)
    {
        $itemCollection = $this->filterTime($itemCollection, $action);
        $itemCollection = $this->filterUserPermissionEnable($itemCollection, $action);
        return $itemCollection;
    }
    
    protected function filterSubPuntos($puntos) {
        return Restrictions::filterPuntos($puntos);
    }
    
    protected function filterSubPuntosRetirados($puntos)
    {
        return Restrictions::filterSubPuntosRetirados($puntos);
    }

    public function getAdhesionInitiative($id) {
        return $this->restrictionAdhesionItem($this->getItem('AdhesionInitiative', $id));
    }

    public function getAdhesionOffer($id) {
        return $this->restrictionAdhesionItem($this->getItem('AdhesionOffer', $id));
    }

    public function getAdhesionProposal($id) {
        return $this->restrictionAdhesionItem($this->getItem('AdhesionProposal', $id));
    }

    public function getAdhesionRequest($id) {
        return $this->restrictionAdhesionItem($this->getItem('AdhesionRequest', $id));
    }

    public function getInitiative($id) {
        return $this->restrictionItem($this->getItem('Initiative', $id));
    }

    public function getItemAccionista($id) {
        return $this->restrictionItem($this->getItem('ItemAccionista', $id));
    }

    public function getOffer($id) {
        return $this->restrictionItem($this->getItem('Offer', $id));
    }

    public function getProposal($id) {
        return $this->restrictionItem($this->getItem('Proposal', $id));
    }

    public function getRequest($id) {
        return $this->restrictionItem($this->getItem('Request', $id));
    }

    public function getThread($id) {
        return $this->restrictionItem($this->getItem('Thread', $id));
    }
    
    public function getQuestion($id) {
        return $this->restrictionItem($this->getItem('Question', $id));
    }
    
    public function getDesertion($id) {
        return $this->restrictionItem($this->getItem('Desertion', $id));
    }

    public function getAccionista($id) {
        return $this->restrictionItem($this->getItem('Accionista', $id));
    }
    public function getAccionistaByDocument($documentNum) {
        return $this->getItemByField('Accionista', array('documentNum' => $documentNum), false);
    }
    public function getUser($id) {
        return $this->getItem('User', $id);
    }
    public function getUserByUserName($username) {
        return $this->getItemByField('User', array('username' => $username), false);
    }
    public function getUserByEMail($email) {
        return $this->getItemByField('User', array('email' => $email), false);
    }
    public function getUserByCert($cert) {
        return $this->getItemByField('User', array('cert' => $cert));
    }

    public function getAdhesionInitiatives() {
        return $this->restrictionAdhesionCollectItem($this->getAllItems('AdhesionInitiative'));
    }

    public function getAdhesionOffers() {
        return $this->restrictionAdhesionCollectItem($this->getAllItems('AdhesionOffer'));
    }

    public function getAdhesionProposals() {
        return $this->restrictionAdhesionCollectItem($this->getAllItems('AdhesionProposal'));
    }

    public function getAdhesionRequests() {
        return $this->restrictionAdhesionCollectItem($this->getAllItems('AdhesionRequest'));
    }

    public function getInitiatives() {
        return $this->restrictionCollectItem($this->getAllItems('Initiative'));
    }

    public function getItemAccionistas() {
        return $this->restrictionCollectItem($this->getAllItems('ItemAccionista'));
    }

    public function getOffers() {
        return $this->restrictionCollectItem($this->getAllItems('Offer'));
    }

    public function getProposals() {
        return $this->restrictionCollectItem($this->getAllItems('Proposal'));
    }

    public function getRequests() {
        return $this->restrictionCollectItem($this->getAllItems('Request'));
    }

    public function getThreads() {
        return $this->restrictionCollectItem($this->getAllItems('Thread'));
    }
    
    public function getQuestions() {
        return $this->restrictionCollectItem($this->getAllItems('Question'));
    }
    
    public function getDesertions() {
        return $this->restrictionCollectItem($this->getAllItems('Desertion'));
    }

    public function getAccionistas() {
        return $this->restrictionCollectItem($this->getAllItems('Accionista'));
    }

    public function getAdhesionInitiativesByState($state) {
        return $this->restrictionCollectItem($this->getAllItemsByField('AdhesionInitiative', array('state' => $state)));
    }

    public function getAdhesionOffersByState($state) {
        return $this->restrictionCollectItem($this->getAllItemsByField('AdhesionOffer', array('state' => $state)));
    }

    public function getAdhesionProposalsByState($state) {
        return $this->restrictionCollectItem($this->getAllItemsByField('AdhesionProposal', array('state' => $state)));
    }

    public function getAdhesionRequestsByState($state) {
        return $this->restrictionCollectItem($this->getAllItemsByField('AdhesionRequest', array('state' => $state)));
    }

    public function getInitiativesByState($state) {
        return $this->restrictionCollectItem($this->getAllItemsByField('Initiative', array('state' => $state)));
    }

    public function getItemAccionistasByState($state) {
        return $this->restrictionCollectItem($this->getAllItemsByField('ItemAccionista', array('state' => $state)));
    }

    public function getOffersByState($state) {
        return $this->restrictionCollectItem($this->getAllItemsByField('Offer', array('state' => $state)));
    }

    public function getProposalsByState($state) {
        return $this->restrictionCollectItem($this->getAllItemsByField('Proposal', array('state' => $state)));
    }

    public function getRequestsByState($state) {
        return $this->restrictionCollectItem($this->getAllItemsByField('Request', array('state' => $state)));
    }

    public function getThreadsByState($state) {
        return $this->restrictionCollectItem($this->getAllItemsByField('Thread', array('state' => $state)));
    }
    
    public function getQuestionsByState($state) {
        return $this->restrictionCollectItem($this->getAllItemsByField('Question', array('state' => $state)));
    }
    
    public function getPunto($id) {
        return $this->getItem('PuntoDia', $id);
    }
    
    public function getPuntos() {
        return $this->filterSubPuntosRetirados($this->filterSubPuntos($this->getByNot('PuntoDia', array('retirado' => true), array('orden' => 'asc'))));
    }
    
    public function getAdminPuntos() {
        return $this->filterSubPuntos($this->getAllItemsByField('PuntoDia', array(), array('orden'=>'asc')));
    }
    
    public function getCobsaPuntos() {
        return $this->getAllItemsByField('PuntoDia', array(), array('orden'=>'asc'));
    }

    public function getOpcionesVoto($id) {
        return $this->getItem('OpcionesVoto', $id);
    }

    public function getOpcionesVotos() {
        return $this->getAllItems('OpcionesVoto');
    }

    public function getVoto($id) {
        return $this->restrictionItem($this->getItem('Voto', $id));
    }

    public function getVotos() {
        return $this->restrictionCollectItem($this->getAllItems('Voto'));
    }

    public function getAccion($id) {
        return $this->restrictionItem($this->getItem('Accion', $id));
    }

    public function getAccions() {
        return $this->restrictionCollectItem($this->getAllItems('Accion'));
    }

    public function getAccionsNoFile() {
        return $this->restrictionCollectItem($this->getAllItemsByField('Accion', array('movFileTagged' => null)));
    }

    public function getDelegacion($id) {
        return $this->restrictionItem($this->getItem('Delegacion', $id));
    }
    
    public function getDelegacionToken($id, $token) {
        return $this->DelegationToken($this->exceptionTime($this->getItem('Delegacion', $id)), $token);
    }

    public function getDelegaciones() {
        return $this->restrictionCollectItem($this->getAllItems('Delegacion'));
    }

    public function getDelegado($id) {
        return $this->exceptionTime($this->getItem('Delegado', $id));
    }

    public function getDelegadoByDocument($doc) {
        return $this->exceptionTime($this->getItemByField('Delegado', array('documentNum' => $doc)));
    }

    public function getDelegados() {
        return $this->filterTime($this->getAllItems('Delegado'));
    }

    public function getDirectors() {
        return $this->filterTime($this->getAllItemsByField('Delegado', array('isConseller' => true)));
    }
    
    public function getSecretarys() {
        return $this->filterTime($this->getAllItemsByField('Delegado', array('isSecretary' => true)));
    }
    
    public function getAnulacion($id) {
        return $this->exceptionTime($this->getItem('Anulacion', $id));
    }

    public function getAnulaciones() {
        return $this->filterTime($this->getAllItems('Anulacion'));
    }
    
    public function getLastAnulaciones() {
        return $this->restrictionCollectItem($this->getAllItemsByField('Anulacion', array('accionPosterior' => null)));
    }
    
    public function getAccionRechazada($id) {
        return $this->exceptionTime($this->getItem('AccionRechazada', $id));
    }

    public function getAccionesRechazadas() {
        return $this->filterTime($this->getAllItems('AccionRechazada'));
    }

    public function getDocumentsByToken($token) {
        return $this->filterTime($this->getAllItemsByField('Document', array('token' => $token)));
    }

    public function getDocument($id) {
        return $this->exceptionTime($this->getItem('Document', $id));
    }

    public function getParametros() {
        return $this->getAllItems('Parametros');
    }

    public function getParametro($id) {
        return $this->getItem('Parametros', $id);
    }

    public function getCommunique($id) {
        return $this->restrictionEnableItem($this->getItem('Communique', $id));
    }

    public function getCommuniques() {
        return $this->filterUserPermissionEnable($this->getAllItems('Communique'));
    }

    public function getLogMail($id) {
        return $this->hasUserPermissionReadMail($this->getItem('LogMail', $id));
    }

    public function getLogMails() {
        return $this->filterUserPermissionReadMail($this->getAllItems('LogMail'));
    }

    public function getLogMailsUser($user) {
        return $this->filterUserPermissionReadMail($this->getAllItemsByField('LogMail', array('userdest' => $user), array('dateTime'=>'desc')));
    }

    public function getLogMailsUserWorkflow($user, $wf, $direction='in') {
        switch ($direction) {
            case 'in':
                $dir = 'userdest';
                break;
            case 'out':
                $dir = 'userfrom';
                break;
            default:
                $dir = 'userdest';
                break;
        }
        return $this->filterUserPermissionReadMail($this->getAllItemsByFieldGroup(
                'LogMail', 
                array($dir => $user->getId(), 'wf' => $wf ? 1 : 0, 'hide' => 0), 
                array('dateTime'=>'desc'), 
                array('uniqueid')));
    }
    
    public function getMessage($id) {
        return $this->restrictionItem($this->getItem('Message', $id));
    }

    public function getTipoVotobyTipoSerie($tipo, $serie) {
        return $this->getItemByField('TipoVoto', array('tipo' => $tipo, 'is_serie' => $serie), false);
    }
    
    public function getGrupoOpcionesVoto($id) {
        return $this->getItem('GrupoOpcionesVoto', $id);
    }

    public function getApps() {
        $ret = $this->getAllItems('Apps');
        if (count($ret) < 1)
        {
            throw new Exception("No apps");
        }
        return $ret[0];
    }

    public function getLastVotos() {
        return $this->restrictionCollectItem($this->getAllItemsByField('Voto', array('accionPosterior' => null)));
    }
    
    public function getLastDelegaciones() {
        return $this->restrictionCollectItem($this->getAllItemsByField('Delegacion', array('accionPosterior' => null)));
    }

    public function getLastAccions() {
        return $this->restrictionCollectItem($this->getAllItemsByField('Accion', array('accionPosterior' => null)));
    }
    
    public function getLastAccionsAv() {
        //$discrArray = [2,100];
        $actions = $this->getAllItemsByField('Accion', array('accionPosterior' => null));
        $items = array();
        foreach($actions as $action){
            switch($action::nameClass){
                case 'AnulacionAv':
                case 'Av':
                    $items[] = $action;
            }
        }
        return $this->restrictionCollectItem($items);
    }
    
    public function getLastAccionsVe() {
        //$discrArray = [0,1,99];
        $actions = $this->getAllItemsByField('Accion', array('accionPosterior' => null));
        $items = array();
        foreach($actions as $action){
            switch($action::nameClass){
                case 'Anulacion':
                case 'Voto':
                case 'Delegacion':
                    $items[] = $action;
            }
        }
        return $this->restrictionCollectItem($items);
    }

    public function getTipoPuntos($tipo) {
        return $this->filterSubPuntosRetirados($this->filterSubPuntos($this->getByYESNot('PuntoDia', array('tipoVoto' => $tipo), array('retirado' => true), array('orden' => 'asc'))));
    }

    public function getAdminTipoVotos() {
        return $this->getAllItems('TipoVoto');
    }

    public function getTipoVoto($id) {
        return $this->getItem('TipoVoto', $id);
    }

    public function getTipoVotos() {
        $puntos = $this->getAllItemsByFieldGroup('PuntoDia', array(), array(), array('tipoVoto'));
        return array_filter($this->getAllItems('TipoVoto'),function($element)use($puntos){
            return in_array($element, array_map(function($punto){
                return $punto->getTipoVoto();           
            }, $puntos)); 
        });
    }
    
    public function getAbsAdicionals() {
        return $this->getAllItems('AbsAdicional');
    }

    public function getTipoAbsAdicional($tipoVoto) {
        return $this->getAllItemsByField('AbsAdicional', array('tipoVoto' => $tipoVoto));
    }

    public function getAbsAdicional($id) {
        return $this->getItem('AbsAdicional', $id);
    }

    public function getAv($id) {
        return $this->restrictionItem($this->getItem('Av', $id));
    }

    public function getAvs() {
        return $this->restrictionCollectItem($this->getAllItems('Av'));
    }

    public function getLastAvs() {
        return $this->restrictionCollectItem($this->getAllItemsByField('Av', array('accionPosterior' => null)));
    }

    public function getAnulacionAv($id) {
        return $this->exceptionTime($this->getItem('AnulacionAv', $id));
    }

    public function getAnulacionesAv() {
        return $this->filterTime($this->getAllItems('AnulacionAv'));
    }
    
    public function getLastAnulacionesAv() {
        return $this->restrictionCollectItem($this->getAllItemsByField('AnulacionAv', array('accionPosterior' => null)));
    }
    
    public function getLivesByAccionista($accionista) {
        
        $allLives = $this->container->get('lugh.parameters')->getByKey('Av.live.allLives', '1');
        
        if ($allLives !== '0')
        {
            $lives = $this->getAllItems('Live');
        }
        else
        {
            $lives = $accionista->getAppbyDiscr(3)->getLives() == null ? null : $accionista->getAppbyDiscr(3)->getLives()->toArray();
        }
        
        return $lives;
    }
    public function getAccionistasRequestAV() {
        return array_filter($this->restrictionCollectItem($this->getAllItems('Accionista')),function($accionista){
            return $accionista->getAppbyDiscr(3)->getState() == StateClass::statePublic;
        });
    }
    
    public function getAccionistasAcreditados() {
        return $this->restrictionCollectItem($this->getAllItemsByField('Accionista', array('acreditado' => true)));
    }

    public function getLive($id) {
        return $this->restrictionItem($this->getItem('Live', $id));
    }

    public function getLiveByEventAndSession($event_id, $session_id) {
        return $this->getItemByField('Live', array('eventId' => $event_id, 'sessionId' => $session_id), false);
    }
    
    public function getJunta() {
        $junta = $this->getAllItems('Junta');
        if ($junta == null) {
            throw new \Exception("No Item Junta");
        }
        return reset($junta);
    }
    public function getJuntas() {
        $junta = $this->getAllItems('Junta');
        return reset($junta);
    }
    
    public function getRegistroByReferencia($referencia) {
        return $this->getItemByField('Registro', array('referencia' => $referencia), false);
    }
    
    public function getAcceso($id) {
        return $this->restrictionItem($this->getItem('Acceso', $id));
    }

    public function getAccesos() {
        return $this->restrictionCollectItem($this->getAllItems('Acceso'));
    }
    
    public function getLastAccesos() {
        return $this->restrictionCollectItem($this->getAllItemsByField('Acceso', array('accesoPosterior' => null)));
    }
    
    public function getLastAccesosAv() {
        return $this->restrictionCollectItem($this->getAllItemsByField('AccesoAV', array('accesoPosterior' => null)));
    }

}