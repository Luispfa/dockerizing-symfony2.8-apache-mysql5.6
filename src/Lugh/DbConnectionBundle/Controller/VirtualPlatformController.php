<?php 

namespace Lugh\DbConnectionBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Lugh\DbConnectionBundle\Lib\PlatformsManager;
use Symfony\Component\Config\Definition\Exception\Exception;
use Doctrine\DBAL\DBALException;
use Lugh\WebAppBundle\Entity\Junta;
use Lugh\WebAppBundle\Entity\Parametros;
use Lugh\WebAppBundle\DomainLayer\State\StateClass;

/**
 * @Route("/virtualplatform")
 */
class VirtualPlatformController extends Controller
{

    /**
     * @Route("/" ,name="_virtualplatform_index")
     * @Template()
    */
    public function indexAction()
    {
        return array();
    }

    /**
     * @Route("/parameters" ,name="_virtualplatform_parameters")
     * @Template()
     */
    public function contentParametersAction()
    {
        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $platform_id = $request->get('platform_id');
        return array('platform_id' => $platform_id);
    }

    /**
     * @Route("/content" ,name="_virtualplatform_content")
     * @Template()
     */
    public function contentAction()
    {
        return array();
    }

    /**
     * @Route("/parameterslivecontent" ,name="_virtualplatform_parameters_live")
     * @Template()
     */
    public function parametersLiveContentAction(){
        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $platform_id = $request->get('platform_id');
        $platform = $this->getEm($platform_id);
        $em = $this->getDoctrine()->getManager();

        $parameters = $em->getRepository('Lugh\WebAppBundle\Entity\Parametros')->findAll();

        $parameters_stats = array();
        foreach ($parameters as $parameter) {
            if (strpos($parameter->getKeyParam(), 'Av.live.') !== false)
            {
                $parameter_av['key'] = $parameter->getKeyParam();
                $parameter_av['value'] = $parameter->getValueParam();
                $parameter_av['clase'] = str_replace('Av.live.', '',$parameter->getKeyParam());;

                $parameter_av['id'] = str_replace('_', '-',$parameter->getKeyParam() );
                $parameter_av['id'] = str_replace('.', '_', $parameter_av['id'] );

                $parameter_av['config']  = $this->getWidget('text', 'stats_api_' . $parameter_av['id'] ,  $parameter_av['value']);
                $parameters_stats[] = $parameter_av;
            }
        }

        return array('parameters_av' => $parameters_stats, 'platform' => $platform_id);
    }        

