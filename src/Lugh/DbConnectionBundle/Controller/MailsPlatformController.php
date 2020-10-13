<?php

namespace Lugh\DbConnectionBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Lugh\DbConnectionBundle\Lib\PlatformsManager;
use Symfony\Component\Config\Definition\Exception\Exception;
use Lugh\WebAppBundle\Entity\Mails;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Dumper;

/**
 * @Route("/mailsplatform")
 */
class MailsPlatformController extends Controller
{
    /**
     * @Route("/" ,name="_mailsplatform_index")
     * @Template()
     */
    public function indexAction()
    {
        return array();
    }
    
    
    /**
     * @Route("/mails" ,name="_mailsplatform_mails")
     * @Template()
     */
    public function contentMailsAction()
    {
        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $platform_id = $request->get('platform_id');
        return array('platform_id' => $platform_id);
    }
    
    /**
     * @Route("/editallmail/{platform_id}/{tag}" ,name="_mailplatform_edit_all_mail", defaults={"tag":"UNDEFINED"})
     * @Template()
     */
    public function editAllMailAction($platform_id, $tag)
    {
        $platform = $this->getEm($platform_id);  
        $em = $this->getDoctrine()->getManager();
        $parameters = $em->getRepository('Lugh\WebAppBundle\Entity\Mails')->findAll();
        //$tag = $tag == "UNDEFINED" ? '' : $this->getTag($platform_id, $tag);
        $tag = $tag == "UNDEFINED" ? '' : $tag;

        $mails = array();
        foreach ($parameters as $parameter) {
            if (strpos($parameter->getKeyParam(), '.mail.') != false && strpos($parameter->getKeyParam(), 'Config.') === false && !$this->specialMails($parameter->getKeyParam()))
            {
                $actions = strpos($parameter->getKeyParam(), '.activate') != false ? $this->getActions('activate') : $this->getActions('state');
                $valueJSON = json_decode($parameter->getValueParam(), true);
                $mail['key'] = $parameter->getKeyParam();
                $mail['value'] = $parameter->getValueParam();
                $mail['ide'] = $parameter->getId();
                $mail['id'] = str_replace('.', '_',$parameter->getKeyParam());
                $mail['clase'] = substr($parameter->getKeyParam(), 0, strpos($parameter->getKeyParam(), '.mail.'));
                $mail['action'] = substr($parameter->getKeyParam(), strpos($parameter->getKeyParam(), '.mail.')+ 6);
                //$mail['translatetag'] = $mail_json['TranslateTag'];
                
                $mail['to'] = $this->getWidget(
                        'textarea', 
                        'to', 
                        $this->getTos($valueJSON)
                        );
                $mails[] = $mail;
            }
        }
        $form_mail = array();
        //$form_mail['id'] = str_replace('-', '_',$opcionVoto->getId());
        $form_mail['clase'] = $this->getWidget(
                'choice', 
                'clase', 
                '', 
                array('choices' => $this->getClasses('state'))
                );
        $form_mail['action'] = $this->getWidget(
                'choice', 
                'action', 
                '', 
                array('choices' => $actions)
                );
        $form_mail['subject'] = $this->getWidget(
                'text', 
                'Subject',
                ''
                );
        $form_mail['to'] = $this->getWidget(
                'choice', 
                'To',
                array(),
                array('choices' => $this->getTo(), 'empty_value' => false, 'multiple'  => true));
        $tags = $this->getTags($this->readTranslate($platform_id), $platform_id);
        $form_mail['translatetag'] = $this->getWidget(
                'choice', 
                'TranslateTag',
                $tag,
                array('choices' => $tags, 'empty_value' => false));
        
        $form_mail['template'] = $this->getWidget(
                        'textarea', 
                        'Template', 
                        ''
                        );
        $form_mail['hide'] = $this->getWidget(
                    'checkbox', 
                    'Hide', 
                    false,
                    array('value' => 'activate')
                    );
        $form_mail['activate'] = $this->getWidget(
                    'checkbox', 
                    'Activate', 
                    false,
                    array('value' => 'activate')
                    );

        $L = $this->get('lugh.langs.db_connection');
        $langs = $L->from($platform_id);
        foreach ($langs as $lang => $value){
            $langs[$lang] = array('code' => $L->langCode($lang), 'name' => $L->langName($lang));
        }
        return array('mails' => $mails, 'form_mail' => $form_mail, 'platform' => $platform, 'langs' => $langs);
    }
    
