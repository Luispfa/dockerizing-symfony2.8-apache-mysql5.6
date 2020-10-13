<?php

namespace Lugh\DbConnectionBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Lugh\DbConnectionBundle\Lib\PlatformsManager;
use Symfony\Component\Config\Definition\Exception\Exception;
use Lugh\WebAppBundle\Entity\Parametros;

use Lugh\DbConnectionBundle\Lib\Classes\PDFObjects\TitlePage;
use Lugh\DbConnectionBundle\Lib\Classes\PDFObjects\UsoPage;
use Lugh\DbConnectionBundle\Lib\Classes\PDFObjects\Header;
use Lugh\DbConnectionBundle\Lib\Classes\PDFObjects\Footer;
use Lugh\DbConnectionBundle\Lib\Classes\PDFObjects\EstadisticasPage;
use Lugh\DbConnectionBundle\Lib\Classes\PDF\DocumentoPDF;
use Symfony\Component\HttpFoundation\File\File;
use Lugh\WebAppBundle\DomainLayer\State\StateClass;

/**
 * @Route("/configplatform")
 */
class ConfigPlatformController extends Controller
{
    /**
     * @Route("/" ,name="_configplatform_index")
     * @Template()
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * @Route("/editparametersoptions" ,name="_configplatform_edit_parameters_options")
     * @Template()
     */
    public function editParametersOptionsAction()
    {
        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $platform_id = $request->get('platform_id');
        $platform = $this->getEm($platform_id);
        $em = $this->getDoctrine()->getManager();

        $parameters = $em->getRepository('Lugh\WebAppBundle\Entity\Parametros')->findAll();

        $parameters_options = array();

        foreach ($parameters as $parameter) {
            if (strpos($parameter->getKeyParam(), 'Options.') !== false)
            {

                $parameter_opt['param_id'] = $parameter->getId();
                $parameter_opt['key'] = $parameter->getKeyParam();
                $parameter_opt['value'] = $this->getOptions(substr($parameter->getKeyParam(), 8))[$parameter->getValueParam()];
                $parameter_opt['clase'] = substr($parameter->getKeyParam(), 8);
                $parameter_opt['id'] = str_replace('.', '_',$parameter->getKeyParam());
                $parameter_opt['options'] = $parameter->getValueParam();
                $parameters_options[] = $parameter_opt;
            }
        }
        $form_options = array();
        $form_options['options'] = $this->getWidget(
                'choice',
                'options',
                '',
                array('choices' => $this->getOptions())
                );
        $form_options['values'] = $this->getWidget(
                'choice',
                'valueoption',
                ''
                );

        return array('parameters_options' => $parameters_options, 'form_options' => $form_options, 'platform' => $platform);
    }

    /**
     * @Route("/editparametersconfig" ,name="_configplatform_edit_parameters_config")
     * @Template()
     */
    public function editParametersConfigAction()
    {
        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $platform_id = $request->get('platform_id');
        $platform = $this->getEm($platform_id);
        $em = $this->getDoctrine()->getManager();

        $parameters = $em->getRepository('Lugh\WebAppBundle\Entity\Parametros')->findAll();

        $parameters_config = array();

        foreach ($parameters as $parameter) {
            if (strpos($parameter->getKeyParam(), 'Config.') !== false)
            {

                $mail_parameters = array(
                    '0' =>  'SendMail',
                    '1' =>  'SMTP'
                );

                $parameter_conf['param_id'] = $parameter->getId();
                $parameter_conf['key'] = $parameter->getKeyParam();
                $parameter_conf['value'] = $parameter->getValueParam();
                $parameter_conf['clase'] = substr($parameter->getKeyParam(), 7);
                $parameter_conf['id'] = str_replace('.', '_',$parameter->getKeyParam());

                switch (substr($parameter->getKeyParam(), strpos($parameter->getKeyParam(), 'Config.')+7)) {
                    case 'factory.class':

                    case 'mail.bcc':
                    case 'mail.from':
                    case 'mail.template':
                    case 'accionista.accionesMin':
                    case 'mail.user':
                    case 'mail.password':
                    case 'mail.port':
                    case 'mail.server':
                        $parameter_conf['config'] = $parameter->getValueParam();
                        break;
                    case 'mail.workFlow':
                        $parameter_conf['config'] = $parameter->getValueParam() ? 'Activated' : 'Deactivated';
                        break;
                    case 'mail.transport':
                        $parameter_conf['config'] = $mail_parameters[$parameter->getValueParam()];
                        break;
                    default:
                        $parameter_conf['config'] = $parameter->getValueParam();
                        break;
                }
                if (strpos($parameter->getKeyParam(), '.enable') !== false)
                {
                    $parameter_conf['config'] = $parameter->getValueParam() ? 'Activated' : 'Deactivated';
                }
                $parameters_config[] = $parameter_conf;
            }
        }
        $form_config = array();
        $form_config['clase'] = $this->getWidget(
                'text',
                'clase',
                ''
                );
        $form_config['config'] = $this->getWidget(
                'text',
                'config',
                ''
                );

        return array('parameters_config' => $parameters_config, 'form_config' => $form_config, 'platform' => $platform);
    }

    /**
     * @Route("/editparametersdefaultstate" ,name="_configplatform_edit_parameters_defaultstates")
     * @Template()
     */
    public function editParametersDefaultStatesAction()
    {
        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $platform_id = $request->get('platform_id');
        $platform = $this->getEm($platform_id);
        $em = $this->getDoctrine()->getManager();

        $parameters = $em->getRepository('Lugh\WebAppBundle\Entity\Parametros')->findAll();

        $parameters_default_states = array();

        foreach ($parameters as $parameter) {
            if (strpos($parameter->getKeyParam(), '.default.') != false && strpos($parameter->getKeyParam(), '.state') != false)
            {

                $parameter_default_state['param_id'] = $parameter->getId();
                $parameter_default_state['key'] = $parameter->getKeyParam();
                $parameter_default_state['value'] = $parameter->getValueParam();
                $parameter_default_state['id'] = str_replace('.', '_',$parameter->getKeyParam());
                $parameter_default_state['clase'] = substr($parameter->getKeyParam(), 0, strpos($parameter->getKeyParam(), '.default.'));
                $parameter_default_state['default_state'] = $this->getStates()[$this->getValueConversion(substr($parameter->getKeyParam(), strpos($parameter->getKeyParam(), '.default')+9))[$parameter->getValueParam()]];

                $parameters_default_states[] = $parameter_default_state;
            }
        }
        $form_default_state = array();
        $form_default_state['clase'] = $this->getWidget(
                'choice',
                'clase',
                '',
                array('choices' => $this->getClasses('state'))
                );
        $form_default_state['default_state'] = $this->getWidget(
                'choice',
                'defaultstate',
                '',
                array('choices' => $this->getStates())
                );

        return array('parameters_default_states' => $parameters_default_states, 'form_default_state' => $form_default_state, 'platform' => $platform);
    }

