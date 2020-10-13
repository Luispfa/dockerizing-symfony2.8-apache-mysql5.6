<?php

namespace Lugh\DbConnectionBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Lugh\DbConnectionBundle\Lib;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\ConsoleOutput;

class DefaultController extends Controller
{
    /**
     * @Route("/" , name="_index")
     * @Template()
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * @Route("/content" ,name="_platforms_content_default")
     * @Template()
     */
    public function contentDefaultAction()
    {
        return array();
    }

    /**
     * @Route("/platformscontent" ,name="_platforms_content")
     * @Template()
     */
    public function platformsContentAction()
    {
        $em = $this->getDoctrine()->getManager('db_connection');
        $platforms =  $em->getRepository('Lugh\DbConnectionBundle\Entity\Auth')->findAll();
        $templates =  $em->getRepository('Lugh\DbConnectionBundle\Entity\Template')->findBy(array(), array('name' => 'ASC'));
        return array('platforms' => $platforms, 'templates' => $templates);
    }

    /**
     * @Route("/setActive/{platform_id}/{platform}", name="_platform_setActive")
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
            case 'av':
                $ret = Lib\PlatformsManager::setAVActive($platform_id, $active);
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
     * @Route("/setParam/{platform_id}/{platform}", name="_platform_setParam")
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
     * @Route("/clearCache", name="_clear_cache")
     * @Template()
     */
    public function clearCacheAction(){
        global $kernel;
        if ('AppCache' == get_class($kernel)) {
            $kernel = $kernel->getKernel();
        }
        $application = new Application($kernel);
        $application->setAutoExit(false);
        $command = 'cache:clear --env=prod';
        $input = new StringInput($command);
        $output = new ConsoleOutput();
        try {
            set_time_limit(120);
            $ret = $application->run($input, $output);
            set_time_limit(30);
        } catch (Exception $exc) {
            return new Response(json_encode( array('error'=> $exc->getMessage() )));
        }
        //var_dump($ret);
        if ($ret === 0)
        {
            return new Response(json_encode(array('success'=> '1')));
        }
        else
        {
            return new Response(json_encode(array('success'=> '0')));
        }
    }
}