    /**
     * @Route("/editmail/{id}/{platform_id}/{tag}" ,name="_mailplatform_editmail", defaults={"tag":"UNDEFINED"})
     * @Template()
     */
    public function editMailAction($id, $platform_id, $tag)
    {
        $platform = $this->getEm($platform_id);  
        $em = $this->getDoctrine()->getManager();
        //$tag = $tag == "UNDEFINED" ? '' : $this->getTag($platform_id, $tag);
        $tag = $tag == "UNDEFINED" ? '' : $tag;
        
        $parameter = $em->getRepository('Lugh\WebAppBundle\Entity\Mails')->find($id);
        $mails_json = json_decode($parameter->getValueParam(), true);
        $index = 0;
        $mails = array();
        foreach ($mails_json as $mail_json) {
            $mail = array();
            $mail['id'] = $index;
            $mail['to'] = array_reduce($mail_json['To'], function($carry, $item){return $carry . $item . ', ';});
            $mail['subject'] = $mail_json['Subject'];
            $mail['translatetag'] = $mail_json['TranslateTag'];
            $mail['template'] = $mail_json['Template'];
            $mail['hide'] = $this->getWidget(
                    'checkbox', 
                    'hide', 
                    isset($mail_json['Hide']) && $mail_json['Hide'] ? true : false,
                    array('value' => 'activate')
                    );
            $mail['activate'] = $this->getWidget(
                    'checkbox', 
                    'activate', 
                    isset($mail_json['Activate']) && $mail_json['Activate'] ? true : false,
                    array('value' => 'activate')
                    );
            $mails[] = $mail;
            $index++;
        }
        $form_mail = array();
        //$form_mail['id'] = str_replace('-', '_',$opcionVoto->getId());
        $form_mail['subject'] = $this->getWidget(
                'text', 
                'Subject',
                ''
                );
        $form_mail['to'] = $this->getWidget(
                'choice', 
                'To',
                array(),
                array('choices' => $this->getTo(), 'empty_value' => false, 'multiple'  => true));
        $tags = $this->getTags($this->readTranslate($platform_id), $platform_id);
        $form_mail['translatetag'] = $this->getWidget(
                'choice', 
                'TranslateTag',
                $tag,
                array('choices' => $tags, 'empty_value' => false));
        
        $form_mail['template'] = $this->getWidget(
                        'textarea', 
                        'Template', 
                        ''
                        );
        $form_mail['hide'] = $this->getWidget(
                    'checkbox', 
                    'Hide', 
                    false,
                    array('value' => 'activate')
                    );
        $form_mail['activate'] = $this->getWidget(
                    'checkbox', 
                    'Activate', 
                    false,
                    array('value' => 'activate')
                    );

        $L = $this->get('lugh.langs.db_connection');
        $langs = $L->from($platform_id);
        foreach ($langs as $lang => $value){
            $langs[$lang] = array('code' => $L->langCode($lang), 'name' => $L->langName($lang));
        }
        return array('mails' => $mails, 'form_mail' => $form_mail, 'mail_id' => $id, 'platform' => $platform, 'langs' => $langs);
    }
    