    /**
     * @Route("/editparameterstime" ,name="_configplatform_edit_parameters_time")
     * @Template()
     */
    public function editParametersTimeAction()
    {
        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $platform_id = $request->get('platform_id');
        $platform = $this->getEm($platform_id);
        $em = $this->getDoctrine()->getManager();

        $parameters = $em->getRepository('Lugh\WebAppBundle\Entity\Parametros')->findAll();
        $parameters_time = array();

        foreach ($parameters as $parameter) {
            if (strpos($parameter->getKeyParam(), '.time.') != false)
            {
                $actions = strpos($parameter->getKeyParam(), '.activate') != false ? $this->getActions('activate') : $this->getActions('state');
                $valueJSON = json_decode($parameter->getValueParam(), true);
                $parameter_time['param_id'] = $parameter->getId();
                $parameter_time['id'] = str_replace('.', '_',$parameter->getKeyParam());
                $parameter_time['key'] = $parameter->getKeyParam();
                $parameter_time['value'] = $parameter->getValueParam();
                $parameter_time['clase'] = substr($parameter->getKeyParam(), 0, strpos($parameter->getKeyParam(), '.time'));
                $parameter_time['action'] = substr($parameter->getKeyParam(), strpos($parameter->getKeyParam(), '.time')+ 6);
                $parameter_time['from'] = isset($valueJSON['from']) ? $valueJSON['from'] : '';
                $parameter_time['to'] = isset($valueJSON['to']) ? $valueJSON['to'] : '';

                $parameters_time[] = $parameter_time;
            }
        }
        $form_time = array();
        $form_time['clase'] = $this->getWidget(
                'choice',
                'clase',
                '',
                array('choices' => $this->getClasses())
                );

        $form_time['action'] = $this->getWidget(
                'choice',
                'action',
                '',
                array('choices' => $this->getActions())
                );

        $form_time['from_date'] = $this->getWidget(
                'date',
                'from_date',
                new \Datetime(),
                array('widget' => 'single_text')
                );
        $form_time['from_time'] = $this->getWidget(
                'time',
                'from_time',
                new \Datetime(),
                array('widget' => 'single_text', 'with_seconds' => true)
                );

        $form_time['from_activate'] = $this->getWidget(
                'checkbox',
                'fromactivate',
                true,
                array('value' => 'activate')
                );

        $form_time['to_date'] = $this->getWidget(
                'date',
                'to_date',
                new \Datetime(),
                array('widget' => 'single_text')
                );
        $form_time['to_time'] = $this->getWidget(
                'time',
                'to_time',
                new \Datetime(),
                array('widget' => 'single_text', 'with_seconds' => true)
                );

        $form_time['to_activate'] = $this->getWidget(
                'checkbox',
                'toactivate',
                true,
                array('value' => 'activate')
                );

        return array('parameters_time' => $parameters_time, 'form_time' => $form_time,'platform' => $platform);
    }

    /**
     * @Route("/parameters" ,name="_configplatform_parameters")
     * @Template()
     */
    public function contentParametersAction()
    {
        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $platform_id = $request->get('platform_id');
        return array('platform_id' => $platform_id);
    }

    /**
     * @Route("/saveparameters/{platform_id}" ,name="_configplatform_save_parameters")
     * @Template()
     */
    public function saveParametersAction($platform_id)
    {
        $this->getEm($platform_id);
        $em = $this->getDoctrine()->getManager();

        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $params = $request->get('params');

        $form = $params['form'];
        $form_id = $params['id'];

        try {

            foreach ($form_id as $key => $ids) {

                switch ($key) {

                    case 'time':
                        foreach ($ids as $id) {
                            $parameter = $em->getRepository('Lugh\WebAppBundle\Entity\Parametros')->findOneBy(array('key_param' => str_replace('_', '.', $id)));
                            $key_param = $form['clase_' . $id] . '.time.' . $form['action_' . $id];
                            $value_param = array();
                            if (isset($form['fromactivate_' . $id]))
                            {
                                $value_param['from'] = date('d-m-Y', strtotime($form['from_' . $id]['date'])) . ' ' . $form['from_' . $id]['time'];
                            }
                            if (isset($form['toactivate_' . $id]))
                            {
                                $value_param['to'] = date('d-m-Y', strtotime($form['to_' . $id]['date'])) . ' ' . $form['to_' . $id]['time'];
                            }
                            $value_param = json_encode($value_param);
                            $parameter->setKeyParam($key_param);
                            $parameter->setValueParam($value_param);
                            $em->persist($parameter);
                        }

                        break;
                    case 'default_state':
                        foreach ($ids as $id) {
                            $parameter = $em->getRepository('Lugh\WebAppBundle\Entity\Parametros')->findOneBy(array('key_param' => str_replace('_', '.', $id)));
                            $key_param = $form['clase_' . $id] . '.default.state';
                            $value_param = $form['defaultstate_' . $id];
                            if ($form['defaultstate_' . $id] == '10' || $form['defaultstate_' . $id] == '11')
                            {
                                $key_param = $form['clase_' . $id] . '.default.locked';
                                $value_param = substr($form['defaultstate_' . $id], -1,1);
                            }
                            if ($form['defaultstate_' . $id] == '20' || $form['defaultstate_' . $id] == '11')
                            {
                                $key_param = $form['clase_' . $id] . '.default.enabled';
                                $value_param = substr($form['defaultstate_' . $id], -1,1);
                            }
                            $parameter->setKeyParam($key_param);
                            $parameter->setValueParam($value_param);
                            $em->persist($parameter);
                        }

                        break;
                    case 'config':
                        foreach ($ids as $id) {
                            if($id == 'Config_langs_active'){
                                $langs = $this->get('lugh.langs.db_connection')->from($platform_id);

                                $active_langs = array_fill_keys(array_keys($langs),0);
                                foreach($form['config_' . $id] as $lang_index){
                                    $active_langs[array_keys($langs)[$lang_index]] = 1;
                                }

                                $form['config_' . $id] = json_encode($active_langs); //Overwrite multichoice array to json lang format
                            }

                            $parameter = $em->getRepository('Lugh\WebAppBundle\Entity\Parametros')->findOneBy(array('key_param' => str_replace('_', '.', $id)));
                            $key_param = str_replace('_', '.', $id);
                            if (isset($form['config_' . $id]))
                            {
                                 $value_param = $form['config_' . $id] == 'activate' ? '1' : $form['config_' . $id];
                            }
                            else {
                                $value_param = '0';
                            }
                            $parameter->setValueParam($value_param);
                            $em->persist($parameter);
                        }

                        break;
                    case 'options':
                        foreach ($ids as $id) {
                            $parameter = $em->getRepository('Lugh\WebAppBundle\Entity\Parametros')->findOneBy(array('key_param' => str_replace('_', '.', $id)));
                            $key_param = str_replace('_', '.', $id);
                            $value_param = $form['option_' . $id];
                            $parameter->setValueParam($value_param);
                            $em->persist($parameter);
                        }

                        break;
                    case 'file':
                        $id = $ids[0];
                        $parameter = $em->getRepository('Lugh\WebAppBundle\Entity\Parametros')->findOneBy(array('key_param' => str_replace('_', '.', $id)));
                        $key_param = str_replace('_', '.', $id);
                        $value_param = array();
                        $value_param['r'] = explode(chr(13).chr(10), $form['file_read_' . $id]);
                        array_pop($value_param['r']);
                        $value_param['w'] = $form['file_write_' . $id];
                        $value_param = json_encode($value_param);
                        $parameter->setValueParam($value_param);
                        $em->persist($parameter);
                        break;

                    case 'stats':

                        foreach ($ids as $id) {
                            $key = str_replace('_', '.', $id);
                            $key = str_replace('-', '_', $key);

                            $parameter = $em->getRepository('Lugh\WebAppBundle\Entity\Parametros')->findOneBy(array('key_param' => $key));
                            $value_param = $form['stats_api_' . $id];
                            $parameter->setValueParam($value_param);
                            $em->persist($parameter);
                        }

                        break;
                    case 'junta':

                        foreach ($ids as $id) {
                            $key = str_replace('_', '.', $id);
                            $key = str_replace('-', '_', $key);

                            $parameter = $em->getRepository('Lugh\WebAppBundle\Entity\Parametros')->findOneBy(array('key_param' => $key));
                            //$value_param = $form['juntas_api_' . $id];
                            if (isset($form['juntas_api_' . $id]))
                            {
                                 $value_param = $form['juntas_api_' . $id] == 'activate' ? '1' : $form['juntas_api_' . $id];
                            }
                            else {
                                $value_param = '0';
                            }
                            $parameter->setValueParam($value_param);
                            $em->persist($parameter);
                        }

                        break;
                    default:
                        break;
                }
            }

            $em->flush();
        } catch (Exception $exc) {
            return new Response(json_encode(array('error'=> $exc->getMessage())));
        }
        return new Response(json_encode(array('success'=> '1')));
    }

