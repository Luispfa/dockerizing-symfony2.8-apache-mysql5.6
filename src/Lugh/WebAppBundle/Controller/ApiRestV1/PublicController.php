<?php

namespace Lugh\WebAppBundle\Controller\ApiRestV1;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use JMS\Serializer\SerializationContext;
use Lugh\WebAppBundle\Annotations\Permissions;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccountStatusException;
use Lugh\WebAppBundle\Lib\External\StoreManager;
use Lugh\WebAppBundle\Lib\External;
use Lugh\WebAppBundle\Lib\External\NifCifNiePredictor;

 /**
 * @RouteResource("public")
 */
class PublicController extends Controller {
    
    public function getParametrosAction($key) // GET Resource
    {
        $serializer = $this->container->get('jms_serializer');
        $param = $this->get('lugh.parameters')->getByKey($key, null);
        if($param === null) {
            return new Response($serializer->serialize(array('error' => "Parameter not found"), 'json'));
        }
        $mailer = $this->get('lugh.server')->getMailer();
        return new Response($serializer->serialize(array($key => $param), 'json', SerializationContext::create()->setGroups(array('Default'))));

    }// "get_resource"      [GET] /publics/{key}/parametros
    
    public function accionistaAction() // GET Resource
    {
        $serializer = $this->container->get('jms_serializer');
        $user = $this->getUser();
        try {
            $accionista = $user->getAccionista();
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize($accionista, 'json',SerializationContext::create()->setGroups(array('Default', 'Documents','ItemAccionista', 'Personal'))));

    }// "get_resource"      [GET] /publics/accionista
    
    public function postAccionistaAction() // Create Resource
    { 
        $serializer     = $this->container->get('jms_serializer');
        $behavior       = $this->get('lugh.server')->getBehavior();
        $request        = $this->get('request');       
        $userjson       = $request->get('user','');
        $accionistajson = $request->get('accionista','');
        $recaptcha  = $request->get('reCaptcha','');
        
        if($recaptcha != null && $recaptcha != ''){
        
            try {
                $response=file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".$this->container->getParameter('recaptcha_prKey')."&response=".$recaptcha."&remoteip=".$request->getClientIp());
                $this->requieredFields($request, array('Config.require.doca-certificate', 'Config.require.docb-certificate' ));
                $this->DocValid($accionistajson['documentType'], $accionistajson['documentNum']);
                
                $accionista = $behavior->createAccionista($userjson, $accionistajson);


            } catch (Exception $exc) {
                return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
            }
            return new Response($serializer->serialize(array('success' => true), 'json'));
        }
        else{
            return new Response($serializer->serialize(array('error' => 'reCaptcha'), 'json'));
        }
    }// "new_Accionistas"     [POST] /publics/accionistas
    
    public function postAccionistacertificateAction() // Create Resource
    { 
        $serializer     = $this->get('jms_serializer');
        $behavior       = $this->get('lugh.server')->getBehavior();
        $request        = $this->get('request');       
        $userjson       = $request->get('user','');
        $accionistajson = $request->get('accionista','');
        $cert           = $request->getSession()->get('cert');
        
        try {
            if ($cert == null || !isset($cert['status']) || $cert['status'] != 'VALID') throw new Exception('No Certificate Session');
            $userjson['username'] = str_replace(' ', '_', substr($cert['name'], 0, 10))  . '_' . $cert['dni'] . '_' . substr($cert['clientCert'], 0, 5);
            $this->requieredFields($request, array('Config.require.username', 'Config.require.doca-user', 'Config.require.docb-user' ));
            
            $accionista = $behavior->createAccionista($userjson, $accionistajson, $cert);
            
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize(array('success' => $accionista), 'json',SerializationContext::create()->setGroups(array('Default', 'Documents', 'ItemAccionista'))));
    }// "new_Accionistas"     [POST] /publics/accionistacertificates