    /**
     * @Route("/parameterstablecontent" ,name="_mailsplatform_parameters_table_content")
     * @Template()
     */
    public function parametersTableContentAction()
    {
        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $platform_id = $request->get('platform_id');
        $platform = $this->getEm($platform_id);        
        $em = $this->getDoctrine()->getManager();

        $parameters = $em->getRepository('Lugh\WebAppBundle\Entity\Mails')->findAll();

        $parameters_mails = array();

        
        foreach ($parameters as $parameter) {
            if (strpos($parameter->getKeyParam(), '.mail.') != false && strpos($parameter->getKeyParam(), 'Config.') === false && !$this->specialMails($parameter->getKeyParam()))
            {
                $actions = strpos($parameter->getKeyParam(), '.activate') != false ? $this->getActions('activate') : $this->getActions('state');
                $valueJSON = json_decode($parameter->getValueParam(), true);
                $parameter_mail['key'] = $parameter->getKeyParam();
                $parameter_mail['value'] = $parameter->getValueParam();
                $parameter_mail['ide'] = $parameter->getId();
                $parameter_mail['id'] = str_replace('.', '_',$parameter->getKeyParam());
                $parameter_mail['clase'] = $this->getWidget(
                        'choice', 
                        'clase_' . $parameter_mail['id'], 
                        substr($parameter->getKeyParam(), 0, strpos($parameter->getKeyParam(), '.mail.')), 
                        array('choices' => $this->getClasses('state'))
                        );
                $parameter_mail['action'] = $this->getWidget(
                        'choice', 
                        'action_' . $parameter_mail['id'], 
                        substr($parameter->getKeyParam(), strpos($parameter->getKeyParam(), '.mail.')+ 6), 
                        array('choices' => $actions, 'empty_value' => false)
                        );
                
                $parameter_mail['to'] = $this->getWidget(
                        'textarea', 
                        'to_' . $parameter_mail['id'], 
                        $this->getTos($valueJSON)
                        );
                $parameters_mails[] = $parameter_mail;
            }
        }
        return array('parameters_mails' => $parameters_mails, 'platform' => $platform);
    }
    
    /**
     * @Route("/specialmailstablecontent" ,name="_mailsplatform_specialmails_table_content")
     * @Template()
     */
    public function specialmailsTableContentAction()
    {
        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $platform_id = $request->get('platform_id');
        $platform = $this->getEm($platform_id);        
        $em = $this->getDoctrine()->getManager();

        $parameters = $em->getRepository('Lugh\WebAppBundle\Entity\Mails')->findAll();

        $parameters_mails = array();

        
        foreach ($parameters as $parameter) {
            if ($this->specialMails($parameter->getKeyParam()))
            {
                $actions = strpos($parameter->getKeyParam(), '.activate') != false ? $this->getActions('activate') : $this->getActions('others');
                $valueJSON = json_decode($parameter->getValueParam(), true);
                $parameter_mail['key'] = $parameter->getKeyParam();
                $parameter_mail['value'] = $parameter->getValueParam();
                $parameter_mail['ide'] = $parameter->getId();
                $parameter_mail['id'] = str_replace('.', '_',$parameter->getKeyParam());
                $parameter_mail['clase'] = $this->getWidget(
                        'choice', 
                        'clase_' . $parameter_mail['id'], 
                        substr($parameter->getKeyParam(), 0, strpos($parameter->getKeyParam(), '.mail.')), 
                        array('choices' => $this->getClasses('others'))
                        );
                $parameter_mail['action'] = $this->getWidget(
                        'choice', 
                        'action_' . $parameter_mail['id'], 
                        substr($parameter->getKeyParam(), strpos($parameter->getKeyParam(), '.mail.')+ 6), 
                        array('choices' => $actions, 'empty_value' => false)
                        );
                
                $parameter_mail['to'] = $this->getWidget(
                        'textarea', 
                        'to_' . $parameter_mail['id'], 
                        $this->getTos($valueJSON)
                        );
                $parameters_mails[] = $parameter_mail;
            }
        }
        return array('parameters_mails' => $parameters_mails, 'platform' => $platform);
    }
    
    /**
     * @Route("/yamlcontent/{platform_id}/{tag}" ,name="_mailsplatform_yaml_content", defaults={"tag":"UNDEFINED"})
     * @Template()
     */
    public function yamlContentAction($platform_id,$tag)
    {
        $yaml = $this->readTranslate($platform_id,true);
        try {
            $content = array('message' => $yaml[$tag]);
        } catch (\Exception $exc) {
            return new Response(json_encode(array('error'=> $exc->getMessage())));
        }
        return new Response(json_encode(array('success'=> $content)));
    }
    
    /**
     * @Route("/editcontentmail/{platform_id}/{tag}" ,name="_mailsplatform_edit_content_mail", defaults={"tag":"UNDEFINED"})
     * @Template()
     */
    public function editContentMailAction($platform_id, $tag)
    {
        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $contents = $request->get('content',array());
        try {
            foreach ($contents as $key => $value) {
                list($lang) = sscanf($key, 'form-tag-%s');
                $lang = substr($lang, 0, 2);
                //$yaml = $this->readTranslate($platform_id, false, $lang);
                $yaml[$tag] = $value;
                $this->writeTranslate($platform_id, $yaml, $lang);
            }
        } catch (\Exception $exc) {
            return new Response(json_encode(array('error'=> $exc->getMessage())));
        }
        return new Response(json_encode(array('success'=> "1")));
    }
    
