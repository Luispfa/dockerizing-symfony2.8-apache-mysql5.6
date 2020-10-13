<?php
namespace Lugh\WebAppBundle\DomainLayer\Mail;
use Lugh\WebAppBundle\DomainLayer\Builder\Builder;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpFoundation\Request;
use Lugh\WebAppBundle\DomainLayer\State\StateClass;
use Symfony\Component\Config\Definition\Exception\Exception;
use Lugh\WebAppBundle\Lib\External\StoreManager;
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Storage
 *
 * @author a.navarro
 */
abstract class LughMail extends Builder{

    protected $stack = array();

    protected $em;

    protected $workflowOff;

    private $lang = 'es_es';

    abstract public function workflow($item, $state, $extra = '');

    public function __construct($container) {
        parent::__construct($container);

        $this->em           = $this->get('doctrine')->getManager();
        $this->mailer       = $this->get('lugh.mailer');
        $this->lang         = $this->get('lugh.translate.register')->getLocale();
        $this->workflowOff  = false;
    }

    public function setWorkflowOff($switch = true)
    {
        $this->workflowOff = $switch;
    }

    public function formatandsend($item, $state, $extra = '', $attributs = array(), $external = array(), $attachments = array())
    {
        if ($this->workflowOff == false)
        {
            return $this->formatandsendFunc($item, $state, $extra, $attributs, $external, $attachments);
        }
        $this->workflowOff = true;
        return true;
    }


    public function sendMailByAddress($to, $body, $subject, $from = null) {
        $storage = $this->get('lugh.Storage');
        $userfrom = $this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED') ? $this->get('security.context')->getToken()->getUser() : null;
        //$templateMail = $this->getTemplateMail($body, $this->get('lugh.parameters')->getByKey('Config.mail.template', 'default'));
        $templateMail = $this->getTemplateMail($body, $this->get('lugh.route.template')->getMail());
        $uniqueid = uniqid();
        $user = null;
        
        foreach ($to as $toaddress) {
            
            $user = $this->em->getRepository('Lugh\WebAppBundle\Entity\User')->findOneByEmail($toaddress);
            
            $logMail = $this->get('domain.builder')->buildLogMail();
            $logMail->setBody($templateMail)
                    ->setSubject($subject)
                    ->setMailfrom($from == null ? $this->get('lugh.mailer')->getFrom() : $from)
                    ->setMailto($toaddress)
                    ->setWf(false)
                    ->setUserfrom($userfrom)
                    ->setUserdest($user)
                    ->setDateTime(new \DateTime())
                    ->setUniqueid($uniqueid)
                    ->setHide(false)
                ;
            $storage->addStack($logMail);
            $mailUsers[] = $toaddress;
        }
        $this->sendMail($mailUsers, $templateMail, $subject, $from == null ? $this->get('lugh.mailer')->getFrom() : $from);
        $storage->saveStack($logMail);
    }

    public function sendMailByRole($roles, $body, $subject, $from = null) {
        $storage = $this->get('lugh.Storage');
        $userfrom = $this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED') ? $this->get('security.context')->getToken()->getUser() : null;
        //$templateMail = $this->getTemplateMail($body, $this->get('lugh.parameters')->getByKey('Config.mail.template', 'default'));
        $templateMail = $this->getTemplateMail($body, $this->get('lugh.route.template')->getMail());
        $uniqueid = uniqid();

        foreach ($roles as $role) {
            foreach ($this->getUsersByRole($role) as $user) {
                $logMail = $this->get('domain.builder')->buildLogMail();
                $logMail->setBody($templateMail)
                        ->setSubject($subject)
                        ->setMailfrom($from == null ? $this->get('lugh.mailer')->getFrom() : $from)
                        ->setMailto($user->getEmail())
                        ->setWf(false)
                        ->setUserfrom($userfrom)
                        ->setUserdest($user)
                        ->setDateTime(new \DateTime())
                        ->setUniqueid($uniqueid)
                        ->setHide(false)
                    ;
                $storage->addStack($logMail);
                $mailUsers[] = $user->getEmail();
            }

        }
        $this->sendMail($mailUsers, $templateMail, $subject, $from == null ? $this->get('lugh.mailer')->getFrom() : $from);
        $storage->saveStack($logMail);

    }