    /**
     * @Route("/parametersconfigcontent" ,name="_virtualplatform_parameters_config_content")
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
            if (strpos($parameter->getKeyParam(), 'Config.Av') !== false)
            {
                $parameter_conf['key'] = $parameter->getKeyParam();
                $parameter_conf['value'] = $parameter->getValueParam();
                $parameter_conf['clase'] = substr($parameter->getKeyParam(), 7);
                $parameter_conf['id'] = str_replace('.', '_',$parameter->getKeyParam());

                switch (substr($parameter->getKeyParam(), strpos($parameter->getKeyParam(), 'Config.')+7)) {

                    //Checkbox Input
                    /**/
                    case 'Av.opentargetmode':
                    case 'Av.showquestions':
                    case 'Av.showdesertion':
                        $parameter_conf['config'] = $this->getWidget('checkbox', 'config_' . $parameter_conf['id'], $parameter->getValueParam() ? true : false, array('value' => 'activate'));
                        break;
                    default:
                        $parameter_conf['config'] = $this->getWidget('text', 'config_' . $parameter_conf['id'], $parameter->getValueParam());
                        break;
                }

                $parameters_config[] = $parameter_conf;

            }
        }
        return array('parameters_config' => $parameters_config, 'platform' => $platform);
    }
    
    /**
     * @Route("/saveparameters/{platform_id}" ,name="_virtualplatform_save_parameters")
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

                    case 'config':
                        foreach ($ids as $id) {

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
                    case 'states':

                        foreach ($ids as $id) {
                            $parameter = $em->getRepository('Lugh\WebAppBundle\Entity\Parametros')->findOneBy(array('key_param' => str_replace('_', '.', $id)));
                            //$key_param = 'Any.states.' . $form['clase_' . $id];
                            $value_param = array();
                            
                            if (isset($form['acreditacion_' . $id])) {
                                $value_param['acreditacion'] =  $form['acreditacion_' . $id] == 'activate' ? '1' : $form['acreditacion_' . $id];
                            }
                            else {
                                $value_param['acreditacion'] = '0';
                            }
                            
                            if (isset($form['votacion_' . $id])) {
                                $value_param['votacion'] =  $form['votacion_' . $id] == 'activate' ? '1' : $form['votacion_' . $id];
                            }
                            else {
                                $value_param['votacion'] = '0';
                            }
                            
                            if (isset($form['preguntas_' . $id])) {
                                $value_param['preguntas'] =  $form['preguntas_' . $id] == 'activate' ? '1' : $form['preguntas_' . $id];
                            }
                            else {
                                $value_param['preguntas'] = '0';
                            }
                            
                            if (isset($form['live_' . $id])) {
                                $value_param['live'] =  $form['live_' . $id] == 'activate' ? '1' : $form['live_' . $id];
                            }
                            else {
                                $value_param['live'] = '0';
                            }
                            
                            if (isset($form['abandono_' . $id])) {
                                $value_param['abandono'] =  $form['abandono_' . $id] == 'activate' ? '1' : $form['abandono_' . $id];
                            }
                            else {
                                $value_param['abandono'] = '0';
                            }                            
                            
                            $value_param = json_encode($value_param);
                            //$parameter->setKeyParam($key_param);
                            $parameter->setValueParam($value_param);
                            $em->persist($parameter);
                        }

                        break;
                    case 'junta':
                        foreach ($ids as $id) {
                            if (!isset($form['state_' . $id]) || $form['state_' . $id]=='')
                            {
                                throw new Exception("State Blank");
                            }
                            $junta = $em->getRepository('Lugh\WebAppBundle\Entity\Junta')->find(str_replace('_', '-',$id));
                            //$junta->setState($form['state_' . $id]);
                            switch($form['state_' . $id]) {
                                case 1:
                                    $junta->configuracion();
                                    break;
                                case 2:
                                    $junta->convocatoria();
                                    break;
                                case 3:
                                    $junta->prejunta();
                                    break;
                                case 4:
                                    $junta->asistencia();
                                    break;
                                case 5:
                                    $junta->quorumcerrado();
                                    break;
                                case 6:
                                    $junta->votacion();
                                    break;
                                case 7:
                                    $junta->finalizado();
                                    break;
                                
                            }
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
     * @Route("/editparametersconfig" ,name="_virtualplatform_edit_parameters_config")
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
            if (strpos($parameter->getKeyParam(), 'Config.Av') !== false)
            {

                $parameter_conf['param_id'] = $parameter->getId();
                $parameter_conf['key'] = $parameter->getKeyParam();
                $parameter_conf['value'] = $parameter->getValueParam();
                $parameter_conf['clase'] = substr($parameter->getKeyParam(), 7);
                $parameter_conf['id'] = str_replace('.', '_',$parameter->getKeyParam());

                switch (substr($parameter->getKeyParam(), strpos($parameter->getKeyParam(), 'Config.')+7)) {
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
     * @Route("/addparameter/{platform_id}" ,name="_virtualplatform_add_parameter")
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
                case 'config':
                    if (!isset($form['config']) || $form['config']=='')
                    {
                        throw new Exception("Config Blank");
                    }
                    $parameter = new Parametros();
                    $key_param = 'Config.Av.' . $form['clase'];
                    $value_param = $form['config'];
                    $parameter->setKeyParam($key_param);
                    $parameter->setValueParam($value_param);
                    break;
                case 'states':
                    if (!isset($form['clase']) || $form['clase']=='')
                    {
                        throw new Exception("State Blank");
                    }
                    $parameter = new Parametros();
                    $key_param = 'Any.states.' . $form['clase'];
                    $value_param = array();
                    if (isset($form['acreditacion']))
                        $value_param['acreditacion'] = $form['acreditacion'] == 'activate' ? '1' : $form['acreditacion'];
                    else
                        $value_param['acreditacion'] = '0';
                    if (isset($form['votacion']))
                        $value_param['votacion'] = $form['votacion'] == 'activate' ? '1' : $form['votacion'];
                    else
                        $value_param['votacion'] = '0';
                    if (isset($form['preguntas']))
                        $value_param['preguntas'] = $form['preguntas'] == 'activate' ? '1' : $form['preguntas'];
                    else
                        $value_param['preguntas'] = '0';
                    if (isset($form['live']))
                        $value_param['live'] = $form['live'] == 'activate' ? '1' : $form['live'];
                    else
                        $value_param['live'] = '0';
                    if (isset($form['abandono']))
                        $value_param['abandono'] = $form['abandono'] == 'activate' ? '1' : $form['abandono'];
                    else
                        $value_param['abandono'] = '0';
                    $value_param = json_encode($value_param);
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
     * @Route("/removeparameter" ,name="_virtualplatform_remove_parameter")
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
     * @Route("/parametersstatescontent" ,name="_virtualplatform_parameters_states_content")
     * @Template()
     */
    public function parametersStatesContentAction()
    {
        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $platform_id = $request->get('platform_id');
        $platform = $this->getEm($platform_id);
        $em = $this->getDoctrine()->getManager();

        $parameters = $em->getRepository('Lugh\WebAppBundle\Entity\Parametros')->findAll();

        $parameters_config = array();

        foreach ($parameters as $parameter) {
            if (strpos($parameter->getKeyParam(), 'Any.states') !== false)
            {
                $parameter_conf['key'] = $parameter->getKeyParam();
                $parameter_conf['value'] = $parameter->getValueParam();
                $parameter_conf['clase'] = substr($parameter->getKeyParam(), 11);
                $parameter_conf['id'] = str_replace('.', '_',$parameter->getKeyParam());
                
                $actions_json = json_decode($parameter->getValueParam(),true);
                
                $parameter_conf['acreditacion'] = $this->getWidget(
                        'checkbox', 
                        'acreditacion_' . $parameter_conf['id'], 
                        isset($actions_json['acreditacion']) && $actions_json['acreditacion'] == 1 ? true : false, 
                        array('value' => 'activate')
                        );
                
                $parameter_conf['votacion'] = $this->getWidget(
                        'checkbox', 
                        'votacion_' . $parameter_conf['id'], 
                        isset($actions_json['votacion']) && $actions_json['votacion'] == 1 ? true : false, 
                        array('value' => 'activate')
                        );
                
                $parameter_conf['preguntas'] = $this->getWidget(
                        'checkbox', 
                        'preguntas_' . $parameter_conf['id'], 
                        isset($actions_json['preguntas']) && $actions_json['preguntas'] == 1 ? true : false, 
                        array('value' => 'activate')
                        );
                
                $parameter_conf['live'] = $this->getWidget(
                        'checkbox', 
                        'live_' . $parameter_conf['id'], 
                        isset($actions_json['live']) && $actions_json['live'] == 1 ? true : false, 
                        array('value' => 'activate')
                        );
                        
                $parameter_conf['abandono'] = $this->getWidget(
                        'checkbox', 
                        'abandono_' . $parameter_conf['id'], 
                        isset($actions_json['abandono']) && $actions_json['abandono'] == 1 ? true : false, 
                        array('value' => 'activate')
                        );

                $parameters_config[] = $parameter_conf;

            }
        }
        return array('parameters_states' => $parameters_config, 'platform' => $platform);
    }
    
    /**
     * @Route("/parametersjuntacontent" ,name="_virtualplatform_parameters_junta_content")
     * @Template()
     */
    public function parametersJuntaContentAction()
    {
        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $platform_id = $request->get('platform_id');
        $platform = $this->getEm($platform_id);
        $em = $this->getDoctrine()->getManager();

        $junta = $em->getRepository('Lugh\WebAppBundle\Entity\Junta')->findAll();

        $parameters_junta = array();

        foreach ($junta as $parameter) {
            
                $parameter_junta['ide'] = $parameter->getId();
                $parameter_junta['id'] = str_replace('-', '_',$parameter->getId());
                
                $parameter_junta['state'] = $this->getWidget(
                        'choice',
                        'state_' . $parameter_junta['id'],
                        $parameter->getState(),
                        array('choices' => $this->getStates('states'))
                        );
                
                $parameter_junta['actual'] = $this->getStates('states')[$parameter->getState()];
                $parameter_junta['acreditacion'] = $parameter->getAcreditacionEnabled() == 1 ? 'Activo' : 'Inactivo';
                $parameter_junta['votacion'] = $parameter->getVotacionEnabled() == 1 ? 'Activo' : 'Inactivo';
                $parameter_junta['preguntas'] = $parameter->getPreguntasEnabled() == 1 ? 'Activo' : 'Inactivo';
                $parameter_junta['live'] = $parameter->getLiveEnabled() == 1 ? 'Activo' : 'Inactivo';
                $parameter_junta['abandono'] = $parameter->getAbandonoEnabled() == 1 ? 'Activo' : 'Inactivo';
                
                $parameters_junta[] = $parameter_junta;

            
        }
        return array('parameters_junta' => $parameters_junta, 'platform' => $platform);
    }
    
    /**
     * @Route("/editparametersstates" ,name="_virtualplatform_edit_parameters_states")
     * @Template()
     */
    public function editParametersStatesAction()
    {
        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $platform_id = $request->get('platform_id');
        $platform = $this->getEm($platform_id);
        $em = $this->getDoctrine()->getManager();

        $parameters = $em->getRepository('Lugh\WebAppBundle\Entity\Parametros')->findAll();
        $parameters_time = array();
        
        foreach ($parameters as $parameter) {
            
            if (strpos($parameter->getKeyParam(), 'Any.states') !== false)
            {
                $valueJSON = json_decode($parameter->getValueParam(), true);
                $parameter_time['param_id'] = $parameter->getId();
                $parameter_time['id'] = str_replace('.', '_',$parameter->getKeyParam());
                $parameter_time['key'] = $parameter->getKeyParam();
                $parameter_time['value'] = $parameter->getValueParam();
                $parameter_time['clase'] = substr($parameter->getKeyParam(), 11);
                
                $parameter_time['acreditacion'] = isset($valueJSON['acreditacion']) && $valueJSON['acreditacion'] == 1 ? 'Activo' : 'Inactivo';
                $parameter_time['votacion'] = isset($valueJSON['votacion']) && $valueJSON['votacion'] == 1 ? 'Activo' : 'Inactivo';
                $parameter_time['preguntas'] = isset($valueJSON['preguntas']) && $valueJSON['preguntas'] == 1 ? 'Activo' : 'Inactivo';
                $parameter_time['live'] = isset($valueJSON['live']) && $valueJSON['live'] == 1 ? 'Activo' : 'Inactivo';
                $parameter_time['abandono'] = isset($valueJSON['abandono']) && $valueJSON['abandono'] == 1 ? 'Activo' : 'Inactivo';
                
                
                $parameters_time[] = $parameter_time;
            }
        }
        $form_time = array();
        $form_time['clase'] = $this->getWidget(
                'choice',
                'clase',
                '',
                array('choices' => $this->getClasses('states'))
                );

        $form_time['acreditacion'] = $this->getWidget(
                'checkbox',
                'acreditacion',
                true,
                array('value' => 'activate')
                );

        $form_time['votacion'] = $this->getWidget(
                'checkbox',
                'votacion',
                true,
                array('value' => 'activate')
                );
        
        $form_time['preguntas'] = $this->getWidget(
                'checkbox',
                'preguntas',
                true,
                array('value' => 'activate')
                );
        
        $form_time['live'] = $this->getWidget(
                'checkbox',
                'live',
                true,
                array('value' => 'activate')
                );
        
        $form_time['abandono'] = $this->getWidget(
                'checkbox',
                'abandono',
                true,
                array('value' => 'activate')
                );

        return array('parameters_states' => $parameters_time, 'form_states' => $form_time,'platform' => $platform);
    }
    
    private function getEm($platform_id)
    {
        $em = $this->getDoctrine()->getManager('db_connection');
        $platform =  $em->getRepository('Lugh\DbConnectionBundle\Entity\Auth')->find($platform_id);
        PlatformsManager::switchDb($platform->getDbname());

        return $platform;
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

    private function getClasses($type = null)
    {
        $clases = array(
            'states' => array(
                'Configuracion'         => 'Configuración',
                'Convocatoria'          => 'Convocatoria',
                'Prejunta'              => 'Pre Junta',
                'Asistencia'            => 'Asistencia',
                'QuorumCerrado'         => 'Quorum Cerrado',
                'Votacion'              => 'Votacion',
                'Finalizado'            => 'Finalizado',
            )
        );
        
        return $type == null ? $clases['state'] : $clases[$type];

    }
    
    private function getStates($type = null)
    {
        $states =  array(
            'states' => array(
                '1'   => 'Configuracion',
                '2'   => 'Convocatoria',
                '3'   => 'Prejunta',
                '4'   => 'Asistencia',
                '5'   => 'Quorum Cerrado',
                '6'   => 'Votacion',
                '7'   => 'Finalizado'
            ),
            
        );
        return $type == null ? $states['states'] : $states[$type];
    }
}