    /**
     * @Route("/addparameter/{platform_id}" ,name="_configplatform_add_parameter")
     * @Template()
     */
    public function addParameterAction($platform_id)
    {
        $this->getEm($platform_id);
        $em = $this->getDoctrine()->getManager();

        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $params = $request->get('params');
        $type = $request->get('type');
        $form = $params['form'];
        try {

            switch ($type) {
                case 'time':
                    if (!isset($form['action']) || $form['action']=='')
                    {
                        throw new Exception("Action Blank");
                    }
                    if (!isset($form['clase']) || $form['clase']=='')
                    {
                        throw new Exception("Clase Blank");
                    }
                    $parameter = new Parametros();
                    $key_param = $form['clase'] . '.time.' . $form['action'];
                    $value_param = array();
                    if (isset($form['fromactivate']))
                    {
                        $value_param['from'] = date('d-m-Y', strtotime($form['from_date'])) . ' ' . $form['from_time'];
                    }
                    if (isset($form['toactivate']))
                    {
                        $value_param['to'] = date('d-m-Y', strtotime($form['to_date'])) . ' ' . $form['to_time'];
                    }
                    $value_param = json_encode($value_param);
                    $parameter->setKeyParam($key_param);
                    $parameter->setValueParam($value_param);
                    break;
                case 'default_state':

                    if (!isset($form['defaultstate']) || $form['defaultstate']=='')
                    {
                        throw new Exception("Default State Blank");
                    }
                    if (!isset($form['clase']) || $form['clase']=='')
                    {
                        throw new Exception("Clase Blank");
                    }
                    $parameter = new Parametros();
                    $key_param = $form['clase'] . '.default.state';
                    $value_param = $form['defaultstate'];
                    if ($form['defaultstate'] == '10' || $form['defaultstate'] == '11')
                    {
                        $key_param = $form['clase'] . '.default.locked';
                        $value_param = substr($form['defaultstate'], -1,1);
                    }
                    if ($form['defaultstate'] == '20' || $form['defaultstate'] == '11')
                    {
                        $key_param = $form['clase'] . '.default.enabled';
                        $value_param = substr($form['defaultstate'], -1,1);
                    }
                    $parameter->setKeyParam($key_param);
                    $parameter->setValueParam($value_param);
                    break;
                case 'options':
                    if (!isset($form['options']) || $form['options']=='')
                    {
                        throw new Exception("Config Blank");
                    }
                    $parameter = new Parametros();
                    $key_param = 'Options.' . $form['options'];
                    $value_param = $form['valueoption'];
                    $parameter->setKeyParam($key_param);
                    $parameter->setValueParam($value_param);
                    break;
                case 'config':
                    if (!isset($form['config']) || $form['config']=='')
                    {
                        throw new Exception("Config Blank");
                    }
                    $parameter = new Parametros();
                    $key_param = 'Config.' . $form['clase'];
                    $value_param = $form['config'];
                    $parameter->setKeyParam($key_param);
                    $parameter->setValueParam($value_param);
                    break;
                default:
                    break;
            }

            if ($em->getRepository('Lugh\WebAppBundle\Entity\Parametros')->findOneBy(array('key_param' => $key_param)) != null)
            {
                throw new Exception("Duplicate Param");
            }
            $em->persist($parameter);
            $em->flush();
        } catch (Exception $exc) {
            return new Response(json_encode(array('error'=> $exc->getMessage())));
        }
        return new Response(json_encode(array('success'=> '1')));
    }

    /**
     * @Route("/removeparameter" ,name="_configplatform_remove_parameter")
     * @Template()
     */
    public function removeParameterAction()
    {
        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $em = $this->getDoctrine()->getManager();
        try {
            $parameter = $em->getRepository('Lugh\WebAppBundle\Entity\Parametros')->find($request->get('id'));
            $em->remove($parameter);
            $em->flush();
        } catch (\Exception $exc) {
            return new Response(json_encode(array('error'=> $exc->getMessage())));
        }
        return new Response(json_encode(array('success'=> '1')));
    }