    public function sendMailByUsername($usernames, $body, $subject, $from = null) {
        $storage = $this->get('lugh.Storage');
        $userfrom = $this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED') ? $this->get('security.context')->getToken()->getUser() : null;
        //$templateMail = $this->getTemplateMail($body, $this->get('lugh.parameters')->getByKey('Config.mail.template', 'default'));
        $templateMail = $this->get('lugh.route.template')->getMail();
        $uniqueid = uniqid();
        $mailUsers = array();

        foreach ($usernames as $username) {
            foreach ($this->getUsersByUsername($username) as $user) {
                $logMail = $this->get('domain.builder')->buildLogMail();
                $logMail->setBody($templateMail)
                        ->setSubject($subject)
                        ->setMailfrom($from == null ? $this->get('lugh.mailer')->getFrom() : $from)
                        ->setMailto($user->getEmail())
                        ->setWf(false)
                        ->setUserfrom($userfrom)
                        ->setUserdest($user)
                        ->setDateTime(new \DateTime())
                        ->setUniqueid($uniqueid)
                        ->setHide(false)
                    ;
                $storage->addStack($logMail);
                $mailUsers[] = $user->getEmail();
            }

        }
        $this->sendMail($mailUsers, $templateMail, $subject, $from == null ? $this->get('lugh.mailer')->getFrom() : $from);
        $storage->saveStack($logMail);

    }


    private function formatandsendFunc($item, $state, $extra = '', $attributs = array(), $external = array(), $attachments = array())
    {
        
        $hide               = true;
        $extraKey           = ($extra != '') ? '_' . $extra : '';
        $itemWorkflowJson   = $this->get('lugh.mails')->getByKey($item::nameClass . $extraKey . '.mail.' . $state , false);
        $itemWF             = $itemWorkflowJson != false ? json_decode($itemWorkflowJson, true) : false;

        if ($itemWF != false)
        {
            foreach ($itemWF as $itemMail) {
                $this->checkJson($itemMail);
                $hide = isset($itemMail['Hide']) ? $itemMail['Hide'] : false;

                if ($itemMail['Activate'] == 1)
                {
                     $this->formatAndSendMail($item, $itemMail, $attributs, $external, $state, $hide, $attachments);
                }
            }
        }
        return true;
    }

    private function formatAndSendMail($item, $itemMail, $attributs, $external, $state, $hide, $attachments)
    {
        foreach ($itemMail['To'] as $to_role) {
            

            $to_user        = $this->getTo($to_role, $item);
            $to_mail        = null;
            $attributs      = isset($external['tag']) && isset($external['vars']) ?
                                array_merge($attributs,array('%external%' => $this->setTranslate($external['tag'], $external['vars']))) :
                                array_merge($attributs,array('%external%' => ''));
            
            
            // Attachments
            $documentos = $attachments;
            $attachments = array();
            foreach($documentos as $documento)
            {
                $path = StoreManager::RetrieveGenericFile($documento->getNombreInterno());
                $attachments[$path] = $documento->getNombreExterno();
            }

            if ($to_role == 'DELEGADO')
            {
                $to_user = array();
                $to_mail = $item->getDelegado()->getEmail() != null ? $item->getDelegado()->getEmail() : 'desarrollo@header.net';
            }

            foreach ($to_user as $user) {
                $actual_lang    = $this->setLocaleByUserRole($to_role, $user);
                $body           = $this->getTemplate($itemMail, $item, $state,$this->get('lugh.route.template')->getMail(), $attributs);
                $notification   = $this->getTemplate($itemMail, $item, $state, 'notification');
                $subject        = $this->getSubject($itemMail['Subject']);

                $this->setLocaleByLang($actual_lang);
                $this->sendUserMail($body,$subject,array($user),true,$notification,$to_mail,null,$hide, $attachments);
            }
        }
        return true;
    }



