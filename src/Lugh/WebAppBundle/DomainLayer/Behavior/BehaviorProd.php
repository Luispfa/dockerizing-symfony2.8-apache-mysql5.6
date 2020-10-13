<?php
namespace Lugh\WebAppBundle\DomainLayer\Behavior;
use Lugh\WebAppBundle\DomainLayer\State\Restrictions;
use Symfony\Component\Config\Definition\Exception\Exception;
use Lugh\WebAppBundle\DomainLayer\State\StateClass;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Lugh\WebAppBundle\Entity\App;
use Symfony\Component\HttpFoundation\Request;
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of LughBuilderProd
 *
 * @author a.navarro
 */
class BehaviorProd extends Behavior{
    
    public function selfAdhesion($item,$adhesion)
    {
        if (Restrictions::selfAdhesion($item, $adhesion))
        {
            throw new Exception("Adhesion self Item");
        }
        return $item;
    }
    
    public function multipleAdhesion($item,$adhesion)
    {
        if (Restrictions::multipleAdhesion($item, $adhesion))
        {
            throw new Exception("Other Item Adhesion with same Accionista");
        }
        return $item;
    }
    public function hasUserPermission($item, $action)
    {
        if (!Restrictions::hasUserPermission($item, $action))
        {
            throw new Exception("Item not permission to " . $action);
        }
        return $item;
    }
    public function hasUserPermissionItem($item, $action)
    {
        if (!Restrictions::hasUserOwnerItem($item, $action))
        {
            throw new Exception("Not permission to " . $action . " on Item");
        }
        return $item;
    }
    public function hasUserPermissionDocument($item, $action)
    {
        if (!Restrictions::hasUserOwnerDocument($item, $action))
        {
            throw new Exception("Not permission to " . $action . " on Document");
        }
        return $item;
    }
    public function hasUserPermissionWriteMessage($item, $action)
    {
        if (!Restrictions::hasUserPermissionWriteMessage($item, $action))
        {
            throw new Exception("Not permission to " . $action . " Message on Item");
        }
        return $item;
    }
    public function filterUserPermission($itemCollection, $action)
    {
        return Restrictions::filterUserPermission($itemCollection, $action);
    }
    
    public function filterUserPermissionAdhesion($itemCollection, $action)
    {
        return Restrictions::filterUserPermissionAdhesion($itemCollection, $action);
    }
    
    public function filterUserOwnerMessage($itemCollection, $action)
    {
        return Restrictions::filterUserOwnerMessage($itemCollection, $action);
    }
    public function filterUserOwnerItems($itemCollection, $action)
    {
        return Restrictions::filterUserOwnerItems($itemCollection, $action);
    }
    public function filterItemsInTime($itemCollection, $state = 'get')
    {
        return Restrictions::filterInTime($itemCollection, $state);
    }

    public function annulationPermitted($item, $lastItem) {
        if ($lastItem != null && !Restrictions::annulationPermitted($item, $lastItem))
        {
            throw new Exception("Not permission to Nullify");
        }
        return $item;
    }
    
    public function delegationNoDelegado($item) {
        if (Restrictions::delegationNoDelegado($item))
        {
            throw new Exception("Delegado is null");
        }
        return $item;
    }
    
    public function delegationNoVoteInTime() {
        if (!Restrictions::delegationVoteInTime())
        {
            throw new Exception("Vote Delegation is not permitted");
        }
        return true;
    }
    
    public function annulationVoto($accionista) {
        $builder    = $this->get('lugh.server')->getBuilder();
        $actions    = $accionista->getLastAccion();
        $action     = $actions != null ? $actions->first() : null;
        
        if ($action != null && $action::appClass === 'Voto')
        {
            $anulacion = $builder->buildAnulacion();
            $anulacion->setDateTime(new \DateTime());
            $anulacion->setAccionista($accionista);
        }
        
        return $accionista;
    }
    
    public function putPendingAppAv($item) {
        
        $accionista     = $item->getAccionista();
        $appAv          = $accionista->getAppbyDiscr(App::appAv);
        
        if ($item::appClass == 'Voto' && $appAv->getState() == StateClass::statePublic || $appAv->getState() == StateClass::statePending)
        {
            try {       
               $appAv->retorna();
            } catch (Exception $exc) {
                throw new Exception($exc->getMessage());
            }
        }
        
        return $item;
    }
    