    /**
     * @Route("/parametersfilecontent" ,name="_configplatform_parameters_file_content")
     * @Template()
     */
    public function parametersFileContentAction()
    {
        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $platform_id = $request->get('platform_id');
        $platform = $this->getEm($platform_id);
        $em = $this->getDoctrine()->getManager();

        $parameters = $em->getRepository('Lugh\WebAppBundle\Entity\Parametros')->findAll();

        $parameters_file = array();

        foreach ($parameters as $parameter) {
            if (strpos($parameter->getKeyParam(), 'Directory.fileupload.storages') !== false)
            {
                $valueJSON = json_decode($parameter->getValueParam(), true);
                $parameters_file['key'] = $parameter->getKeyParam();
                $parameters_file['value'] = $parameter->getValueParam();
                $parameters_file['clase'] = $parameter->getKeyParam();
                $parameters_file['id'] = str_replace('.', '_',$parameter->getKeyParam());
                $read_text = '';
                foreach ($valueJSON['r'] as $value) {
                    $read_text .= $value . chr(10);
                }
                $parameters_file['read'] = $this->getWidget('textarea', 'file_read_' . $parameters_file['id'], $read_text);
                $parameters_file['write'] = $this->getWidget('text', 'file_write_' . $parameters_file['id'], $valueJSON['w']);
            }
        }
        return array('parameter_file' => $parameters_file, 'platform' => $platform);
    }

    /**
     * @Route("/parametersoptionscontent" ,name="_configplatform_parameters_options_content")
     * @Template()
     */
    public function parametersOptionsContentAction()
    {
        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $platform_id = $request->get('platform_id');
        $platform = $this->getEm($platform_id);
        $em = $this->getDoctrine()->getManager();

        $parameters = $em->getRepository('Lugh\WebAppBundle\Entity\Parametros')->findAll();

        $parameters_options = array();

        foreach ($parameters as $parameter) {
            if (strpos($parameter->getKeyParam(), 'Options.') !== false)
            {
                $parameter_opt['key'] = $parameter->getKeyParam();
                $parameter_opt['value'] = $parameter->getValueParam();
                $parameter_opt['clase'] = substr($parameter->getKeyParam(), 8);
                $parameter_opt['id'] = str_replace('.', '_',$parameter->getKeyParam());

                $parameter_opt['option'] = $this->getWidget(
                        'choice',
                        'option_' . $parameter_opt['id'],
                        $parameter->getValueParam(),
                        array('choices' => $this->getOptions($parameter_opt['clase']))
                        );

                $parameters_options[] = $parameter_opt;
            }
        }
        return array('parameters_options' => $parameters_options, 'platform' => $platform);
    }

    /**
     * @Route("/parametersconfigcontent" ,name="_configplatform_parameters_config_content")
     * @Template()
     */
    public function parametersConfigContentAction()
    {
        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $platform_id = $request->get('platform_id');
        $platform = $this->getEm($platform_id);
        $em = $this->getDoctrine()->getManager();

        $parameters = $em->getRepository('Lugh\WebAppBundle\Entity\Parametros')->findAll();

        $parameters_config = array();

        foreach ($parameters as $parameter) {
            if (strpos($parameter->getKeyParam(), 'Config') !== false && strpos($parameter->getKeyParam(), '.Av.') == false)
            {
                $parameter_conf['key'] = $parameter->getKeyParam();
                $parameter_conf['value'] = $parameter->getValueParam();
                $parameter_conf['clase'] = substr($parameter->getKeyParam(), 7);
                $parameter_conf['id'] = str_replace('.', '_',$parameter->getKeyParam());

                switch (substr($parameter->getKeyParam(), strpos($parameter->getKeyParam(), 'Config.')+7)) {

                    //MultipleChoice Input
                    case 'langs.active':{
                        $langs = $this->get('lugh.langs.db_connection')->from($platform_id);
                        /*json decoded*/    $a_json = json_decode($parameter->getValueParam(),true);
                        /*default options*/ $selected_options = array();

                        for($i = 0; $i < sizeof($langs); $i++){
                            $selected_options[array_keys($langs)[$i]] = $langs[array_keys($langs)[$i]] * $i;
                        }

                        $parameter_conf['config'] = $this->getWidget(
                            /*type */'choice',
                            /*name */'config_'.$parameter_conf['id'],
                            /*data */$selected_options,
                            /*extra*/array('choices' => array_keys($langs), 'multiple' => true)
                        );
                        break;
                    }

                    //Text Input
                    case 'factory.class':

                    case 'mail.bcc':
                    case 'mail.from':
                    case 'mail.template':
                    case 'accionista.accionesMin':
                    case 'mail.user':
                    case 'mail.password':
                    case 'mail.port':
                    case 'mail.server':
                        $parameter_conf['config'] = $this->getWidget('text', 'config_' . $parameter_conf['id'], $parameter->getValueParam());
                        break;

                    //Checkbox Input
                    /**/
                    case 'register.alertsTop':
                    case 'register.alertsField':
                    case 'require.LOPD':
                    case 'require.username':
                    case 'require.email':
                    case 'require.tipo-persona':
                    case 'require.name':
               /*0*/case 'require.doca':
               /*0*/case 'require.docb':
               /*0*/case 'require.doca-user':
               /*0*/case 'require.doca-certificate':
               /*0*/case 'require.docb-user':
               /*0*/case 'require.docb-certificate':
                    case 'require.tipo-doc':
                    case 'require.numero-doc':
               /*0*/case 'require.telephone':
                    case 'cookies.message':
                    case 'delegation.require.nombre':
                    case 'delegation.require.tipodoc':
                    case 'delegation.require.numdoc':
               /*0*/case 'delegation.hide.nombre':
               /*0*/case 'delegation.hide.tipodoc':
               /*0*/case 'delegation.hide.numdoc':
               /*0*/case 'delegation.hide.presidente':
                    case 'delegation.hide.secretary':
                    case 'delegation.hide.listado':
                    case 'delegation.hide.persona':
                    case 'shares.minSharesBlock':
               /*0*/case 'shares.sharesBlock':
                    case 'vote.minVotesBlock':
                    case 'instructions.minVotesBlock':
                    case 'vote.maxVotesBlock':
                    case 'instructions.maxVotesBlock':
                    case 'vote.loadPreviousVote':
               /*0*/case 'vote.show.substitution':
               /*0*/case 'vote.show.absAdicional':
                    case 'platform.logo':
                    case 'Av.opentargetmode':
                    case 'foro.allowproposals':
                    case 'accionista.check.fichero':
                    case 'junta.workFlow':
                    /**/
                    case 'mail.workFlow':
                        $parameter_conf['config'] = $this->getWidget('checkbox', 'config_' . $parameter_conf['id'], $parameter->getValueParam() ? true : false, array('value' => 'activate'));
                        break;
                    case 'mail.transport':
                        $parameter_conf['config'] = $this->getWidget('choice', 'config_' . $parameter_conf['id'], $parameter->getValueParam(), array('choices'=>array('0'=>'SendMail','1'=>'SMTP')));
                        break;
                    default:
                        $parameter_conf['config'] = $this->getWidget('text', 'config_' . $parameter_conf['id'], $parameter->getValueParam());
                        break;
                }
                if (strpos($parameter->getKeyParam(), '.enable') !== false)
                {
                    $parameter_conf['config'] = $this->getWidget('checkbox', 'config_' . $parameter_conf['id'], $parameter->getValueParam() ? true : false, array('value' => 'activate'));
                }

                $parameters_config[] = $parameter_conf;

            }
        }
        return array('parameters_config' => $parameters_config, 'platform' => $platform);
    }
    
    