    /**
     * @Route("/addContentMail/{platform_id}/{id}" ,name="_mailplatform_add_contentMail")
     * @Template()
     */
    public function addParameterAction($platform_id, $id)
    {
        $this->getEm($platform_id);        
        $em = $this->getDoctrine()->getManager();
        
        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $params = $request->get('params');
        $type = $request->get('type');      
        $form = $params['form'];
        $parameter = $em->getRepository('Lugh\WebAppBundle\Entity\Mails')->find($id);
        $mail_json = json_decode($parameter->getValueParam(), true);

        try {
            if (!isset($form['Subject']) || $form['Subject']=='')
            {
                throw new Exception("Subject Blank");
            }
            if (!isset($form['To']) || $form['To']=='')
            {
                throw new Exception("To Blank");
            }
            $mail_json[] = $form;
            $mail = json_encode($mail_json);
            $parameter->setValueParam($mail);
            $em->persist($parameter); 
            $em->flush();
        } catch (Exception $exc) {
            return new Response(json_encode(array('error'=> $exc->getMessage())));
        }
        return new Response(json_encode(array('success'=> '1')));
    }
    
    /**
     * @Route("/addMail/{platform_id}" ,name="_mailplatform_add_Mail")
     * @Template()
     */
    public function addMailAction($platform_id)
    {
        $this->getEm($platform_id);        
        $em = $this->getDoctrine()->getManager();
        
        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $params = $request->get('params');
        $type = $request->get('type');      
        $form = $params['form'];
        $falg_param = false;

        try {
            $validation = array(
                'clase',
                'action',
                'Subject',
                'To'
            );
            foreach ($validation as $val) {
                if (!isset($form[$val]) || $form[$val]=='')
                {
                    throw new Exception($val . " Blank");
                }
            }
            $parameters = $em->getRepository('Lugh\WebAppBundle\Entity\Mails')->findAll();
            foreach ($parameters as $parameter) {
                if (
                        strpos($parameter->getKeyParam(), '.mail.') != false                && 
                        strpos($parameter->getKeyParam(), 'Config.') === false              && 
                        !$this->specialMails($parameter->getKeyParam())                     &&
                        strpos($parameter->getKeyParam(), ucfirst($form['clase']) . '.') !== false    &&
                        strpos($parameter->getKeyParam(), '.' . lcfirst($form['action'] )) != false
                    )
                {
                    $mail_json = json_decode($parameter->getValueParam(), true);
                    foreach ($mail_json as $mail_js) {
                        if ($form['TranslateTag']==$mail_js['TranslateTag'] && count(array_diff($form['To'], $mail_js['To'])) == 0)
                        {
                            throw new Exception("Duplicate Tag and To");
                        }
                    }
                    unset($form['clase']);
                    unset($form['action']);
                    $mail_json[] = $form;
                    $mail = json_encode($mail_json);
                    $parameter->setValueParam($mail);
                    $em->persist($parameter); 
                    //$em->flush();
                    $falg_param = true;
                    break;
                }
            }
            if ($falg_param == false)
            {
                $parameter = new Mails();
                $key_param = ucfirst($form['clase']) . '.mail.' . lcfirst($form['action']);
                unset($form['clase']);
                unset($form['action']);
                $mail_json[] = $form;
                $mail = json_encode($mail_json);
                $parameter->setKeyParam($key_param);
                $parameter->setValueParam($mail);
                $em->persist($parameter); 
                $em->flush();
            }

        } catch (Exception $exc) {
            return new Response(json_encode(array('error'=> $exc->getMessage())));
        }
        return new Response(json_encode(array('success'=> '1')));
    }
    