    public function delegationCreate($delegation) 
    {
        $state = $delegation->getState();
        switch ($state) {
            case StateClass::statePending:
                $this->mailerAction($delegation,StateClass::actionPending);
                break;
            case StateClass::statePublic:
                if (count($delegation->getVotacion()) > 0) {
                    $this->mailerAction($delegation,StateClass::actionPublic, '', $this->getExternal($delegation->getVotoHtml(), 'DelegationVoteTag'));
                }
                else {
                    $this->mailerAction($delegation,StateClass::actionPublic);
                }
                break;

            default:
                break;
        } 
        return $delegation;
    }

    public function accionistaRegister($accionista) 
    {
        $check = $this->get('lugh.parameters')->getByKey('Config.accionista.check.fichero', 0);
        
        if($check == 1){
            //check
            $is_accionista = $this->checkAccionista($accionista->getValidJson());
            
            if($is_accionista){
                $state_global = StateClass::statePublic;
                $state = StateClass::statePublic;
            }
            else{
                $state_global = StateClass::statePending;
                $state = StateClass::statePending;
            }
        }
        else{
            //the normal thing
            $state_global = $this->get('lugh.parameters')->getByKey('Accionista.default.state', StateClass::statePending);
            $state = $this->get('lugh.parameters')->getByKey('Accionista_' .$this->userCert($accionista->getUser()) . '.default.state', $state_global);
        }
        
        $accionista_apps = $this->get('lugh.parameters')->getByKey('Accionista.default.apps', null);
        $this->setApss($accionista, $accionista_apps);
        
        $accionista->getItemAccionista()->setState($state);
        
        $this->mailerAction($accionista->getItemAccionista(),StateClass::actionCreate);
        $this->mailerAction($accionista->getItemAccionista(),StateClass::actionCreate,$this->userCert($accionista->getUser()));
        
        
        switch ($state) {
            case StateClass::statePending:
                $this->uniqueRole($accionista->getUser(), 'ROLE_USER_PEN');
                $accionista->getUser()->setEnabled(true);
                $this->mailerAction($accionista->getItemAccionista(),StateClass::actionPending);
                $this->mailerAction($accionista->getItemAccionista(),StateClass::actionPending,$this->userCert($accionista->getUser()));
                break;
            case StateClass::statePublic:
                $this->uniqueRole($accionista->getUser(), $this->userCert($accionista->getUser())); //ROLE_USER_FULL
                $accionista->getUser()->setEnabled(true);
                $token = new UsernamePasswordToken($accionista->getUser(), null, 'main', $accionista->getUser()->getRoles());
                $this->get('security.context')->setToken($token);
                $this->mailerAction($accionista->getItemAccionista(),StateClass::actionPublic);
                $this->mailerAction($accionista->getItemAccionista(),StateClass::actionPublic,$this->userCert($accionista->getUser()));
                break;
            case StateClass::stateRetornate:
                $this->uniqueRole($accionista->getUser(), 'ROLE_USER_RET');
                $accionista->getUser()->setEnabled(true);
                $this->mailerAction($accionista->getItemAccionista(),StateClass::actionRetornate);
                $this->mailerAction($accionista->getItemAccionista(),StateClass::actionRetornate,$this->userCert($accionista->getUser()));
                break;
            case StateClass::stateReject:
                $this->uniqueRole($accionista->getUser());
                $accionista->getUser()->setEnabled(false);
                $this->mailerAction($accionista->getItemAccionista(),StateClass::actionReject);
                $this->mailerAction($accionista->getItemAccionista(),StateClass::actionReject,$this->userCert($accionista->getUser()));
                break;

            default:
                break;
        } 
        return $accionista;
    }
    
    private function setApss($accionista, $accionista_apps)
    {/* @TODO: Apps */
        if ($accionista_apps != null)
        {
            $default_apps   = json_decode($accionista_apps);
            $apps           = $this->getApss();
            foreach ($default_apps as $app_name => $app_value) {
                //$accionista->getApps()->{'set' . ucfirst($app)}($value);
                $accionista->addApp($apps[$app_name]);
                $apps[$app_name]->preSave();
            }
        }
    }
    
    private function getApss()
    { /* @TODO: Apps */
        $builder = $this->get('lugh.server')->getBuilder();
        $apps = array(
            'voto'      =>  $builder->buildAppVoto(),
            'foro'      =>  $builder->buildAppForo(),
            'derecho'   =>  $builder->buildAppDerecho(),
            'av'        =>  $builder->buildAppAV(),
        );
        
        return $apps;
    }
    