    /**
     * @Route("/parametersdefautlstatescontent" ,name="_configplatform_parameters_default_states_content")
     * @Template()
     */
    public function parametersDefaultStatesContentAction()
    {
        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $platform_id = $request->get('platform_id');
        $platform = $this->getEm($platform_id);
        $em = $this->getDoctrine()->getManager();

        $parameters = $em->getRepository('Lugh\WebAppBundle\Entity\Parametros')->findAll();

        $parameters_default_states = array();

        foreach ($parameters as $parameter) {
            if (strpos($parameter->getKeyParam(), '.default.') != false && strpos($parameter->getKeyParam(), '.state') != false)
            {
                $parameter_default_state['key'] = $parameter->getKeyParam();
                $parameter_default_state['value'] = $parameter->getValueParam();
                $parameter_default_state['id'] = str_replace('.', '_',$parameter->getKeyParam());
                $parameter_default_state['clase'] = $this->getWidget(
                        'choice',
                        'clase_' . $parameter_default_state['id'],
                        substr($parameter->getKeyParam(), 0, strpos($parameter->getKeyParam(), '.default.')),
                        array('choices' => $this->getClasses('state'))
                        );
                $parameter_default_state['default_state'] = $this->getWidget(
                        'choice',
                        'defaultstate_' .$parameter_default_state['id'],
                        $this->getValueConversion(substr($parameter->getKeyParam(), strpos($parameter->getKeyParam(), '.default')+9))[$parameter->getValueParam()],
                        array('choices' => $this->getStates(substr($parameter->getKeyParam(), strpos($parameter->getKeyParam(), '.default')+9)),'empty_value' => false)
                        );

                $parameters_default_states[] = $parameter_default_state;
            }
        }
        return array('parameters_default_states' => $parameters_default_states, 'platform' => $platform);
    }

    /**
     * @Route("/parameterstimecontent" ,name="_configplatform_parameters_time_content")
     * @Template()
     */
    public function parametersTimeContentAction()
    {
        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $platform_id = $request->get('platform_id');
        $platform = $this->getEm($platform_id);
        $em = $this->getDoctrine()->getManager();

        $parameters = $em->getRepository('Lugh\WebAppBundle\Entity\Parametros')->findAll();
        $parameters_time = array();

        foreach ($parameters as $parameter) {
            if (strpos($parameter->getKeyParam(), '.time.') != false)
            {
                $actions = strpos($parameter->getKeyParam(), '.activate') != false ? $this->getActions('activate') : $this->getActions('state');
                $valueJSON = json_decode($parameter->getValueParam(), true);
                $parameter_time['id'] = str_replace('.', '_',$parameter->getKeyParam());
                $parameter_time['key'] = $parameter->getKeyParam();
                $parameter_time['value'] = $parameter->getValueParam();
                $parameter_time['clase'] = $this->getWidget(
                        'choice',
                        'clase_' . $parameter_time['id'],
                        substr($parameter->getKeyParam(), 0, strpos($parameter->getKeyParam(), '.time')),
                        array('choices' => $this->getClasses())
                        );

                $parameter_time['action'] = $this->getWidget(
                        'choice',
                        'action_' . $parameter_time['id'],
                        substr($parameter->getKeyParam(), strpos($parameter->getKeyParam(), '.time')+ 6),
                        array('choices' => $actions, 'empty_value' => false)
                        );

                $parameter_time['from'] = $this->getWidget(
                        'datetime',
                        'from_' . $parameter_time['id'],
                        new \Datetime(isset($valueJSON['from']) ? $valueJSON['from'] : ''),
                        array('date_widget' => 'single_text', 'time_widget' => 'single_text', 'with_seconds' => true)
                        );

                $parameter_time['from_activate'] = $this->getWidget(
                        'checkbox',
                        'fromactivate_' . $parameter_time['id'],
                        isset($valueJSON['from']) ? true : false,
                        array('value' => 'activate')
                        );

                $parameter_time['to'] = $this->getWidget(
                        'datetime',
                        'to_' . $parameter_time['id'],
                        new \Datetime(isset($valueJSON['to']) ? $valueJSON['to'] : ''),
                        array('date_widget' => 'single_text', 'time_widget' => 'single_text', 'with_seconds' => true)
                        );

                $parameter_time['to_activate'] = $this->getWidget(
                        'checkbox',
                        'toactivate_' . $parameter_time['id'],
                        isset($valueJSON['to']) ? true : false,
                        array('value' => 'activate')
                        );

                $parameters_time[] = $parameter_time;
            }
        }

        return array('parameters_time' => $parameters_time, 'platform' => $platform);
    }

    /**
     * @Route("/content" ,name="_configplatform_content")
     * @Template()
     */
    public function contentAction()
    {
        return array();
    }