    /**
     * @Route("/removeMailContent/{id}" ,name="_mailsplatform_remove_mail_content")
     * @Template()
     */
    public function removePuntoAction($id)
    {
        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $em = $this->getDoctrine()->getManager();
        try {
            $parameter = $em->getRepository('Lugh\WebAppBundle\Entity\Mails')->find($id);
            $mail_json = json_decode($parameter->getValueParam(), true);
            array_splice($mail_json, intval($request->get('id')), 1);
            $mail = json_encode($mail_json);
            $parameter->setValueParam($mail);
            $em->persist($parameter); 
            $em->flush();
        } catch (\Exception $exc) {
            return new Response(json_encode(array('error'=> $exc->getMessage())));
        }
        return new Response(json_encode(array('success'=> '1')));
    }
    
    
    
    
    
    private function getWidget($type, $name, $data, $extra=array())
    {
 
        $widget = $this->createFormBuilder()->add(
                $name, 
                $type, 
                array_merge(array(
                    'data'      => $data,
                    'required'  => false
                ),$extra))->getForm()->createView();
        
        return $widget;
    }
    
    private function getActions($type = null)
    {
        $actions = array(
            'state' => array(
                'pendiente' => 'Pendiente',
                'publica'   => 'Publica',
                'retorna'   => 'Retorna',
                'rechaza'   => 'Rechaza',
                'store'     => 'Store',
                'create'    => 'Create',
                'delete'    => 'Delete',
                'get'       => 'Get',
                'add'       => 'Add',
                'locked'    => 'Locked',
                'unlocked'  => 'Unlocked',
                'enable'    => 'Enable',
                'disable'   => 'Disable',
                ),
            'activate' => array(
                'activate'  => 'Activate'
            ), 
            'others' => array(
                'forgotPassword'    =>  'Forgot Password',
                'resetPassword'     =>  'Reset Password'
            ),
        );
        return $type == null ? $actions['state']+$actions['activate']+$actions['others'] : $actions[$type];
    }
    
    private function getClasses($type = null)
    {
        $clases = array(
            'state' => array(
                'ItemAccionista'        => 'Accionista',
                'ItemAccionista_ROLE_USER_CERT'        => 'Accionista Certificado',
                'ItemAccionista_ROLE_USER_FULL'        => 'Accionista user/pass',
                'Proposal'          => 'Proposal',
                'Initiative'        => 'Initiative',
                'Offer'             => 'Offer',
                'Request'           => 'Request',
                'AdhesionProposal'  => 'AdhesionProposal',
                'AdhesionInitiative'=> 'AdhesionInitiative',
                'AdhesionOffer'     => 'AdhesionOffer',
                'AdhesionRequest'   => 'AdhesionRequest',
                'Thread'            => 'Thread',
                'Communique'        => 'Communique',
                'Delegacion'        => 'Delegacion',
                'Proposal'          => 'Proposal',
                'Question'          => 'Ruegos y preguntas',
                'Desertion'         => 'Abandono',
                'Anulacion'         => 'Anulacion',
                'Av'                => 'Av',
                'AppAV'             => 'AppAv',
                'Accionista'        => 'Accionista',
                'Message'           => 'Message',
                'Message_Proposal'  => 'Message_Proposal',
                'Message_Initiative'=> 'Message_Initiative',
                'Message_Request'   => 'Message_Request',
                'Message_Offer'     => 'Message_Offer',
                'Voto'              => 'Voto',
            ),
            'others' => array(
                'User'              => 'User',
                'ItemAccionista'    => 'ItemAccionista',
                'LogMail'           => 'LogMail',
                'Document'          => 'Document',
                'Parametros'        => 'Parametros',
                'VotoPunto'         => 'VotoPunto',
                'Anulacion'         => 'Anulacion',
                'Delegacion'        => 'Delegacion',
                'Delegado'          => 'Delegado',
                'PuntoDia'          => 'PuntoDia',
                'OpcionesVoto'      => 'OpcionesVoto',
                'Config'            => 'Config'
            )
        );
        
        return $type == null ? $clases['state']+$clases['others'] : $clases[$type];
    }
    
    private function getEm($platform_id)
    {
        $em = $this->getDoctrine()->getManager('db_connection');
        $platform =  $em->getRepository('Lugh\DbConnectionBundle\Entity\Auth')->find($platform_id);
        PlatformsManager::switchDb($platform->getDbname());
        
        return $platform;
    }
    