    protected function getVars($objs = array())
    {
        $serializer = $this->get('jms_serializer');
        $request = Request::createFromGlobals();

        $objsArray = array(
            '%host%'    =>  $request->getHttpHost(),
            '%baseurl%' =>  $request->getBaseUrl(),
            '%baseuri%' =>  $request->getUriForPath('/'),
            '%uri%'     =>  $request->getUri()
        );
        foreach ($objs as $obj) {
            $objArray = $serializer->serialize($obj, 'json',SerializationContext::create()->setGroups(array('Default', 'Pass', 'VirtualMail', 'VarMail', 'Personal')));
            $arrayfl = $this->subArray(json_decode($objArray, true));
            $objsArray = array_merge($objsArray, $arrayfl);
        }
        return $objsArray;
    }

    private function checkJson($itemMail) {
        $require = array(
            'Activate',
            'To',
            'Subject'
            );

        foreach ($require as $value) {
            if (!isset($itemMail[$value]))
            {
                throw new Exception($value . " missing");
            }
        }

        return true;
    }

    private function subArray($array, $keyarray = '')
    {
        $arrayfl = array();
        $keyarray = $keyarray != '' ? $keyarray . '_' : $keyarray;
        foreach ($array as $key => $value)
            {
                if (!is_array($value))
                {
                    $arrayfl['%'.$keyarray.$key.'%'] = $value;
                }
                else
                {
                    $arrayfl = array_merge($arrayfl,$this->subArray($value,$keyarray.$key));
                }
            }
        return $arrayfl;
    }

    private function setTranslate($trans, $vars, $iterator = 0)
    {
        $translate = $this->getTranslate($trans, $vars);
        $newvars = $this->getSubVars($translate, $vars);

        if ($translate != $trans && $iterator < 10)
        {
            $trans = $this->setTranslate($translate, $newvars, $iterator+1);
        }
        return $trans;

    }
    private function getSubVars($translate, $vars)
    {
        $tags = array();
        foreach (explode(' ', $translate) as $word) {
            list($tag) = sscanf($word, "%%%s%%");
            if ($tag != null)
            {
                $tag = substr($tag, 0, strlen($tag)-1);
                $tags['%'.$tag.'%'] = $this->getTranslate($tag, $vars);
            }
        }
        return array_merge($vars,$tags);
    }

    private function getTranslate($trans, $vars){
        $domain     = $this->get('lugh.route.template')->getTemplatePath();
        $translate  = $this->get('translator')->trans($trans,$vars, $domain, $this->lang);

        if ($translate == $trans) {
            $translate = $this->get('translator')->trans($trans,$vars, 'messages', $this->lang);
        }

        return $translate;
    }

    private function getTemplateMail($translate, $template = 'default', $item=null, $state = null, $subject=null)
    {
        $template_path = 'LughWebAppBundle:Mail:' .$template . '.html.twig';
        return $this->get('templating')->render(
                $template_path,
                array(
                    'item'=>$item,
                    'state'=>$state,
                    'subject' => $subject,
                    'translate' => $translate
                ));
    }

