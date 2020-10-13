<?php

namespace Lugh\DbConnectionBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Lugh\DbConnectionBundle\Lib;

/**
 * @Route("/addplatform")
 */
class AddPlatformController extends Controller
{
    /**
     * @Route("/" ,name="_addplatform_index")
     * @Template()
     */
    public function indexAction()
    {
        return array();
    }
    
    /**
     * @Route("/content" ,name="_addplatform_content")
     * @Template()
     */
    public function contentAction()
    {
        return array();
    }
    
    /**
     * @Route("/platformscontent" ,name="_addplatform_platforms")
     * @Template()
     */
    public function platformsContentAction()
    {
        $em = $this->getDoctrine()->getManager('db_connection');
        $platforms =  $em->getRepository('Lugh\DbConnectionBundle\Entity\Auth')->findAll();
        $templates =  $em->getRepository('Lugh\DbConnectionBundle\Entity\Template')->findAll();
        return array('platforms' => $platforms, 'templates' => $templates);
    }
    
    /**
     * @Route("/setActive/{platform_id}/{platform}", name="_addplatform_setActive")
     * @Template()
     */
    public function setActiveAction($platform_id, $platform)
    {
        
        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $active = $request->get('active');
        switch ($platform) {
            case 'voto':
                $ret = Lib\PlatformsManager::setVotoActive($platform_id, $active);
                break;
            case 'foro':
                $ret = Lib\PlatformsManager::setForoActive($platform_id, $active);
                break;
            case 'derecho':
                $ret = Lib\PlatformsManager::setDerechoActive($platform_id, $active);
                break;
            case 'active':
                $ret = Lib\PlatformsManager::setActiveActive($platform_id, $active);
                break;
            case 'onProductionDates':
                $ret = Lib\PlatformsManager::setProductionActive($platform_id, $active);
                break;
            default:
                break;
        }
        if ($ret)
        {
            return new Response(json_encode(array('success'=> '1')));
        }
        else
        {
            return new Response(json_encode(array('success'=> '0')));
        }
        
    }
    
    /**
     * @Route("/setParam/{platform_id}/{platform}", name="_addplatform_setParam")
     * @Template()
     */
    public function setParamAction($platform_id, $platform)
    {
        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $param = $request->get('param');
        switch ($platform) {
            case 'host':
                $ret = Lib\PlatformsManager::setHostParam($platform_id, $param);
                break;
            case 'dbname':
                $ret = Lib\PlatformsManager::setDbNameParam($platform_id, $param);
                break;
            case 'style':
                $ret = Lib\PlatformsManager::setStyleParam($platform_id, $param);
                break;
            default:
                break;
        }
        if ($ret)
        {
            return new Response(json_encode(array('success'=> '1')));
        }
        else
        {
            return new Response(json_encode(array('success'=> '0')));
        }
        
    }
    
    /**
     * @Route("/formplatform" ,name="_addplatform_form")
     * @Template()
     */
    public function formPlatformAction()
    {
        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $params = $request->get('params');
        $ret = Lib\PlatformsManager::addPlatform($params);
        if ($ret)
        {
            return new Response(json_encode(array('success'=> '1')));
        }
        else
        {
            return new Response(json_encode(array('success'=> '0')));
        }
    }
}