    /**
     * @Route("/platformscontent" ,name="_configplatform_platforms_content")
     * @Template()
     */
    public function platformsContentAction()
    {
        $em = $this->getDoctrine()->getManager('db_connection');
        $platforms =  $em->getRepository('Lugh\DbConnectionBundle\Entity\Auth')->findAll();
        $templates =  $em->getRepository('Lugh\DbConnectionBundle\Entity\Template')->findAll();
        return array('platforms' => $platforms, 'templates' => $templates, 'verifnum' => uniqid());
    }
    /**
     * @Route("/reset" ,name="_configplatform_reset")
     * @Template()
     */
    public function resetAction()
    {
        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $form = $request->get('form');

        if ($form['numVerifInput'] != $form['numVerif'])
        {
            return new Response(json_encode(array('success'=> '0')));
        }
        $platform_id = $form['platform_id'];
        $this->getEm($platform_id);
        $em = $this->getDoctrine()->getManager();
        
        
        /*$registros = $em->getRepository('Lugh\WebAppBundle\Entity\Registro')->findAll();
        foreach($registros as $registro){
            $em->remove($registro);
        }
        $em->flush();
        
        $desertions = $em->getRepository('Lugh\WebAppBundle\Entity\Desertion')->findAll();
        foreach($desertions as $desertion){
            $em->remove($desertion);
        }
        $em->flush();*/
        
        $accionistas = $em->getRepository('Lugh\WebAppBundle\Entity\Accionista')->findAll();
        foreach ($accionistas as $accionista) {
            $acciones = $em->getRepository('Lugh\WebAppBundle\Entity\Accion')->findByAccionista($accionista);
            
            foreach ($acciones as $accion) {
                
                $accion->setAccionAnterior(null);
                $accion->setAccionPosterior(null);
                if ($accion::nameClass == 'Delegacion') $accion->setDelegadoNull();

                $em->persist($accion);
                $em->flush();

            }
            
            $accesos = $em->getRepository('Lugh\WebAppBundle\Entity\Acceso')->findByAccionista($accionista);
            
            foreach ($accesos as $acceso) {
                
                $acceso->setAccesoAnterior(null);
                $acceso->setAccesoPosterior(null);
                $em->persist($acceso);
                $em->flush();

            }
            
            $em->remove($accionista);
        }

        $communiques = $em->getRepository('Lugh\WebAppBundle\Entity\Communique')->findAll();
        foreach ($communiques as $communique) {
            $em->remove($communique);
        }

        $delegados = $em->getRepository('Lugh\WebAppBundle\Entity\Delegado')->findAll();
        foreach ($delegados as $delegado) {
            $em->remove($delegado);
        }

        $logmails = $em->getRepository('Lugh\WebAppBundle\Entity\LogMail')->findAll();
        foreach ($logmails as $logmail) {
            $em->remove($logmail);
        }
        
        $junta = $em->getRepository('Lugh\WebAppBundle\Entity\Junta')->findAll()[0];
        $junta->setState(StateClass::stateConfiguracion);
        $junta->setAcreditacionEnabled(false);
        $junta->setVotacionEnabled(false);
        $junta->setPreguntasEnabled(false);
        $junta->setLiveEnabled(false);
        $junta->setAbandonoEnabled(false);
        
        
        $em->flush();

        return new Response(json_encode(array('success'=> '1')));
    }
    /**
     * @Route("/statistics" ,name="_configplatform_statistics")
     * @Template()
     */
    public function statisticsAction()
    {
        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $platform_id = $request->get('platform_id');
        
        $auth = $this->getEM($platform_id);
        $apps = array(
            'voto'      => $auth->getVoto(),
            'foro'      => $auth->getForo(),
            'derecho'   => $auth->getDerecho()
        );

        $site_id = $this->get('lugh.parameters')->getByKey('stats.api.site_id','1');
        //$apps    = json_decode($this->get('lugh.parameters')->getByKey('Accionista.default.apps'),true);

        $start = $request->get('startdate',null);
        $end   = $request->get('enddate',null);
        $comparativa = $request->get('databaseName', null);

        $title    = 'Plataformas electrónicas';
        $platform = '';
        if($apps['foro'] == 1 && $apps['voto'] == 0 && $apps['derecho'] == 0){
            $title = 'Foro electrónico';
            $platform = 'foro electrónico';
        }
        if($apps['foro'] == 0 && $apps['voto'] == 1 && $apps['derecho'] == 0){
            $title = 'Voto electrónico';
            $platform = 'voto electrónico';
        }
        if($apps['foro'] == 0 && $apps['voto'] == 0 && $apps['derecho'] == 1){
            $title = 'Derecho de información';
            $platform = 'derecho de información';
        }

        $pages = array(
            new TitlePage($title),
            new UsoPage(
                $platform,
                $site_id,
                $start,
                $end
            ),
            new EstadisticasPage($apps,
                $comparativa,
                $start,
                $end
            )
         );

        $doc = new DocumentoPDF();

        $doc->Output(
            new Header($title),
            new Footer(),
            $pages
        );
        return new Response(json_encode(array('success'=> '1')));
    }

     /**
     * @Route("/parametersoptions/optionvalue/{value}" ,name="_configplatform_options_value", defaults={"value":"UNDEFINED"})
     * @Template()
     */
    public function optionsValueAction($value)
    {
        return array(
            'form_options'  =>  $this->getWidget(
                    'choice',
                    'valueoption',
                    null,
                    array('choices' => $this->getOptions($value), 'empty_value' => false)
                    )
            );
    }