    public function postToadminAction() // Create Mail
    {
        $serializer     = $this->container->get('jms_serializer');
        $mailer         = $this->get('lugh.server')->getMailer();
        $request        = $this->get('request');
        $recaptcha      = $request->get('reCaptcha','');
        $body           = nl2br(strip_tags(html_entity_decode($request->get('body','')),'<br><br/>'));
        $subject        = nl2br(strip_tags(htmlspecialchars('Solicitud de contacto - '.$request->get('name','').' : '.$request->get('subject','')),'<br><br/>'));
        $from           = nl2br(strip_tags(htmlspecialchars($request->get('from','')),'<br><br/>'));
        
        if($recaptcha != null && $recaptcha != ''){
        
            try {
                //$this->recaptchaCheck($recaptcha['challenge'], $recaptcha['response'], $request->getClientIp());
                $response=file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".$this->container->getParameter('recaptcha_prKey')."&response=".$recaptcha."&remoteip=".$request->getClientIp());
                
                $mailer->sendMailByRole(array('ROLE_CUSTOMER', 'ROLE_SUPER_ADMIN', 'ROLE_ADMIN'), $body, $subject, $from);
            } catch (Exception $exc) {
                return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
            }
            return new Response($serializer->serialize(array('success' => true), 'json'));
        }
        else{
            return new Response($serializer->serialize(array('error' => 'reCaptcha'), 'json'));
        }
    }// "new_mails"     [POST] /publics/toadmin
    
    //Accionista pasa de retornado a pendiente
    /**
     * @Permissions(perm={"ROLE_USER_RET"})
     */
    public function postRegrantAction()
    {
        $serializer = $this->container->get('jms_serializer');
        $behavior   = $this->get('lugh.server')->getBehavior();
        $request    = $this->get('request');

        try {
            $user = $this->getUser();
            
            $userData = $request->get('user',false);
            $userData['token'] = $request->get('token',false);

            $accionistaElement['name']          = $request->get('name','');
            $accionistaElement['representedBy'] = $request->get('represented_by','');
            $accionistaElement['documentNum']   = $request->get('document_num',  '');
            $accionistaElement['documentType' ] = $request->get('document_type', '');
            $accionistaElement['sharesNum']     = $request->get('shares_num',    '');
            $message                            = $request->get('message',false);

            $accionista = $behavior->regrantAccionista($user, $userData, $accionistaElement, $message);

        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize(array('success' => $accionista), 'json',SerializationContext::create()->setGroups(array('Default', 'Documents', 'ItemAccionista'))));
    } // "get_resource_comments"     [POST] /publics/regrants
    
