<?php

namespace Lugh\WebAppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Lugh\WebAppBundle\DomainLayer\State\StateClass;

/**
 * @Route("/workspace")
 */
class WorkSpaceController extends Controller
{

     /**
     * @Route("/main.js", name="_main_app_js")
     * @Template("LughWebAppBundle:Default:mainAppJs.html.twig")
     */
    public function mainAppJsAction()
    {
        $params = array(
            'webroot'           => $this->getRequest()->getBasePath(),
            'apiroot'           => $this->getRequest()->getBaseUrl(),
            'namecontroller'    => 'workspace',
            'apiAddress'        => $this->container->get('lugh.parameters')->getByKey('stats.api.address', 'https://analytics.juntadeaccionistas.es/'),
            'siteId'            => $this->container->get('lugh.parameters')->getByKey('stats.api.site_id', '1'),
            'userName'          => $this->getUser()->getUsername(),
            'lang'              => $this->container->get('lugh.translate.register')->getLocale()
                );
        $response = new \Symfony\Component\HttpFoundation\Response(
            $this->renderView('LughWebAppBundle:Default:mainAppJs.html.twig', $params
        ));
        $response->headers->set('Content-Type', 'application/javascript');
        return $response;
    }
    
    /**
     * @Route("/", name="workspace_homepage")
     * @Template()
     */
    public function indexAction()
    {
        $template = $this->get('lugh.route.template')->getTemplate('/app/', 'index.html');
        return $this->render(
            $template,
            array('title' => $this->container->get('lugh.parameters')->getByKey('Config.platform.title', 'Lugh Electronic Platforms'))
        );
    }
    
    /**
     * @Route("/views/{file}", requirements={"file" = ".+"}, name="app_views_file")
     * @Template()
     */
    public function viewsAction($file)
    {
        
        $template = $this->get('lugh.route.template')->getTemplate('/app/views/', $file);
        return $this->render(
            $template,
            array()
        );
    }
    
    /**
     * @Route("/jsapp/{file}", requirements={"file" = ".+"})
     * @Template()
     */
    public function jsAction($file)
    {
        return $this->getFileContents($file, '/app/', 'getJs', 'application/javascript');   
    }
    
    /**
     * @Route("/css/{file}", requirements={"file" = ".+"}, name="app_css_file")
     * @Template()
     */
    public function cssAction($file)
    {
        return $this->getFileContents($file, '/', 'getCss', 'text/css');   
    }
    
    /**
     * @Route("/images/{file}", requirements={"file" = ".+"}, name="app_images_file")
     * @Template()
     */
    public function imagesAction($file)
    {
        return $this->getFileContents($file, '/', 'getimages');  
    }
    
    /**
     * @Route("/i18n/{file}", requirements={"file" = ".+"})
     * @Template()
     */
    public function i18nAction($file)
    {
        $this->container->get('lugh.translate.register')->register($file);
        $template = $this->get('lugh.route.template')->geti18n('/i18n/',$file);
        return new Response($template['contents'],200,array('Content-Type' => 'application/json'));
    }
    
    /**
     * @Route("/models/{file}", requirements={"file" = ".+"})
     * @Template()
     */
    public function modelsAction($file)
    {
        return $this->getFileContents($file, '/models/', 'getJs', 'application/javascript'); 
    }

    /**
     * @Route("/sharedjs/{file}", requirements={"file" = ".+"})
     * @Template()
     */
    public function sharedjsAction($file)
    {
        return $this->getFileContents($file, '/shared/js/', 'getJs', 'application/javascript');
    }
    
    /**
     * @Route("/sharedviews/{file}", requirements={"file" = ".+"})
     * @Template()
     */
    public function sharedviewsAction($file)
    {
        $template = $this->get('lugh.route.template')->getTemplate('/shared/views/', $file);
        return $this->render(
            $template,
            array()
        );
        
    }
    
    /**
     * @Route("/services/{file}", requirements={"file" = ".+"})
     * @Template()
     */
    public function servicesAction($file)
    {
        return $this->getFileContents($file, '/services/', 'getJs', 'application/javascript');
    }