    private function getTemplate($itemMail, $item, $state, $templateTag = 'default', $attributs = array())
    {
        $template = '';
        if (isset($itemMail['Template']) && $itemMail['Template'] != '')
        {
            $template = $itemMail['Template'];
        }

        
        $vars = array_merge($this->getVars(array($item)),array('%state%' => $state), array('%date_time%'=> date('d-m-Y H:i')), $attributs);
        $translate = $this->setTranslate($itemMail['TranslateTag'], $vars);
        if ($translate == $itemMail['TranslateTag'])
        {
            $translate = 'Item: ' . $item->getId() . ' ' . 'State: ' . $state;
            $templateMail = ($template != '') ? $template : $this->getTemplateMail($translate, $templateTag, $item, $state, $this->getSubject($itemMail['Subject']));
        }
        else
        {
            $templateMail = ($template != '') ? $template : $this->getTemplateMail($translate, $templateTag, $item, $state, $this->getSubject($itemMail['Subject']));
        }

        return $templateMail;
    }


    private function setLocaleByUserRole($role, $user = null)
    {
        $actual_locale = $this->lang;
        switch ($role) {
            case 'CUSTOMER':
                $this->lang = 'es_es';
                break;
            case 'ADMIN':
                $this->lang = 'es_es';
                break;
            case 'DELEGADO':
                $this->lang = 'es_es';
                break;
            case 'USER':
                $locale = $this->get('lugh.translate.register')->getUserLocale($user);
                $this->lang = $locale;
                break;
            case 'ADHESIONS':
                $locale = $this->get('lugh.translate.register')->getUserLocale($user);
                $this->lang = $locale;
                break;
            case 'OWNER':
                $locale = $this->get('lugh.translate.register')->getUserLocale($user);
                $this->lang = $locale;
                break;
            default:
                break;
        }
        return $actual_locale;
    }

    private function setLocaleByLang($lang)
    {
        $this->lang = $lang;
        return $this->lang;
    }

    private function getTo($role, $item)
    {
        $to = array();
        switch ($role) {
            case 'CUSTOMER':
                $to = array_merge(
                        $this->getUsersByRole('ROLE_CUSTOMER'),
                        $this->getUsersByRole('ROLE_ADMIN'),
                        $this->getUsersByRole('ROLE_SUPER_ADMIN')
                        );
                break;
            case 'ADMIN':
                $to = array_merge(
                        $this->getUsersByRole('ROLE_ADMIN'),
                        $this->getUsersByRole('ROLE_SUPER_ADMIN')
                        );
                break;
            case 'USER':
                $to = array($this->getUser($item));
                break;
            case 'ADHESIONS':
                $to = $this->getUserAdhesion($item);
                break;
            case 'OWNER':
                $to = $this->getUserOwner($item);
                break;
            default:
                break;
        }
        return $to;
    }

    private function getSubject($subject)
    {
        $translate = $this->get('translator')->trans($subject, array(), 'messages', $this->lang);
        return ($translate != $subject) ? $translate : $subject;
    }




    private function getUsersByRole($varRole)
    {
        $allowedUsers = array();

        $entities = $this->em->getRepository('Lugh\WebAppBundle\Entity\\User')->findAll();


        foreach($entities as $user){

            foreach($user->getRoles() as $role){
                if($role == $varRole){

                       $allowedUsers[] = $user;
                }

            }
        }

        return $allowedUsers;
    }

    private function getUsersByUsername($username)
    {
        $allowedUsers = array();

        $entities = $this->em->getRepository('Lugh\WebAppBundle\Entity\\User')->findAll();


        foreach($entities as $user){

            if($user->getUsername() == $username){
                $allowedUsers[] = $user;
            }
        }

        return $allowedUsers;
    }

    private function getUser($item)
    {
        if (method_exists($item,'getAutor'))
        {
            if ($item::nameClass == 'Message')
            {
                return $this->getUserMessage($item);
            }
            return $item->getAutor()->getUser();
        }
        elseif (method_exists($item, 'getAccionista'))
        {
            return $item->getAccionista()->getUser();
        }
        elseif (method_exists($item, 'getOwner'))
        {
            return $item->getOwner();
        }
        elseif (get_class($item) . '::nameClass' && $item::nameClass == 'Accionista')
        {
            return $item->getUser();
        }
        elseif (get_class($item) . '::nameClass' && $item::nameClass == 'User')
        {
            return $item;
        }
        return null;
    }