    /**
     * @Route("/parameterspiwikcontent" ,name="_configplatform_parameters_piwik")
     * @Template()
     */
    public function parametersPiwikContentAction(){
        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $platform_id = $request->get('platform_id');
        $platform = $this->getEm($platform_id);
        $em = $this->getDoctrine()->getManager();

        $parameters = $em->getRepository('Lugh\WebAppBundle\Entity\Parametros')->findAll();

        $parameters_stats = array();
        foreach ($parameters as $parameter) {
            if (strpos($parameter->getKeyParam(), 'stats.api.') !== false)
            {
                $parameter_piwik['key'] = $parameter->getKeyParam();
                $parameter_piwik['value'] = $parameter->getValueParam();
                $parameter_piwik['clase'] = str_replace('stats.api.', '',$parameter->getKeyParam());;

                $parameter_piwik['id'] = str_replace('_', '-',$parameter->getKeyParam() );
                $parameter_piwik['id'] = str_replace('.', '_', $parameter_piwik['id'] );



                $parameter_piwik['config']  = $this->getWidget('text', 'stats_api_' . $parameter_piwik['id'] ,  $parameter_piwik['value']);
                $parameters_stats[] = $parameter_piwik;
            }
        }

        return array('parameters_stats' => $parameters_stats, 'platform' => $platform_id);
    }
    /**
     * @Route("/removefileuploads" ,name="_configplatform_remove_parameters_files")
     * @Template()
     */
    public function removefileUploadsContentAction(){
        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $platform_id = $request->get('platform_id');
        $filename   = $request->get('filename');

        $em = $this->getDoctrine()->getManager('db_connection');
        $platform =  $em->getRepository('Lugh\DbConnectionBundle\Entity\Auth')->find($platform_id);

        $kernel = $this->get('kernel');
        $bundle = $kernel->getBundle('LughWebAppBundle');
        $rootPath = $bundle->getPath();
        $host = $platform->getHost();
        $basePath = $rootPath.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'web'.DIRECTORY_SEPARATOR.'bundles'.DIRECTORY_SEPARATOR.'lugh';
        $path = $basePath.DIRECTORY_SEPARATOR.$host.DIRECTORY_SEPARATOR;


        if($path !== false AND is_dir($path)){
            try{
                unlink($path . $filename);
            }
            catch(Exception $exc){
                return new Response($exc.getMessage());
            }

            return new Response('{success: 1}');
        }
        else{
            return new Response('{error: 0}');
        }

    }
    /**
     * @Route("/savefileuploads" ,name="_configplatform_save_parameters_files")
     * @Template()
     */
    public function savefileUploadsContentAction(){
        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $platform_id = $request->get('platform_id');
        $langs = $this->get('lugh.langs.db_connection')->from($platform_id);

        $em = $this->getDoctrine()->getManager('db_connection');
        $platform =  $em->getRepository('Lugh\DbConnectionBundle\Entity\Auth')->find($platform_id);

        $kernel = $this->get('kernel');
        $bundle = $kernel->getBundle('LughWebAppBundle');
        $rootPath = $bundle->getPath();
        $host = $platform->getHost();
        $basePath = $rootPath.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'web'.DIRECTORY_SEPARATOR.'bundles'.DIRECTORY_SEPARATOR.'lugh';


        $path = realpath($basePath.DIRECTORY_SEPARATOR.$host.DIRECTORY_SEPARATOR);
        if(!($path !== false AND is_dir($path))){
            mkdir($basePath.DIRECTORY_SEPARATOR.$host.DIRECTORY_SEPARATOR);
        }

        $allowed = array();
        $allowed[] ='logo';
        $allowed[] ='other_';
        foreach($langs as $lang => $value){
            $allowed[] = 'AvisoLegal';// . $lang;
            $allowed[] = 'Reglamento';// . $lang;
        }

        if (!empty($_FILES)) {
            //logo
            foreach( $allowed as $filename ){
                if(isset($_FILES[$filename]) && $_FILES[$filename]['error'] == 0){
                    //$uploadfile = $basePath.DIRECTORY_SEPARATOR.$host.DIRECTORY_SEPARATOR.$_FILES[$filename]['name'];
                    $uploadfile = $basePath.DIRECTORY_SEPARATOR.$host.DIRECTORY_SEPARATOR.$_FILES[$filename]['name'];
                    //$uploadfile = $basePath.DIRECTORY_SEPARATOR.$style.DIRECTORY_SEPARATOR.'img'.DIRECTORY_SEPARATOR.'logo.png';

                    if (move_uploaded_file($_FILES[$filename]['tmp_name'], $uploadfile)) {
                        if($filename == "logo"){
                            $this->get('lugh.image.db_connection')->resizeImage($uploadfile,220,80);
                        }
                        return new Response('{success:1}');
                    } else {
                        return new Response('{error:0}');
                    }
                }

            }

            return new Response('{error:-1}');
        }else{
            $result = array();
            $storeFolder = '';
            //logo
            $widget = $request->get('widget');

            $storeFolder = $basePath.DIRECTORY_SEPARATOR.$host.DIRECTORY_SEPARATOR;
            $files = scandir($storeFolder);                 //1
            if ( false!==$files )
            {
                foreach ( $files as $file )
                {
                    if ( '.'!=$file && '..'!=$file)
                    {                                       //2
                        if ( strpos($widget,'logo') !== false && strpos($file,'logo') !== false )
                        {
                            $obj['name'] = $file;
                            $obj['size'] = filesize($storeFolder.DIRECTORY_SEPARATOR.$file);
                            $result[] = $obj;
                        }
                        else if ( strpos($widget,'Legal') !== false && strpos($file,'AvisoLegal') !== false )
                        {
                            $obj['name'] = $file;
                            $obj['size'] = filesize($storeFolder.DIRECTORY_SEPARATOR.$file);
                            $result[] = $obj;
                        }
                        else if ( strpos($widget,'Reglamento') !== false && strpos($file,'Reglamento') !== false )
                        {
                            $obj['name'] = $file;
                            $obj['size'] = filesize($storeFolder.DIRECTORY_SEPARATOR.$file);
                            $result[] = $obj;
                        }
                        else if ( strpos($widget,'other') !== false ){

                            $obj['name'] = $file;
                            $obj['size'] = filesize($storeFolder.DIRECTORY_SEPARATOR.$file);

                            if(!(strpos( $file,'logo') !== false || strpos( $file,'Reglamento') !== false || strpos( $file,'AvisoLegal') !== false)){
                                $result[] = $obj;
                            }

                        }

                    }
                }
            }

            header('Content-type: text/json');              //3
            header('Content-type: application/json');
            return new Response(json_encode($result));
        }
    }
    /**
     * @Route("/parametersuploadscontent" ,name="_configplatform_parameters_uploads")
     * @Template()
     */
    public function parametersUploadsContentAction(){
        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $platform_id = $request->get('platform_id');
        $platform = $this->getEm($platform_id);

        $kernel = $this->get('kernel');
        $bundle = $kernel->getBundle('LughWebAppBundle');
        $rootPath = $bundle->getPath();
        $base = $request->server->get('BASE')?$request->server->get('BASE') : '';
        $host = $platform->getHost();
        $basePath = $rootPath.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'web'.DIRECTORY_SEPARATOR.'bundles'.DIRECTORY_SEPARATOR.'lughdbconnection';

        //$em = $this->getDoctrine()->getManager('db_connection');
        //$platform=  $em->getRepository('Lugh\DbConnectionBundle\Entity\Auth')->find($platform_id);
        $host = $platform->getHost();
        //  /bundles/lugh/"host"/
        $langs = $this->get('lugh.langs.db_connection')->from($platform_id);
        $widgets = array();
        //  Logo
        {
            $widget['key']   = 'file.uploads.logo';
            $widget['id']    = 'logo';
            $widget['clase'] = 'Logo Host (mails)';
            //$widget['value'] =  new File();
            //$widget['config'] = $this->getWidget('file', 'file_uploads_' . $widget['id'] ,  $widget['value']/*$widget['value']*/);
            //$widget['config'] = $this->createFormBuilder()->add('file_uploads_' . $widget['id'],  'file')->getForm()->createView();
            $widgets[] = $widget;
        }

        //  AvisoLega_es.pdf AvisoLega_en.pdf AvisoLega_ca.pdf  AvisoLega_gl.pdf
        //foreach($langs as $lang => $value){
            $widget['key']   = 'file_uploads_legal';//_'.$lang;
            $widget['id']    = 'AvisoLegal';//_.$lang;
            $widget['clase'] = 'Legal ';//.strtoupper($lang);
            //$widget['value'] = 'AvisoLega_'.$lang.'.pdf';
            //$widget['config'] = $this->getWidget('file', 'file_uploads_' . $widget['id'] ,  new File('C:\wamp\www\Lugh\logic\src\Lugh\WebAppBundle\Resources\public\images\workspace_base\CertificadosSoportados.pdf')/*$widget['value']*/);
            $widgets[] = $widget;
        //}

        //  Reglamento_es.pdf Reglamento_en.pdf Reglamento_ca.pdf   Reglamento_gl.pdf
        //foreach($langs as $lang => $value){
            $widget['key']   = 'file_uploads_reglamento_';//'.$lang;

            $widget['id']    = 'Reglamento';//'.$lang;
            $widget['clase'] = 'Reglamento ';//' . strtoupper($lang);
            //$widget['value'] = 'Reglamento_'.$lang.'.pdf';
            //$widget['config'] = $this->getWidget('file', 'file_uploads_' . $widget['id'] , new File('C:\wamp\www\Lugh\logic\src\Lugh\WebAppBundle\Resources\public\images\workspace_base\CertificadosSoportados.pdf')/*$widget['value']*/);
            $widgets[] = $widget;
        //}
        //  Multiupload
        //$fi = new FilesystemIterator(__DIR__, FilesystemIterator::SKIP_DOTS);
        //for($i=0; $i < iterator_count($fi); $i++){
            $widget['key']   = 'other_';//'.$i;
            $widget['id']    = 'other_';//'.$i;
            $widget['clase'] = 'Otros ficheros';
            //$widget['value'] = 'other_';//'.$i.'.other';
            //$widget['config'] = $this->getWidget('file', 'file_uploads_' . $widget['id'] , new File('C:\wamp\www\Lugh\logic\src\Lugh\WebAppBundle\Resources\public\images\workspace_base\CertificadosSoportados.pdf')/*$widget['value']*/);
            $widgets[] = $widget;
            //$widgets[] = $widget['config'] = $this->getWidget('text', 'file_uploads_' . $widget['id'] ,  $widget['value']);
        //}
        return array('platform' => $platform_id, 'langs' => $langs, 'widgets' => $widgets, 'host' =>  $host.'/'.$base, "assets" => $host . $basePath);
    }
    