    /**
     * @Route("/multiviews")
     * @Template()
     */
    public function multiviewsAction()
    {
        $request = $this->get('request');
        $views = $request->get('multi_views',array());
        $viewsJSON = array();
        
        foreach ($views as $key => $view) {
            $template = $this->get('lugh.route.template')->getTemplate('/app/views/', $view);
            $viewsJSON[] = $this->render(
                $template,
                array()
            )->getContent();
        }
        return new Response(json_encode($viewsJSON),200,array('Content-Type'=>'application/json'));
        
    }
    
     /**
     * @Route("/appjs", name="app_app_js")
     * @Template()
     */
    public function appjsAction()
    {
        $response = '';
        $js_files = array();

        $jsapps = array(
            'app.js',
            'controllers/controllers.js',
            'controllers/adhesionsCtrl.js',
            'controllers/stepsCtrl.js',
            'controllers/profileCtrl.js',
            'controllers/foroCtrl.js',
            'controllers/itemCtrl.js',
            'controllers/derechoCtrl.js',
            'controllers/avCtrl.js',
            'services/Mailservices.js',
            'directives/directives.js',
            'directives/stepDirectives.js',
            'directives/votoDirectives.js',
            'directives/avDirectives.js',
            'services/votoServices.js',
            'services/services.js',
            'services/avServices.js'
            );
        $js_files['/app/'] = $jsapps;
        
        $jsmodels = array(
            'accionistasManager.js',
            'itemsManager.js',
            'notificationsManager.js',
            'adhesionsManager.js',
            'mailsManager.js',
            'stepsManager.js',
            'juntaManager.js',
            );
        $js_files['/models/'] = $jsmodels;
        
        $jsshareds = array(
            'main.js',
            'localize.js',
            'controllers.js',
            'services.js',
            'filters.js',
            'directives.js'
            );
        $js_files['/shared/js/'] = $jsshareds;
        
        foreach ($js_files as $js_index => $js_value) {
            foreach ($js_value as $js) {
                $response .= file_get_contents($this->get('lugh.route.template')->getJs($js_index, $js)['route']);
            }  
        }
        
        return new Response($response,200,array('Content-Type'=>'application/javascript'));
        
    }
    
    /**
     * @Route("/apprequest")
     * @Template()
     */
    public function apprequestAction()
    {
        $apps = $this->getAppRequest();
        return new Response(json_encode($apps),200,array('Content-Type'=>'application/json'));
    }
    
    private function getAppRequest()
    { /* @TODO: Apps */
        $em = $this->get('doctrine')->getManager('db_connection');
        $request = $this->get('request');
        $apps = array(
                'voto'      =>  true,
                'foro'      =>  true,
                'derecho'   =>  true,
                'av'        =>  true
            );
        $apps['platforms'] = $apps;
        
        if (
                //$this->get('kernel')->getEnvironment() != 'dev' && 
                $request->getHttpHost() != '127.0.0.1' && 
                $request->getHttpHost() != 'localhost'
                )
        {
            $apps = $this->getRequest()->getSession()->get('apprequest',$apps);
            $paramsapp = $this->getRequest()->getSession()->get('paramsapp',$apps);
            foreach ($paramsapp as $key => $value) {
                if (!is_array($value)) {
                    //$apps[$key] = $apps[$key] && $value && $this->getUser()->getAccionista()->getApps()->{'get' . ucfirst($key)}();
                    foreach ($this->getUser()->getAccionista()->getApp() as $app) {
                        if (strtolower($app->getAppClass()) == $key) {
                            if($apps[$key] && $value){
                                switch($app->getState()){
                                    
                                    case StateClass::stateNew:
                                    case StateClass::statePending:
                                    case StateClass::stateReject:
                                        $apps[$key] = 0;
                                        break;
                                    case StateClass::statePublic:
                                        $apps[$key] = 1;
                                        break;
                                    case StateClass::stateRetornate:
                                        $apps[$key] = 2;
                                        break;
                                }
                            }
                            else{
                                $apps[$key] = 0;
                            }
                                    
                        }
                    }
                }   
            }
        }
        $this->getRequest()->getSession()->set('apprequest',$apps);
        return $apps;
    }
    
    /**
     * @Route("/routerequest")
     * @Template()
     */
    public function routerequestAction()
    {
        $routes = $this->getRouteRequest();

        return new Response(json_encode($routes),200,array('Content-Type'=>'application/json'));
        
    }
    
    /**
     * @Route("/vototype")
     * @Template()
     */
    public function vototypeAction()
    {
        $votoType = $this->getVotoType();

        return new Response($votoType,200,array('Content-Type'=>'application/json'));
        
    }
    
