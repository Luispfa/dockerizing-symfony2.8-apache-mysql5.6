<?php

namespace Lugh\WebAppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Lugh\WebAppBundle\Lib\External;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * @Route("/home", name="home_index")
 */
class HomeController extends Controller
{
    
    private function CertificateLogin($arrayValidate)
    {
        $storage = $this->get('lugh.server')->getStorage();

        if ($arrayValidate != null && $arrayValidate['status'] == 'VALID')
        {
            try {
                $user = $storage->getUserByCert($arrayValidate['clientCert']);
            } catch (Exception $exc) {
                return false;
            }
            $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
            $this->container->get('security.context')->setToken($token);
            return true;
        }
        return false;
    }
    
     /**
     * @Route("/{_locale}", defaults={"_locale": "es"}, requirements={ "_locale": "es|en|ca|gl" }, name="home_homepage")
     * @Template()
     */
    public function indexAction($_locale)
    {
        $this->container->get('lugh.translate.register')->setLocale($_locale);
        $template = $this->get('lugh.route.template')->getTemplate('/home/', 'index.html');
        return $this->render(
            $template,
            array('title' => $this->container->get('lugh.parameters')->getByKey('Config.platform.title', 'Lugh Electronic Platforms'))
        );
    }
    
     /**
     * @Route("/main.js", name="default_main_app_js")
     * @Template("LughWebAppBundle:Default:mainAppJs.html.twig")
     */
    public function mainAppJsAction()
    {
        $params = array(
            'webroot'           => $this->getRequest()->getBasePath(),
            'apiroot'           => $this->getRequest()->getBaseUrl(),
            'namecontroller'    => 'home',
            'apiAddress'        => $this->container->get('lugh.parameters')->getByKey('stats.api.address', 'https://analytics.juntadeaccionistas.es/'),
            'siteId'            => $this->container->get('lugh.parameters')->getByKey('stats.api.site_id', '1'),
            'userName'          => 'anonymous',
            'lang'              => $this->container->get('lugh.translate.register')->getLocale()
                );
        $response = new \Symfony\Component\HttpFoundation\Response(
            $this->renderView('LughWebAppBundle:Default:mainAppJs.html.twig', $params
        ));
        $response->headers->set('Content-Type', 'application/javascript');
        return $response;
    }
    
    /**
     * @Route("/certificateLogin", name="default_certificatelogin")
     * @Template("")
     */
    public function CertificateLoginAction()
    {
        $bypass = $this->container->get('lugh.parameters')->getByKey('Internal.certificate.bypass', 1);
        //$arrayValidate=  External\CertValidationTractis::GetClientCertificateData();
        $arrayValidate=  External\CertificateValidator::GetCertificateData($bypass);
        
        if ($this->CertificateLogin($arrayValidate))
        {
            return new Response(json_encode(array('success'=>true)),200,array('Content-Type'=>'application/json'));
        }
        $session = $this->getRequest()->getSession();
        $session->set('cert', $arrayValidate);
        $validate = json_encode($arrayValidate);
        return new Response($validate,200,array('Content-Type'=>'application/json'));
        
    }
    
    
    /**
     * @Route("/views/{file}", requirements={"file" = ".+"}, name="home_views_file")
     * @Template()
     */
    public function viewsAction($file)
    {
        $template = $this->get('lugh.route.template')->getTemplate('/home/views/', $file);
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
        return $this->getFileContents($file, '/home/', 'getJs', 'application/javascript');  
    }
    
    /**
     * @Route("/css/{file}", requirements={"file" = ".+"}, name="home_css_file")
     * @Template()
     */
    public function cssAction($file)
    {
        return $this->getFileContents($file, '/', 'getCss'); 
    }
    
    /**
     * @Route("/images/{file}", requirements={"file" = ".+"}, name="home_images_file")
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
        return $this->getFileContents($file, '/home/models/', 'getJs', 'application/javascript'); 
        
    }
    
    /**
     * @Route("/appjs", name="home_app_js")
     * @Template()
     */
    public function appjsAction()
    {
        $response = '';
        $js_files = array();
        $jsapps = array(
            'app.js',
            'controllers/controllers.js',
            'controllers/profileCtrl.js',
            'directives/directives.js',
            'services/services.js'
            );
        $js_files['/home/'] = $jsapps;
        
        $jsmodels = array(
            'accionistasManager.js',
            );
        $js_files['/home/models/'] = $jsmodels;
        
        $jsshareds = array(
            'directives.js',
            'controllers.js',
            'services.js',
            'localize.js',
            'main.js',
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
     * @Route("/utils/recaptcha")
     * @Template()
     */
    public function recaptchaCheckAction()
    {
        $serializer = $this->container->get('jms_serializer');
        $request = $this->get('request');
        
        $resp = External\RecaptchaCheck::recaptcha_check_answer(
                    $this->container->getParameter('recaptcha_prKey'),      //prKey
                    $request->getClientIp(),                                //Remote IP
                    $request->get('challenge'),                             //Challenge
                    $request->get('response')                               //Response
                );
        
        if($resp->is_valid == false)
        {
            return new Response($serializer->serialize(array('error' => 'Response not valid'), 'json')); 
        }
        
        return new Response($serializer->serialize(array('success' => 'Valid response'), 'json'));
    }
    
    /**
     * @Route("/paramrequest")
     * @Template()
     */
    public function apprequestAction()
    {
        $params = $this->getParamRequest();
        return new Response(json_encode($params),200,array('Content-Type'=>'application/json'));
    }
    
    private function getParamRequest()
    {
        $params = $this->getRequest()->getSession()->get('paramrequest',array());
        return $params;
    }
    
    private function getFileContents($file, $route, $method, $content_type = null)
    {
        $mime = array(
            'css'   => 'text/css',
            'js'    => 'application/javascript' 
        );
        
        $ext = substr($file,strpos($file, '.')+1,3);
        $mime_ext = isset($mime[$ext]) ? $mime[$ext] : 'text/plain';
        $template = call_user_func(array($this->get('lugh.route.template'), $method),$route, $file);
        $mime_content = $template['content-type'] == 'text/x-asm' || $template['content-type'] == 'text/troff' || $template['content-type'] == 'text/plain' ? $mime_ext : $template['content-type'];
        $file_contents = file_get_contents($template['route']);
        return new Response($file_contents,200,array('Content-Type' => $content_type != null ? $content_type : $mime_content));
    }
    
    
}