    /**
     * @Route("/parametersjuntacontent" ,name="_configplatform_parameters_junta")
     * @Template()
     */
    public function parametersJuntaContentAction(){
        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $platform_id = $request->get('platform_id');
        $platform = $this->getEm($platform_id);
        $em = $this->getDoctrine()->getManager();

        $parameters = $em->getRepository('Lugh\WebAppBundle\Entity\Parametros')->findAll();

        $parameters_stats = array();
        
        foreach ($parameters as $parameter) {
            if (strpos($parameter->getKeyParam(), 'juntas.api.') !== false)
            {
                $parameter_junta['key'] = $parameter->getKeyParam();
                $parameter_junta['value'] = $parameter->getValueParam();
                $parameter_junta['clase'] = str_replace('juntas.api.', '',$parameter->getKeyParam());;

                $parameter_junta['id'] = str_replace('_', '-',$parameter->getKeyParam() );
                $parameter_junta['id'] = str_replace('.', '_', $parameter_junta['id'] );

                switch (substr($parameter->getKeyParam(), strpos($parameter->getKeyParam(), 'Juntas.')+7)) {

                    //Checkbox Input
                    /**/
                    case 'api.sharesNum':
                        $parameter_junta['config'] = $this->getWidget('checkbox', 'juntas_api_' . $parameter_junta['id'], $parameter->getValueParam() ? true : false, array('value' => 'activate'));
                        break;
                    default:
                        $parameter_junta['config']  = $this->getWidget('text', 'juntas_api_' . $parameter_junta['id'] ,  $parameter_junta['value']);
                        break;
                }

                
                $parameters_stats[] = $parameter_junta;
            }
        }

        return array('parameters_junta' => $parameters_stats, 'platform' => $platform_id);
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
                'global'    =>  'Global',
                'store'     =>  'Store',
                'create'    =>  'Create',
                'get'       =>  'Get',
                'delete'    =>  'Delete',
                'pendiente' =>  'Pendiente',
                'publica'   =>  'Publica',
                'retorna'   =>  'Retorna',
                'rechaza'   =>  'Rechaza'
                ),
            'activate' => array(
                'activate'  => 'Activate'
            )
        );
        return $type == null ? $actions['state']+$actions['activate'] : $actions[$type];
    }

    private function getClasses($type = null)
    {
        $clases = array(
            'state' => array(
                'Platform'          => 'Platform',
                'Voto'              => 'Voto',
                'Foro'              => 'Foro',
                'Derecho'           => 'Derecho',
                'Av'                => 'Asistencia Virtual',
                'AppVoto'           => 'Voto Accionista',
                'AppForo'           => 'Foro Accionista',
                'AppDerecho'        => 'Derecho Accionista',
                'AppAv'             => 'Asistencia Virtual Accionista',
                'Accionista'        => 'Accionista',
                'Accionista_ROLE_USER_CERT'        => 'Accionista Certificado',
                'Accionista_ROLE_USER_FULL'        => 'Accionista user/pass',
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
                'Question'          => 'Ruegos y preguntas',
                'Desertion'         => 'Abandonos',
                'Proposal'          => 'Proposal'
                
            ),
            'others' => array(
                'User'              => 'User',
                'ItemAccionista'    => 'ItemAccionista',
                'Message'           => 'Message',
                'LogMail'           => 'LogMail',
                'Document'          => 'Document',
                'Parametros'        => 'Parametros',
                'Voto'              => 'Voto',
                'VotoPunto'         => 'VotoPunto',
                'Anulacion'         => 'Anulacion',
                'Delegado'          => 'Delegado',
                'PuntoDia'          => 'PuntoDia',
                'OpcionesVoto'      => 'OpcionesVoto',
                'Config'            => 'Config'
            )
        );

        return $type == null ? $clases['state']+$clases['others'] : $clases[$type];

    }

    private function getStates($type = null)
    {
        $states =  array(
            'state' => array(
                '1'   => 'Pendiente',
                '2'   => 'Publico',
                '3'   => 'Retornado',
                '4'   => 'Rechazado'
            ),
            'locked' => array(
                '10'  =>  'Unlocked',
                '11'  =>  'Locked'
            ),
            'enabled' => array(
                '20'  =>  'Disabled',
                '21'  =>  'Enabled'
            )
        );
        return $type == null ? $states['state']+$states['locked']+$states['enabled'] : $states[$type];
    }

    private function getValueConversion($type = null)
    {
        $values =  array(
            'state' => array(
                '1'   => '1',
                '2'   => '2',
                '3'   => '3',
                '4'   => '4'
            ),
            'locked' => array(
                '0'  =>  '10',
                '1'  =>  '11'
            ),
            'enabled' => array(
                '0'  =>  '20',
                '1'  =>  '21'
            )
        );
        return $type == null ? $values['state']+$values['locked']+$values['enabled'] : $values[$type];
    }

    private function getOptions($type = null)
    {
        $values =  array(
            'ratificar.delegacion' => array(
                '0'   => 'Publicar delegación automáticamente',
                '1'   => 'Publicar delegación automáticamente con aviso al delegado',
                '2'   => 'Crear delegación pendiente con ratificación por parte del delegado'
            ),
            'cipher.class' => array(
                'serialize' =>  'Serializado',
                'json'      =>  'JSON',
                'aes'       =>  'AES Cipher'
            ),
            'factory.class' => array(
                'prod'  =>  'Producción',
                'test'  =>  'Test'
            )
        );
        if (!isset($values[$type]))
        {
            return array_combine(array_keys($values),array_keys($values));
        }
        return $values[$type];
    }

    private function getEm($platform_id)
    {
        $em = $this->getDoctrine()->getManager('db_connection');
        $platform =  $em->getRepository('Lugh\DbConnectionBundle\Entity\Auth')->find($platform_id);
        PlatformsManager::switchDb($platform->getDbname());

        return $platform;
    }
    
    

}