    private function specialMails($key_param)
    {
        $keys = array(
            'User.mail.forgotPassword'                  => true,
            'User.mail.resetPassword'                   => true
        );
        
        return isset($keys[$key_param]) ? $keys[$key_param] : false;
    }
    
    private function getTo()
    {
        return array(
            'CUSTOMER'  =>  'Customer',
            'ADMIN'     =>  'Admin',
            'USER'      =>  'User',
            'ADHESIONS' =>  'Adhesions',
            'DELEGADO'  =>  'Delegado'
        );
    }
    
    private function getTos($valueJSON)
    {
        $tos = '';
        foreach ($valueJSON as $value) {
            foreach ($value['To'] as $to)
            {
                $tos .= $to . chr(10);
            }
        }
        return $tos;
    }
    
    private function readTranslate($platform_id, $languages = false, $lang = 'es')
    {

        $L = $this->get('lugh.langs.db_connection');
        $langs = $L->from($platform_id);

        foreach ($langs as $l => $value){
            $langs[$l] = $L->langCode($l);
        }
        $langs_cod = $langs;
        /*
        $langs_cod = array (
            'es' => 'es_es',
            'en' => 'en_gb',
            'ca' => 'ca_es',
            'gl' => 'gl_es'
        );*/
        if ($languages)
        {
            $lang_array = array();
            foreach ($langs_cod as $ln => $val) {
                $lang_array[$ln] = $this->parseLang($ln, $platform_id);
            }
            foreach ($lang_array['es'] as $key => $value) {
                $language[$key] = array();
                foreach ($langs_cod as $ln => $val) {
                     $language[$key][$val] = isset($lang_array[$ln][$key]) ? $lang_array[$ln][$key] : '';
                }
            }
        }
        else 
        {
            $language = $this->parseLang($lang, $platform_id);
        }
        return $language;
    }
    
    private function writeTranslate($platform_id, $yml, $lang = 'es')
    {
        $kernel = $this->get('kernel');
        $bundle = $kernel->getBundle('LughWebAppBundle');
        $rootPath = $bundle->getPath();
        $tanslations_path = $rootPath . '/Resources/translations/';
        $path = $tanslations_path . $this->getHost($platform_id) . '.' . $lang . '.yml';
        $yaml = new Parser();
        $dumper = new Dumper();
        
        if (file_exists($path))
        {
            $yml = array_merge($yaml->parse(file_get_contents($path)),$yml);
            
        }
        
        return file_put_contents($path, $dumper->dump($yml, 1));
    }
    
    private function getTags($yaml_tags, $platform_id)
    {
        $tags = array();
        $host = $this->getHost($platform_id);
        foreach ($yaml_tags as $yaml_tag => $value) {
            //if (strpos($yaml_tag, '@') == false || strpos($yaml_tag, $host) !== false)
            {
                $tags[$yaml_tag] = ' ' . $yaml_tag;
            }
        }
        return $tags;
    }
    
    
    private function getTag($platform_id, $tag)
    {
        $host = $this->getHost($platform_id);
        return $tag . '@' . $host;
    }
    
    private function getHost($platform_id)
    {
        $em = $this->getDoctrine()->getManager('db_connection');
        $platform =  $em->getRepository('Lugh\DbConnectionBundle\Entity\Auth')->find($platform_id);
        return $platform->getHost();
    }
    
    private function getTemplate($platform_id)
    {
        $em = $this->getDoctrine()->getManager('db_connection');
        $platform =  $em->getRepository('Lugh\DbConnectionBundle\Entity\Auth')->find($platform_id);
        return $platform->getTemplate()->getPath();
    }
    
    private function parseLang($lang, $platform_id)
    {
        $kernel = $this->get('kernel');
        $bundle = $kernel->getBundle('LughWebAppBundle');
        $rootPath = $bundle->getPath();
        $tanslations_path = $rootPath . '/Resources/translations/';
        
        $files = array(
            'messages',
            $this->getTemplate($platform_id),
            $this->getHost($platform_id)
        );
        $yaml = new Parser();
        $language = array();
        
        foreach ($files as $file) {
            $path = $tanslations_path . $file . '.' . $lang . '.yml';
            if (file_exists($path))
            {
                $language = array_merge($language,$yaml->parse(file_get_contents($path)));
            }
        }
        
        return $language;
    }
    
    
}