    private function uniqueRole($user, $role = null)
    {
        $roles = $user->getRoles();
        foreach ($roles as $r) {
           $user->removeRole($r); 
        }
        if ($role != null)
        {
            $user->addRole($role);
        }
    }
    
    private function userCert($user)
    {
        if ($user->getCert() != null && $user->getCert() != '')
        {
            return 'ROLE_USER_CERT';
        }
        return 'ROLE_USER_FULL';
    }
    private function getExternal($comments, $postTag = 'Comment')
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $tag = 'user' . $postTag;
        foreach($user->getRoles() as $role){
            if($role == "ROLE_ADMIN" || $role == "ROLE_SUPER_ADMIN" || $role == "ROLE_CUSTOMER"){
                $tag = 'admin' . $postTag;
            }
        }
        
       return $comments == null || $comments == '' ? array() : array('tag' => $tag, 'vars' => array('%comments%' => $comments));
    }
    
    public function mailerAction($item, $state, $extra = '', $external = array(), $wf = true)
    {
        try {
            if ($wf) {
                $this->mailer->workflow($item, $state, $extra, array(), $external);
            }
            else {
                $this->mailer->formatandsend($item, $state, $extra, array(), $external);
                
            }
        } catch (Exception $exc) {
            throw new Exception($exc->getMessage());
        }
    }
    

    public function noContent($content) 
    {
        if ($content == '' || $content == null)
        {
            throw new Exception("No content");
        }
        return $content;
    }

    public function formatString($string) {
        return htmlentities($string);
    }

    public function maxDelegation($item) {
        if (Restrictions::delegationMax($item))
        {
            throw new Exception("Exceeded the limit to delegation");
        }
        return $item;
    }

    public function maxVotos($votacion, $voto) {
        if (Restrictions::votoMax($votacion, $voto))
        {
            throw new Exception("Exceeded the limit to vote");
        }
    }
    
    public function addVotos($accion, $votacion) {
        $storage = $this->get('lugh.server')->getStorage();
        $votoSerie = array();
        $votoPunto = array();
        foreach ($votacion as $voto) {
            $punto = $storage->getPunto($voto['punto_id']);
            $opcionVoto = $storage->getOpcionesVoto($voto['opcionVoto_id']);
            $votoLine = $this->choiceVoto($accion, $punto, $opcionVoto);
            if (isset($votoLine['serie']))
            {
                $votoGroup = isset($votoSerie[key($votoLine['serie'])]) ? $votoSerie[key($votoLine['serie'])] : array();
                $votoSerie[key($votoLine['serie'])][] = $this->duplicateVoto($votoGroup, $votoLine['serie'][key($votoLine['serie'])]);
            }
            if (isset($votoLine['punto']))
            {
                $votoPunto[key($votoLine['punto'])][] = $votoLine['punto'][key($votoLine['punto'])];
            }
        }
        foreach ($votoSerie as $key => $value) {
            $this->addVotoSerie($accion, $value);
        }
        foreach ($votoPunto as $key => $value) {
            $this->minVotos($value);
        }
        
    }
    
    private function choiceVoto($accion, $punto, $opcionVoto)
    {
        $builder = $this->get('lugh.server')->getBuilder();
        $voto = array();
        $votoPunto = $builder->buildVotoPunto();
        $votoPunto->setPunto($punto);
        $votoPunto->setOpcionVoto($opcionVoto);
        if ($punto->getTipoVoto()->getIsSerie()==false)
        {
            $accion->addVotacion($this->duplicateVoto($accion->getVotacion(),$votoPunto));
            $voto['punto'][md5($punto->getTipoVoto()->getTipo())] = $votoPunto;
        }
        else 
        {
            $voto['serie'][md5($punto->getTipoVoto()->getTipo())] = $votoPunto;
        }
        return $voto;
    }
    
    private function addVotoSerie($accion, $votacion)
    {
        $builder = $this->get('lugh.server')->getBuilder();
        $cipher = $this->get('lugh.server')->getCipher();
        $votoSerieTotal = array();
        $votoSerie = $builder->buildVotoSerie();
        $tipoVoto = $this->getTipoVoto($votacion);
        if (Restrictions::votoSerieMaxMin($votacion, $tipoVoto))
        {
            throw new Exception("Exceeded the limit to vote");
        }
        foreach ($votacion as $votoPunto) {
            $votoSerieTotal[] = array(
                'punto'         => $votoPunto->getPunto()->getId(), 
                'opcionvoto'    => $votoPunto->getOpcionVoto()->getId()
            );
        }
        $votoSerie->setTipoVoto($tipoVoto);
        $votoSerie->setVoto($cipher->encode($votoSerieTotal, $tipoVoto->getClaseDecrypt()));
        $votoSerie->setAlgorithm($cipher->getClass() . ' - ' . $tipoVoto->getClaseDecrypt());
        $accion->addVotacionSerie($votoSerie);
        
    }
    
    private function getTipoVoto($votacion)
    {
        $tiposVotos = array_map(function($element) {return $element->getPunto()->getTipoVoto();}, $votacion);
        $tiposVotoUnique = array_unique($tiposVotos, SORT_REGULAR);
        $tipoVoto = reset($tiposVotoUnique);
        if (count($tiposVotoUnique) > 1)
        {
            throw new Exception("Wrong type in VotoUnique");
        }
        return $tipoVoto;
    }
    
    private function minVotos($votacion) {
        $tipoVoto = $this->getTipoVoto($votacion);

        if (Restrictions::votoMin($votacion, $tipoVoto))
        {
            throw new Exception("Exceeded the limit to vote");
        }
    }
    
    private function duplicateVoto($votacion,$voto)
    {
        $votos = is_a($votacion, 'Doctrine\Common\Collections\ArrayCollection') ? $votacion->toArray() : $votacion == null ? array() : $votacion;
        
        if (Restrictions::duplicateVoto($votos,$voto))
        {
            throw new Exception("No permited multiple vote on one point");
        }
        return $voto;
    }


    public function hasSubpunto($punto) {
        if (Restrictions::hasSubpunto($punto))
        {
            throw new Exception("No permited to vote on this point (has subpoint)");
        }
        return $punto;
    }

    public function getVotoSerieDecrypt($votacionesSerie) {
        $votoReconstruct = array();
        foreach ($votacionesSerie as $votoserie) {
            $votoLine = array();
            $votoLine['tipoVoto'] = $votoserie->getTipoVoto();
            $votoLine['voto'] = $this->reconstructVotoSerie($votoserie);
            $votoReconstruct[] = $votoLine;
        }
        return $votoReconstruct;
    }
    
    private function reconstructVotoSerie($votoserie)
    {
        $storage = $this->get('lugh.server')->getStorage();
        $cipher = $this->get('lugh.server')->getCipher();
        $votoReconstruct = array();
        $votoDec = $cipher->decode($votoserie->getVoto(), $votoserie->getTipoVoto()->getClaseDecrypt());
        $votos = is_array($votoDec) ? $votoDec : array();
        foreach ($votos as $voto) {
            $votoLine['punto'] = $storage->getPunto($voto['punto']);
            $votoLine['opcion_voto'] = $storage->getOpcionesVoto($voto['opcionvoto']);
            $votoReconstruct[] = $votoLine;
        }
        return count($votoReconstruct) > 0 ? $votoReconstruct : array('error'=>'error al desencriptar');
        
    }

    public function restrictionOpcionVoto($punto, $opcionVoto) {
        if (!Restrictions::restrictionOpcionVoto($punto, $opcionVoto))
        {
            throw new Exception("OpcionVoto not accept in this Ponit");
        }
    }
    
    public function userExist($username) {
        $storage = $this->get('lugh.server')->getStorage();
        if ($storage->getUserByUserName($username) != null)
        {
            throw new Exception("Duplicate username");
        }
        return $username;
    }
    
    public function emailExist($email, $usermail) {
        $storage = $this->get('lugh.server')->getStorage();
        
        if ($email == $usermail)
        {
            return $email;
        }
        if ($storage->getUserByEMail($email) != null)
        {
            throw new Exception("Duplicate email");
        }
        return $email;
    }
    
    public function documentNumExist($documentNum, $newDocumentNum) {
        $storage = $this->get('lugh.server')->getStorage();
        
        if ($documentNum != $newDocumentNum && $storage->getAccionistaByDocument($newDocumentNum) != null)
        {
            throw new Exception("Duplicate document");
        }
        
        return $newDocumentNum;
    }
    
    public function getDefaultState($item)
    {
        return $this->get('lugh.parameters')->getByKey($item::nameClass . '.default.state', StateClass::statePending);
    }
    
    public function createAccionista($userElement, $accionistaElement, $cert = null)
    {
        //var_dump("BehaviorProd-> createAccionista 2");
        
        $builder = $this->get('lugh.server')->getBuilder();
        $storage = $this->get('lugh.server')->getStorage();
        $locale  = $this->get('lugh.translate.register')->getLocale(); 
        
        $username   =   $userElement['username'];
        $email      =   $userElement['email'];
        $token      =   $userElement['token'];
        $password   =   substr(md5(time()),0,8);

        $maxShares = $this->get('lugh.parameters')->getByKey('Config.accionista.accionesMin',null);
        
        try {
            $accionista = $builder->buildAccionista();
            $user       = $this->setAccionista($username, $password, $email);
            
            $user->setLang($locale);
            $user->setDateTime(new \DateTime());
            
            if ($cert != null) 
            {
                $user->setCert($cert['clientCert']);
                $accionista->setName($cert['name']);
                $accionista->setDocumentNum($cert['dni']);
            }
            else 
            {
                $accionista->setName($accionistaElement['name']);
                $accionista->setDocumentNum($accionistaElement['documentNum']);
            }
            
            $accionista->setRepresentedBy($accionistaElement['representedBy']);
            $accionista->setDocumentType ($accionistaElement['documentType' ]);
            $accionista->setTelephone($accionistaElement['telephone']);
            $accionista->setUser($user);     
            
            // Api Junta de Accionistas
            $check = $this->get('lugh.parameters')->getByKey('Config.accionista.check.fichero', 0);
            $checkShares = $this->get('lugh.parameters')->getByKey('juntas.api.sharesNum', 0);
            
            if($check == 1){
                
                $accionesElement = $this->checkAccionesAccionista($accionista->getDocumentNum());
                
                if ($accionesElement['valid'] === true){
                    $accionista->setReceivedJson($accionesElement['json']);
                    $accionista->setValidJson($accionesElement['validJson']);
                    if ($checkShares){
                        $accionistaElement['sharesNum'] = $accionesElement['sharesNum'];
                        // Si viene 0 porque no dispone acciones, ponemos 1 porque es el mínimo
                        // Se quedara en pendiente al realizar el check accionista
                        //if ($accionistaElement['sharesNum'] == 0) {
                        //    $accionistaElement['sharesNum'] = 1;
                        //}
                    }
                }
            }
                
            if(( $maxShares !== null && $accionistaElement['sharesNum'] >= $maxShares) ||
                ( $maxShares === null && $accionistaElement['sharesNum'] >= 1 )){
                    $accionista->setSharesNum($accionistaElement['sharesNum']);
            } else {
                throw new Exception('SharesNum Not Valid');
            }
			
            //itemaccionista: estado del accionista
            $itemAccionista = $builder->buildItemAccionista();
            $itemAccionista->setAutor($accionista); 
            $itemAccionista->setDateTime(new \DateTime());
            $storage->save($itemAccionista);
			
            $valid_json = $accionista->getValidJson();
            if (!(!isset($valid_json) || trim($valid_json) === ''))
            {
                $referencias = json_decode($accionista->getValidJson(), true);
                if (count($referencias) > 0)
                {
                    foreach($referencias as $referencia){

                        $registro = $builder->buildRegistro();
                        $registro->setAccionista($accionista);
                        $registro->setTitulares($referencia['Titulares']);
                        $registro->setNumero($referencia['Acciones']);
                        $registro->setReferencia($referencia['Referencia']);
                        $storage->save($registro);
                    }
                }
            }
            
            if($token != ''){
                $this->setDocumentsOwner($storage->getDocumentsByToken($token), $user);
            }
        } catch (Exception $exc) {
            throw new Exception($exc->getMessage());
        }
        return $accionista;
    }
    
    public function regrantAccionista($user, $userElement, $accionistaElement, $bodyMessage = false)
    {
        $storage    = $this->get('lugh.server')->getStorage();
        $builder    = $this->get('lugh.server')->getBuilder();
        $maxShares  = $this->get('lugh.parameters')->getByKey('Config.accionista.accionesMin',null);
        $token      = $userElement['token'];

        try {
            if(( $maxShares !== null && $accionistaElement['sharesNum'] < $maxShares) ||
               ( $maxShares === null && $accionistaElement['sharesNum'] < 1 )){
                $accionistaElement['sharesNum'] = '';
            }
            
            $accionista     = $this->addDatAccionista($user->getAccionista(), $accionistaElement);
            $itemAccionista = $accionista->getItemAccionista();
            
            $user->setEmail($userElement['email']);
            
            if($bodyMessage){
                $message = $builder->buildMessage();
                $message->setAutor($user);
                $message->setBody($bodyMessage);
                $message->setDateTime(new \DateTime());
                $itemAccionista->addMessage($message);
            }
            else
            {
                $bodyMessage = '';
            }

            $accionista->pendiente($bodyMessage);
            $storage->save($itemAccionista);
            
            if($token != ''){
                $this->setDocumentsOwner($storage->getDocumentsByToken($token), $user);
            }
        } catch (Exception $exc) {
            throw new Exception($exc->getMessage());
        }
        return $accionista;
    }
    
    public function regrantVoto($user, $userElement, $accionistaElement, $bodyMessage = false)
    {
        $storage    = $this->get('lugh.server')->getStorage();
        $builder    = $this->get('lugh.server')->getBuilder();
        $maxShares  = $this->get('lugh.parameters')->getByKey('Config.accionista.accionesMin',null);
        $token      = $userElement['token'];

        try {
            if(( $maxShares !== null && $accionistaElement['sharesNum'] < $maxShares) ||
               ( $maxShares === null && $accionistaElement['sharesNum'] < 1 )){
                $accionistaElement['sharesNum'] = '';
            }
            
            $accionista     = $this->addDatAccionista($user->getAccionista(), $accionistaElement);
            $itemAccionista = $accionista->getItemAccionista();
            
            $user->setEmail($userElement['email']);
            
            if($bodyMessage){
                $message = $builder->buildMessage();
                $message->setAutor($user);
                $message->setBody($bodyMessage);
                $message->setDateTime(new \DateTime());
                $accionista->getAppbyDiscr(0)->addMessage($message);
            }
            $accionista->getAppbyDiscr(0)->pendiente($bodyMessage !== false);
            $storage->save($itemAccionista);
            
            if($token != ''){
                $this->setDocumentsOwner($storage->getDocumentsByToken($token), $user);
            }
        } catch (Exception $exc) {
            throw new Exception($exc->getMessage());
        }
        return $accionista;
    }
    
    public function regrantForo($user, $userElement, $accionistaElement, $bodyMessage = false)
    {
        $storage    = $this->get('lugh.server')->getStorage();
        $builder    = $this->get('lugh.server')->getBuilder();
        $maxShares  = $this->get('lugh.parameters')->getByKey('Config.accionista.accionesMin',null);
        $token      = $userElement['token'];

        try {
            if(( $maxShares !== null && $accionistaElement['sharesNum'] < $maxShares) ||
               ( $maxShares === null && $accionistaElement['sharesNum'] < 1 )){
                $accionistaElement['sharesNum'] = '';
            }
            
            $accionista     = $this->addDatAccionista($user->getAccionista(), $accionistaElement);
            $itemAccionista = $accionista->getItemAccionista();
            
            $user->setEmail($userElement['email']);
            
            if($bodyMessage){
                $message = $builder->buildMessage();
                $message->setAutor($user);
                $message->setBody($bodyMessage);
                $message->setDateTime(new \DateTime());
                $accionista->getAppbyDiscr(1)->addMessage($message);
            }

            $accionista->getAppbyDiscr(1)->pendiente($bodyMessage !== false);
            $storage->save($itemAccionista);
            
            if($token != ''){
                $this->setDocumentsOwner($storage->getDocumentsByToken($token), $user);
            }
        } catch (Exception $exc) {
            throw new Exception($exc->getMessage());
        }
        return $accionista;
    }
    
    public function regrantDerecho($user, $userElement, $accionistaElement, $bodyMessage = false)
    {
        $storage    = $this->get('lugh.server')->getStorage();
        $builder    = $this->get('lugh.server')->getBuilder();
        $maxShares  = $this->get('lugh.parameters')->getByKey('Config.accionista.accionesMin',null);
        $token      = $userElement['token'];

        try {
            if(( $maxShares !== null && $accionistaElement['sharesNum'] < $maxShares) ||
               ( $maxShares === null && $accionistaElement['sharesNum'] < 1 )){
                $accionistaElement['sharesNum'] = '';
            }
            
            $accionista     = $this->addDatAccionista($user->getAccionista(), $accionistaElement);
            $itemAccionista = $accionista->getItemAccionista();
            
            $user->setEmail($userElement['email']);
            
            if($bodyMessage){
                $message = $builder->buildMessage();
                $message->setAutor($user);
                $message->setBody($bodyMessage);
                $message->setDateTime(new \DateTime());
                $accionista->getAppbyDiscr(2)->addMessage($message);
            }

            $accionista->getAppbyDiscr(2)->pendiente($bodyMessage !== false);
            $storage->save($itemAccionista);
            
            if($token != ''){
                $this->setDocumentsOwner($storage->getDocumentsByToken($token), $user);
            }
        } catch (Exception $exc) {
            throw new Exception($exc->getMessage());
        }
        return $accionista;
    }
    
    public function regrantAv($user, $userElement, $accionistaElement, $bodyMessage = false)
    {
        $storage    = $this->get('lugh.server')->getStorage();
        $builder    = $this->get('lugh.server')->getBuilder();
        $maxShares  = $this->get('lugh.parameters')->getByKey('Config.accionista.accionesMin',null);
        $token      = $userElement['token'];

        try {
            if(( $maxShares !== null && $accionistaElement['sharesNum'] < $maxShares) ||
               ( $maxShares === null && $accionistaElement['sharesNum'] < 1 )){
                $accionistaElement['sharesNum'] = '';
            }
            
            $accionista     = $this->addDatAccionista($user->getAccionista(), $accionistaElement);
            $itemAccionista = $accionista->getItemAccionista();
            
            $user->setEmail($userElement['email']);
            
            if($bodyMessage){
                $message = $builder->buildMessage();
                $message->setAutor($user);
                $message->setBody($bodyMessage);
                $message->setDateTime(new \DateTime());
                $accionista->getAppbyDiscr(3)->addMessage($message);
            }

            $accionista->getAppbyDiscr(3)->pendiente($bodyMessage !== false);
            $storage->save($itemAccionista);
            
            if($token != ''){
                $this->setDocumentsOwner($storage->getDocumentsByToken($token), $user);
            }
        } catch (Exception $exc) {
            throw new Exception($exc->getMessage());
        }
        return $accionista;
    }
    
    public function setAcitveLiveAccionista($accionista, $idLive, $active)
    {
        $appAv      = $accionista->getAppbyDiscr(3);
        $foundLive  = false;
        
        foreach ($appAv->getLives() as $avlive) {
            $live = $avlive->getLive();
            
            if ($live->getId() == $idLive) {
                $avlive->setEnabled($active);
                $foundLive = true;
            }
        }
        if ($foundLive == false)
        {
            throw new Exception('Live Not Found');
        }
        
        return $accionista;
    }
    
    public function setJuntaStateEnabled($junta, $state, $enabled)
    {
        $juntaWorkflow = $this->get('lugh.parameters')->getByKey('Config.junta.workFlow', 1);
        if ($juntaWorkflow == 1)
        {
            $junta->{'set'. ucfirst($state) . 'Enabled'}($enabled);
        }
        return $junta;
    }
    public function getJuntaStateEnabled($junta, $state, $toState)
    {
        $states = array(
            1 => 'Configuracion',
            2 => 'Convocatoria',
            3 => 'Prejunta',
            4 => 'Asistencia',
            5 => 'QuorumCerrado',
            6 => 'Votacion',
            7 => 'Finalizado'
        );
        
        //$juntaStates = $this->get('lugh.parameters')->getByKey($states[$state] . '.states.' . $states[$toState], null);
        $juntaStates = $this->get('lugh.parameters')->getByKey('Any.states.' . $states[$toState], null);
        
        if ($juntaStates !== null)
        {
            $juntaStatesJSON = json_decode($juntaStates, true);
            foreach ($juntaStatesJSON as $juntaState => $enabled) {
                $this->setJuntaStateEnabled($junta, $juntaState, $enabled);
            }
        }
        return isset($juntaStates) ? $junta : null;
    }
    
    private function addDatAccionista($accionista, $accionistaData)
    {
        foreach ($accionistaData as $key => $value) {
            if ($value != '')
            {
                $accionista->{'set'. ucfirst($key) }($value);
            }
        }
        return $accionista;
    }
    
    private function setAccionista($username, $password, $email)
    {
        $builder = $this->get('lugh.server')->getBuilder();
        try {
            $user = $builder->buildUser();
            $user->setUsername($username);
            $user->setPlainPassword($password);
            $user->setEmail($email);
        } catch (\Exception $ex) {
            throw new Exception($ex->getMessage());
        }
        
        return $user;   
    }
    
    private function setDocumentsOwner($documents, $user)
    {
        $storage = $this->get('lugh.server')->getStorage();
        try {
            foreach ($documents as $document) {
                $document->setOwner($user);
                $document->setOwnerbkp($user->getId());
                $document->setToken('');
                //StoreManager::StoreFile($document->getNombreInterno(), $user->getId());
                $storage->addStack($document);
            }
            $storage->saveStack();
        } catch (Exception $exc) {
            throw new Exception($exc->getMessage());
        }
        
    }

    public function setAccionistaAcreditado($accionista, $acreditado) {
        if ($acreditado != 1 && $acreditado != 0)
        {
            throw new Exception('Acreditado value not correct');
        }
        $accionista->setAcreditado($acreditado);
        $this->mailerAction($accionista,'acreditado');

        return $accionista;
    }
    
    private function checkAccionista($json){
        
	$storage = $this->get('lugh.server')->getStorage();
        
        $isAccionista = false;
        $lineas = json_decode($json, true);

        if(count($lineas) > 0){
            // Si la cantidad de lineas de valid_json existe y contiene almenos un elemento
            $isAccionista = true;
        }
        
        return $isAccionista;
    }
    
    private function checkAccionesAccionista($documentNum){
    
        $logger = $this->get('logger');
        $storage = $this->get('lugh.server')->getStorage();
        
        // Iniciamos los elementos a retornar
        $accionesElement = array(
            'valid' => false,
            'sharesNum' => 0,
            'json' => '',
            'validJson' => ''
        );
        
        // Obtenemos los datos de acceso a la api
        $api_address = $this->get('lugh.parameters')->getByKey('juntas.api.address', '');
        $api_user = $this->get('lugh.parameters')->getByKey('juntas.api.user', '');
        $api_pass = base64_decode($this->get('lugh.parameters')->getByKey('juntas.api.key', ''));
        $token = '';
        $code_response = 401;

        // creamos la llamada de autenticacion
        try{
                
            $headers = array(
                'Content-Type: application/json',
                'Content-Length: 0',
                'Authorization: Basic '. base64_encode("$api_user:$api_pass")
            );
                
            $ch = curl_init($api_address.'Authenticate');
            
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");  
            curl_setopt($ch, CURLOPT_POST, true);  
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_setopt($ch, CURLOPT_HEADERFUNCTION, function($ch, $header) use (&$token){
                $data = explode(':', $header, 2);
               if (strtolower(trim($data[0])) === 'token') {
                   $token = trim($data[1]);
               }
               return strlen($header);
            });
            $response  = curl_exec($ch);
            $code_response = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if($response === false || $code_response != 200)
            {
                $logger->info('Can not connect to API Juntas');
                $logger->info(curl_error($ch));
                return $accionesElement;
            } 
            curl_close($ch);
            
            // Autenticado y con el Token, realizamos la llamada
              
            $headers = array(
                'Content-Type: application/json',
                'Token: '. $token
            );
            $data = http_build_query(array(
                'id' => $documentNum
            ));

            $ch = curl_init($api_address.'holders?'.$data);
            
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT_MS, 0);

            $response  = curl_exec($ch);
            $code_response = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if($response === false || $code_response != 200)
            {
		$logger->info('Can not connect to API Juntas');
                $logger->info(curl_error($ch));
                return $accionesElement;
            }
            
            $json = json_decode($response, true);
            curl_close($ch);
            
            $accionesElement['json'] = $response;
            $totalAcciones = 0;
            
            $valid = array();
               
            foreach($json as $linea){
                // Sólo si la referencia no existe en la bbdd es válida
                // De lo contrario lo usa cualquier otro titular
                if($storage->getRegistroByReferencia($linea['Referencia']) == null){
                        
                    $totalAcciones = $totalAcciones + $linea['Acciones'];
                    $valid[] = array('Referencia' => $linea['Referencia'], 'Titulares' => $linea['Titulares'], 'Acciones' => $linea['Acciones']);
                        
                }
            }
            
            $accionesElement['sharesNum'] = $totalAcciones;
            $accionesElement['validJson'] = json_encode($valid, JSON_FORCE_OBJECT);
            $accionesElement['valid'] = true;
        }
        catch (Exception $ex)
        {
            $logger->info($ex->getMessage());
            $accionesElement['valid'] = false;
        }
           
        return $accionesElement;
    }
}

?>