    public function postDocumentAction()
    {
        $serializer = $this->container->get('jms_serializer');
        $builder = $this->get('lugh.server')->getBuilder();
        $storage = $this->get('lugh.server')->getStorage();
        $request = $this->get('request');
        //$mimes = ['application/x-dosexec','application/octet-stream','application/x-msdownload','application/x-ms-installer','application/x-elf','application/x-sh'];

        try {
            $uploadedFile = $request->files->get('file');
            $response = $this->get('validate.file')->isValid($uploadedFile);
            if ($response->getStatusCode() == 200) {
                $document = $builder->buildDocument();
                $document->setDateTime(new \DateTime());
                $document->setNombreExterno($uploadedFile->getClientOriginalName());
                $nombreInterno = uniqid(php_uname('n') . '.', true) . iconv("UTF-8", 'ASCII//TRANSLIT', str_replace(' ', '_', $uploadedFile->getClientOriginalName()));
                $document->setNombreInterno($nombreInterno);
                $document->setToken($request->get('token', ''));

                $storage->save($document);
                StoreManager::StoreGenericFile($nombreInterno, $uploadedFile);
            } else {
                return $response;
            }
        } catch (Exception $exc) {
            $response=new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
            $response->setStatusCode(400);
            return $response;
//            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        
        return new Response($serializer->serialize(array('success' => $document), 'json', SerializationContext::create()->setGroups(array('Default')))); 
            
    }// "new_Documents"     [POST] /publics/documents
    
    public function postForgotmailAction()
    {
        $serializer = $this->container->get('jms_serializer');
        $username = $this->container->get('request')->request->get('mail');

        /** @var $user UserInterface */
        $user = $this->container->get('fos_user.user_manager')->findUserByUsernameOrEmail($username);

        if (null === $user) {
            return new Response($serializer->serialize(array('success' => false), 'json'));
        }

        if ($user->isPasswordRequestNonExpired($this->container->getParameter('fos_user.resetting.token_ttl'))) {
            return new Response($serializer->serialize(array('error' => 'password already requested'), 'json'));
        }
        
        if ($user->getCert() != null) {
            return new Response($serializer->serialize(array('error' => 'User have Certificate'), 'json'));
        }

        if (null === $user->getConfirmationToken()) {
            /** @var $tokenGenerator \FOS\UserBundle\Util\TokenGeneratorInterface */
            $tokenGenerator = $this->container->get('fos_user.util.token_generator');
            $user->setConfirmationToken($tokenGenerator->generateToken());
        }

        $this->container->get('session')->set('fos_user_send_resetting_email/email', $this->getObfuscatedEmail($user));
        $this->container->get('fos_user.mailer')->sendResettingEmailMessage($user);
        $user->setPasswordRequestedAt(new \DateTime());
        $this->container->get('fos_user.user_manager')->updateUser($user);

        //return new RedirectResponse($this->container->get('router')->generate('fos_user_resetting_check_email'));
        return new Response($serializer->serialize(array('success' => true), 'json'));
            
    }// "new_Documents"     [POST] /publics/forgotmails
    
    public function putRemovedocumentAction($id)
    {
        $serializer = $this->container->get('jms_serializer');
        $storage = $this->get('lugh.server')->getStorage();
        
        try {
            $document = $storage->getDocument($id);
            $document->setToken('');
            $document->GetOwner() != null ? $document->setOwner(null) : null;
            $storage->save($document);
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize(array('success' => $document), 'json', SerializationContext::create()->setGroups(array('Default')))); 
            
    }// "put_Documents"     [POST] /publics/{id}/removedocument
    
    public function getRejectdelegationAction($id, $token)
    {
        
        $serializer = $this->container->get('jms_serializer');
        $storage = $this->get('lugh.server')->getStorage();
        try {       
           $delegacion = $storage->getDelegacionToken($id, $token);
           $delegacion->rechaza();
           $storage->save($delegacion, false);
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize(array('success' => true), 'json', SerializationContext::create()->setGroups(array('Default'))));   
    }// "get_resource"      [GET] /publics/{id}/rejectdelegations/{token}
    
    public function getAcceptdelegationAction($id, $token)
    {
        
        $serializer = $this->container->get('jms_serializer');
        $storage = $this->get('lugh.server')->getStorage();
        try {       
           $delegacion = $storage->getDelegacionToken($id, $token);
           $delegacion->publica();
           $storage->save($delegacion, false);
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize(array('success' => true), 'json', SerializationContext::create()->setGroups(array('Default'))));   
    }// "get_resource"      [GET] /publics/{id}/rejectdelegations/{token}
    
    
    public function postLiveAction()
    {
        $serializer     = $this->container->get('jms_serializer');
        $builder        = $this->get('lugh.server')->getBuilder();
        $storage        = $this->get('lugh.server')->getStorage();
        $request        = $this->get('request');
        //die(var_dump($request));
        //$params         = json_decode($request->request, true);

        try {
            if ($request->get('event_id','') === '')
            {
                throw new Exception('No event_id');
            }

            $event_id       = $request->get('event_id','');
            $session_id     = $request->get('session_id','');
            $session_name   = $request->get('session_name','');
            $start_datetime = $request->get('session_start_datetime','');
            $finish_datetime= $request->get('session_finish_datetime','');
            $app_version    = $request->get('app_version','');
            $live_status    = $request->get('session_live_status','');
            $od_status      = $request->get('session_od_status',array())[0]['status'];
            
            $live = $storage->getLiveByEventAndSession($event_id, $session_id);

            if ($live == null)
            {
                $live = $builder->buildLive();
                $live->setEventId($event_id);
                $live->setSessionId($session_id);
                $live->setUrl(External\WebCastApi::GetUrl($event_id, $session_id));
            }
            $live->setSessionName($session_name);
            $live->setSessionStartDatetime(new \DateTime($start_datetime));
            $live->setSessionFinishDatetime(new \DateTime($finish_datetime));
            $live->setAppVersion($app_version);
            $live->setSessionLiveStatus($live_status);
            $live->setSessionOdStatus($od_status);
            $storage->save($live, false);
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize(array('success'), 'json')); 
            
    }// "new_Documents"     [POST] /publics/lives
    
    
    private function requieredFields($request, $noRequire = array())
    {
        $requires = $this->get('lugh.parameters')->getRequireParms();
        foreach ($requires as $require) {
            if (in_array($require['key_param'], $noRequire) == false && $this->getRequestElemnetsRecursive($request->request->all(), $require) == false)
            {
                throw new Exception("Require " . $require['key_param']);
            }
        }
        return true;
    }
    
    private function getRequestElemnetsRecursive($element, $required, $ret = false)
    {
        $storage = $this->get('lugh.server')->getStorage();
        foreach ($element as $key => $value) {
            if (is_array($value)) {
                if ($this->getRequestElemnetsRecursive($value, $required, $ret)) {
                    $ret = true;
                }
            }
            else if ($required['value_param'] == "0")
            {
                return true;
            }
            
            else if ($required['value_param'] == "1" &&
                     $this->getAdjustRequiereElements($required['key_param']) == '.token' &&
                     $key == 'token' &&
                     count($storage->getDocumentsByToken($value)) == 0 
                    )
            {
                return false;
            }
            else if ($required['value_param'] == "1" && 
                    strpos($this->getAdjustRequiereElements($required['key_param']), '.' . $key) !== false &&
                    $value != "") 
            {
                return true;
            }
        }
        return $ret;
        
    }
    private function getAdjustRequiereElements($element)
    {
        $adjustElements = array(
            'Config.require.tipo-persona'       => '.personaType',
            'Config.require.doca'               => '.token',
            'Config.require.docb'               => '.token',
            'Config.require.doca-user'          => '.token',
            'Config.require.doca-certificate'   => '.token',
            'Config.require.docb-user'          => '.token',
            'Config.require.docb-certificate'   => '.token',
            'Config.require.tipo-doc'           => '.documentType',
            'Config.require.numero-doc'         => '.documentNum'
        );
        return isset($adjustElements[$element]) ? $adjustElements[$element] : $element;
    }
    private function DocValid($tipo , $numDoc)
    {
        if ($numDoc != "")
        {
            $type = NifCifNiePredictor::predict($numDoc);
            if ($type != $tipo)
            {
                throw new Exception("NumeroDoc predict as " . $type . " but " . $tipo . " recived" );
            }
        }
        return true;
        
    }
    
    private function getAccionista($id)
    {
        $storage = $this->get('lugh.server')->getStorage();
        $serializer = $this->container->get('jms_serializer');
        try {
           $accionista = $storage->getAccionista($id); 
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return $accionista;
    }
    
    private function recaptchaCheck($challenge, $response, $ip)
    { 
        $resp = External\RecaptchaCheck::recaptcha_check_answer(
                    $this->container->getParameter('recaptcha_prKey'),      //prKey
                    $ip,                                                    //Remote IP
                    $challenge,                                             //Challenge
                    $response                                               //Response
                );
        
        if (!$resp->is_valid)
        {
            throw new Exception('reCaptcha');
        }
        return $resp->is_valid;
    }

    
    
    private function getObfuscatedEmail($user)
    {
        $email = $user->getEmail();
        if (false !== $pos = strpos($email, '@')) {
            $email = '...' . substr($email, $pos);
        }

        return $email;
    }

}