    private function getUserMessage($item)
    {
        $items = array(
            'Offer',
            'Proposal',
            'Request',
            'Initiative',
            'Thread'
        );

        foreach ($items as $method) {
            if ($item->{'get' . $method}() != null)
            {
                return $item->{'get' . $method}()->getAutor()->getUser();
            }
        }
        return null;
    }

    private function getUserAdhesion($item)
    {
        $userAdhesion = array();
        if (method_exists($item,'getAdhesions'))
        {
            foreach ($item->getAdhesions() as $adhesion) {
                if ($adhesion->getState() == StateClass::statePublic)
                {
                    $userAdhesion[] = $this->getUser($adhesion);
                }
            }
        }
        return $userAdhesion;
    }

    private function getUserOwner($adhesion)
    {
        $userOwner = array();
        if (method_exists($adhesion,'getItem') && get_class($adhesion) . '::nameClass' && substr($adhesion::nameClass, 0, 8) == 'Adhesion')
        {
            if ($adhesion->getItem()->getState() == StateClass::statePublic)
            {
                $userOwner[] = $this->getUser($adhesion->getItem());
            }
        }
        return $userOwner;
    }

    private function sendUserMail($body, $subject, $userto = array(), $wf = false, $notification = null, $to = null, $from = null, $hide=false, $attachments = array()) {


        $storage = $this->get('lugh.Storage');
        $userfrom = $this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED') ? $this->get('security.context')->getToken()->getUser() : null;
        $templateMail = $body;
        $uniqueid = uniqid();
        $logMail = null;
        $mailUsers = array();

        if ($to != null)
        {
            $logMail = $this->get('domain.builder')->buildLogMail();
            $logMail->setBody($templateMail)
                    ->setSubject($subject)
                    ->setMailfrom($from == null ? $this->get('lugh.mailer')->getFrom() : $from)
                    ->setMailto($to)
                    ->setWf($wf)
                    ->setUserfrom($userfrom)
                    ->setDateTime(new \DateTime())
                    ->setUniqueid($uniqueid)
                    ->setHide($hide)
                ;
            $storage->addStack($logMail);
            $this->sendMail(array($to), $templateMail, $subject, $from, $attachments);
        }
        elseif (count($userto) > 0)
        {
            foreach ($userto as $user) {
                $logMail = $this->get('domain.builder')->buildLogMail();
                $logMail->setBody($templateMail)
                        ->setSubject($subject)
                        ->setMailfrom($from == null ? $this->get('lugh.mailer')->getFrom() : $from)
                        ->setMailto($user->getEmail())
                        ->setNotification($notification)
                        ->setWf($wf)
                        ->setUserfrom($userfrom)
                        ->setUserdest($user)
                        ->setDateTime(new \DateTime())
                        ->setUniqueid($uniqueid)
                        ->setHide($hide)
                    ;
                $storage->addStack($logMail);
                $mailUsers[] = $user->getEmail();
            }
            $this->sendMail($mailUsers, $templateMail, $subject, $from == null ? $this->get('lugh.mailer')->getFrom() : $from, $attachments);
        }

        if ($logMail != null)
        {
            $storage->saveStack($logMail);
        }
    }

    private function sendMail($to, $body, $subject, $from = null, $attachments = array()) {
        try {
            $message = \Swift_Message::newInstance($subject)
            ->setBody($body)
            ->setContentType('text/html');
                        
            foreach ($attachments as $key=>$value)
            {
                $message->attach(\Swift_Attachment::fromPath($key)->setFilename($value));
            }
            
            foreach ($to as $address) {
                $message->setTo($address);
                $this->get('lugh.mailer')->send($message);
            }            
            
        } catch (Exception $exc) {
            throw new Exception($exc->getMessage());
        }
    }

}
