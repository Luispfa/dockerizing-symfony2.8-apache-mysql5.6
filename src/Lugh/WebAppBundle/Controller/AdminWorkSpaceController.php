<?php

namespace Lugh\WebAppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Lugh\DbConnectionBundle\Lib\PlatformsManager;

/**
 * @Route("/admin_workspace")
 */
class AdminWorkSpaceController extends Controller
{
     /**
     * @Route("/main.js", name="admin_main_app_js")
     * @Template("LughWebAppBundle:Default:mainAppJs.html.twig")
     */
    public function mainAppJsAction()
    {
        $params = array(
            'webroot'           => $this->getRequest()->getBasePath(),
            'apiroot'           => $this->getRequest()->getBaseUrl(),
            'namecontroller'    => 'admin_workspace',
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
     * @Route("/", name="admin_workspace")
     * @Template()
     */
    public function indexAction()
    {
        $template = $this->get('lugh.route.template')->getTemplate('/admin/', 'index.html');
        return $this->render(
            $template,
            array('title' => $this->container->get('lugh.parameters')->getByKey('Config.platform.title', 'Lugh Electronic Platforms'))
        );
    }
    
    /**
     * @Route("/views/{file}", requirements={"file" = ".+"}, name="admin_views_file")
     * @Template()
     */
    public function viewsAction($file)
    {
        
        $template = $this->get('lugh.route.template')->getTemplate('/admin/views/', $file);
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
        return $this->getFileContents($file, '/admin/', 'getJs', 'application/javascript');
    }
    
    /**
     * @Route("/css/{file}", requirements={"file" = ".+"}, name="admin_css_file")
     * @Template()
     */
    public function cssAction($file)
    {
        return $this->getFileContents($file, '/', 'getCss', 'text/css');
    }
    
    /**
     * @Route("/images/{file}", requirements={"file" = ".+"}, name="admin_images_file")
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
     * @Route("/models/{file}", requirements={"file" = ".+"})
     * @Template()
     */
    public function modelsAction($file)
    {
        return $this->getFileContents($file, '/models/', 'getJs', 'application/javascript');
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
     * @Route("/appjs", name="admin_app_js")
     * @Template()
     */
    public function appjsAction()
    {
        $response = '';
        $js_files = array();

        $jsapps = array(
            'app.js',
            'controllers/controllers.js',
            'controllers/parametersCtrl.js',
            'controllers/profileCtrl.js',        
            'controllers/usersTableCtrl.js',
            'controllers/translationCtrl.js',
            'controllers/foroCtrl.js',
            'controllers/itemCtrl.js',
            'controllers/adhesionsCtrl.js',
            'controllers/derechoCtrl.js',
            'controllers/avCtrl.js',
            'controllers/messageCtrl.js',
            'controllers/pendingCtrl.js',
            'controllers/voteCtrl.js',
            'directives/directives.js',
            'services/services.js',
            );
        $js_files['/admin/'] = $jsapps;
        
        $jsmodels = array(
            'accionistasManager.js',
            'itemsManager.js',
            'notificationsManager.js',
            'mailsManager.js',
            'adhesionsManager.js',
            'actionsManager.js',
            'accesosManager.js'
            );
        $js_files['/models/'] = $jsmodels;
        
        $jsshareds = array(
            'main.js',
            'localize.js',
            'controllers.js',
            'directives.js',
            'services.js',
            'filters.js'
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
     * @Route("/statisticsgraph")
     * @Template()
     */
    public function statisticsgraphAction()
    {
        $request = $this->get('request');
        $params = $request->get('params');

        $apiAddress = $this->get('lugh.parameters')->getByKey('stats.api.address', '');
        $siteId = $this->get('lugh.parameters')->getByKey('stats.api.site_id', '');
        $apiKey = $this->get('lugh.parameters')->getByKey('stats.api.key', '');

        $response = array();

        foreach ($params as $param) {
            $period = $param['period'];   //day, week, month, year or range
            $date = ($param['date'] == 'date') ? date_create()->format("Y-m-d") : $param['date'];

            if ($param['type'] == 1) {
                $graphHoursAPIURL =
                    $apiAddress .
                    'index.php?module=API&graphType=verticalBar&width=800&height=200&method=ImageGraph.get&idSite=' .
                    $siteId . '&period=' . $period . '&date=' . $date .
                    '&apiModule=VisitsSummary&apiAction=get&format=JSON&token_auth=' .
                    $apiKey;
            } else {
                $graphHoursAPIURL =
                    $apiAddress .
                    'index.php?module=API&graphType=verticalBar&width=400&height=200&method=ImageGraph.get&idSite=' .
                    $siteId . '&period=' . $period . '&date=' . $date .
                    '&apiModule=VisitTime&apiAction=getVisitInformationPerServerTime&format=JSON&token_auth=' .
                    $apiKey;
            }

            $type = pathinfo($graphHoursAPIURL, PATHINFO_EXTENSION);
            $data = file_get_contents($graphHoursAPIURL);
            $base64 = 'data:image/png;base64,' . base64_encode($data);

            $response[] = $base64;
        }
        return new Response(json_encode(array('success' => 1, 'response' => $response)));
    }

    /**
     * @Route("/lastvisitoractions")
     * @Template()
     */
    public function lastVisitorActionsAction(){
        $apiAddress = $this->get('lugh.parameters')->getByKey('stats.api.address', '');
        $siteId  = $this->get('lugh.parameters')->getByKey('stats.api.site_id', '');
        $apiKey = $this->get('lugh.parameters')->getByKey('stats.api.key', '');

        $lastVisitorActionsAPIURL =
            $apiAddress .
            'index.php?module=API&graphType=verticalBar&method=Live.getLastVisitsDetails&idSite='.
            $siteId .
            '&period=year&date=today&format=JSON&token_auth=' .
            $apiKey;

        return new Response(json_encode(array('lastVisitorActions' => json_decode(file_get_contents($lastVisitorActionsAPIURL),true) ) ));
    }

    
    private function getAppRequest()
    {
        $em = $this->get('doctrine')->getManager('db_connection');
        $request = $this->get('request');
        $apps = array(
                'voto'      =>  true,
                'foro'      =>  true,
                'derecho'   =>  true,
                'av'        =>  true
            );
        $apss['platforms'] = $apps;
        
        if (
                //$this->get('kernel')->getEnvironment() != 'dev' && 
                $request->getHttpHost() != '127.0.0.1' && 
                $request->getHttpHost() != 'localhost'
                )
        {
            $apps = $this->getRequest()->getSession()->get('apprequest',$apps);
        }
        return $apps;
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
            //'/app/profile-admin',
            //'/app/users',
            '/app/users/profile',
            '/app/users/certificado',
            '/app/users/no-certificado',
            '/app/users/todo',
            //'/app/parameters',
            '/app/translateManager',
            '/app/todo.html',
            '/app/header/notificaciones',
            '/app/header/mail/mail',
            '/app/header/contact-user',
            '/app/header/edit-message',
            '/app/header/pendientes',
            '/app/registroAdmin', /*TODO-h3r*/
            '/app/statistics'
        ];

        $routesVoto = [
            '/app/voto/delegaciones', 
            '/app/voto/votos',
            '/app/voto/anulaciones',
            '/app/voto/todo',
            '/app/voto/historial',
            '/app/voto/accion',
            '/app/voto/movimientos'
        ];
    
        $routesForo = [
            '/app/foro/all',
            '/app/foro/adhesions',
            '/app/foro/propuestas',
            '/app/foro/user-propuestas',
            '/app/foro/propuesta',
            '/app/foro/iniciativas',
            '/app/foro/user-iniciativas',
            '/app/foro/iniciativa',
            '/app/foro/ofertas',
            '/app/foro/user-ofertas',
            '/app/foro/oferta',
            '/app/foro/peticiones',
            '/app/foro/user-peticiones',
            '/app/foro/peticion',
            '/app/foro/pendientes',
            '/app/foro/adhesion/proposal',
            '/app/foro/adhesion/initiative',
            '/app/foro/adhesion/offer',
            '/app/foro/adhesion/request'
        ];

        $routesDerecho = [
            '/app/derecho/solicitudes',
            '/app/derecho/user-solicitudes',
            '/app/derecho/pendientes',
            '/app/derecho/solicitud',
            '/app/derecho/comunicados',
            '/app/derecho/comunicado',
            '/app/derecho/crear-comunicado', 
            '/app/derecho/respuesta'
        ];
        
        $routesAV = [
            '/app/av/pendientes',
            '/app/av/ruego',
            '/app/av/ruegos',
            '/app/av/lives',
            '/app/av/userlives',
            '/app/av/votos',
            '/app/av/accion',
            '/app/av/historial',
            '/app/av/abandonos',
            '/app/av/accesos',
            '/app/av/detalle'
        ];
        $apps = $this->getRequest()->getSession()->get('apprequest',$apps);
        
        $routes = $routesDefault;
        
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