    /**
     * @Route("/avvototype")
     * @Template()
     */
    public function avVototypeAction()
    {
        $votoType = $this->getAvVotoType();

        return new Response($votoType,200,array('Content-Type'=>'application/json'));
        
    }
    
    private function getVotoType()
    {
        return $this->container->get('lugh.parameters')->getVotoParam();
    }
    
    private function getAvVotoType()
    {
        return $this->container->get('lugh.parameters')->getAvVotoParam();
    }
    
    private function getRouteRequest()
    {
        $apps = array(
            'voto'      =>  true,
            'foro'      =>  true,
            'derecho'   =>  true,
            'av'        =>  true
        );
        
        $routesDefault = [
            '/app/profile',
            '/app/dashboard',
            '/app/resetPassword',
            '/app/voto/retornar',
            '/app/foro/retornar',
            '/app/derecho/retornar',
            '/app/av/retornar',
            '/app/header/notificaciones',
            '/app/header/mail/mail',
        ];

        $routesVoto = [
            '/app/voto/voto'
        ];
    
        $routesForo = [
            //'/app/foro/dashboard-foro',
            '/app/foro/adhesion/initiative',
            '/app/foro/adhesion/offer',
            '/app/foro/adhesion/proposal',
            '/app/foro/adhesion/request',
            '/app/foro/actividad-publica/propuestas',
            '/app/foro/actividad-publica/propuesta',
            '/app/foro/actividad-publica/iniciativas',
            '/app/foro/actividad-publica/iniciativa',
            '/app/foro/actividad-publica/ofertas',
            '/app/foro/actividad-publica/oferta',
            '/app/foro/actividad-publica/peticiones',
            '/app/foro/actividad-publica/peticion',
            '/app/foro/actividad-publica/todo',
            '/app/foro/actividad-personal/tareas',
            '/app/foro/actividad-personal/propuestas',
            '/app/foro/actividad-personal/propuesta',
            '/app/foro/actividad-personal/iniciativas',
            '/app/foro/actividad-personal/iniciativa',
            '/app/foro/actividad-personal/ofertas',
            '/app/foro/actividad-personal/oferta',
            '/app/foro/actividad-personal/peticiones',
            '/app/foro/actividad-personal/peticion',
            '/app/foro/actividad-personal/todo',
            '/app/foro/actividad-personal/adhesiones',
            '/app/foro/crear-nueva/propuesta',
            '/app/foro/crear-nueva/iniciativa',
            '/app/foro/crear-nueva/representacion'
        ];

        $routesDerecho = [
            //'/app/derecho/dashboard-derecho',
            '/app/derecho/comunicados',
            '/app/derecho/comunicado',
            '/app/derecho/solicitudes-publicas',
            '/app/derecho/solicitudes-propias',
            '/app/derecho/crear-solicitud',
            '/app/derecho/solicitud'
        ];
        
        $routesAV = [
            //'/app/av/dashboard-av',
            '/app/av/crear-ruego',
            '/app/av/ruegos-propios',
            '/app/av/ruego',
            '/app/av/asistencia-virtual',
            '/app/av/voto',
            '/app/av/abandonar'
        ];
        
        $apps = $this->getRequest()->getSession()->get('apprequest',$apps);
        
        $routes = $routesDefault;
        
        foreach ($apps as $key => $value) {
            foreach ($this->getUser()->getAccionista()->getApp() as $app) {
                if (strtolower($app->getAppClass()) == $key) {
                    $apps[$key] = $value && $app->getState() == StateClass::statePublic;          
                }
            }
        }
        
        if ($apps['voto']) {
            $routes = array_merge($routes,$routesVoto);
        }
        if ($apps['foro']) {
            $routes = array_merge($routes,$routesForo);
        }
        if ($apps['derecho']) {
            $routes = array_merge($routes,$routesDerecho);
        }
        if ($apps['av']) {
            $routes = array_merge($routes,$routesAV);
        }
        return $routes;
    }
    
    private function getFileContents($file, $route, $method, $content_type = null)
    {
        $template = call_user_func(array($this->get('lugh.route.template'), $method),$route, $file);
        $file_contents = file_get_contents($template['route']);
        return new Response($file_contents,200,array('Content-Type' => $content_type != null ? $content_type : $template['content-type']));
    }
    
    
    
    
}
