<?php
namespace Lugh\WebAppBundle\DomainLayer;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface as container;

class RouteTemplateService
{


    protected $container;

    public function __construct(container $container = null) {
        $this->container = $container;
    }

    protected function get($id)
    {
        return $this->container->get($id);
    }

    protected function getRequest()
    {
        return $this->container->get('request_stack')->getCurrentRequest();
    }

    private function templateExist($path)
    {
        return $this->get('templating')->exists($path);
    }
    private function MailPath()
    {
        $kernel = $this->get('kernel');
        $bundle = $kernel->getBundle('LughWebAppBundle');
        $rootPath = $bundle->getPath();
        return $rootPath . '/Resources/views/Mail';
    }

    private function jsPath()
    {
        $kernel = $this->get('kernel');
        $bundle = $kernel->getBundle('LughWebAppBundle');
        $rootPath = $bundle->getPath();
        return $rootPath . '/Resources/js/Angular';
    }

    private function cssPath()
    {
        $kernel = $this->get('kernel');
        $bundle = $kernel->getBundle('LughWebAppBundle');
        $rootPath = $bundle->getPath();
        return $rootPath . '/../../../web/bundles/lughwebapp/css';
    }

    private function imagesPath()
    {
        $kernel = $this->get('kernel');
        $bundle = $kernel->getBundle('LughWebAppBundle');
        $rootPath = $bundle->getPath();
        return $rootPath . '/../../../web/bundles/lughwebapp/images';
    }

    private function getRoute($route, $file, $base, $host)
    {
        if (strpos($file, '..') !=false)
        {
            throw new NotFoundHttpException('Route Invalid');
        }

        $temp_name = $base;
        $path =  $route . $file;

        if (
                //$this->container->get('kernel')->getEnvironment() != 'dev' &&
                $host != '127.0.0.1' &&
                $host != 'localhost'
            )
        {

            $request = $this->getRequest();
            $session = $request->getSession();
            $temp_name = $session != null ? $session->get('lugh_' . $host, false) : false;
            if ($temp_name == false)
            {
                $temp_name = $this->getPath($host);
            }

        }

        return $temp_name . $path;

    }

    private function getRoutePath($route, $file, $base, $root, $method = 'file_exists', $extension = '')
    {
        $request = Request::createFromGlobals();
        $host = $request->getHttpHost();
        $template = $root . $base . $route . $file . $extension;
        $path_template =  $root . $host .  $route . $file . $extension;

        if ($this->templateExist($path_template) || file_exists($path_template))
        {
            $template = $path_template;
        }
        else
        {
            $template = $this->getRouteifExist($route, $file, $base, $root, $method, $extension);
        }

        return $template;
    }

    private function getRouteifExist($route, $file, $base, $root, $method = 'file_exists', $extension = '')
    {
        $request = Request::createFromGlobals();
        $host = $request->getHttpHost();
        $template = $root . $base . $route . $file . $extension;
        $path = $this->getRoute($route, $file, $base, $host);
        if (call_user_func($method, $root . $path . $extension))
        {
            $template = $root . $path . $extension;
        }
        return $template;
    }


    private function getPath($host)
    {
        $path = false;
        $em = $this->get('doctrine')->getManager('db_connection');
        $query = $em->createQuery('SELECT a FROM Lugh\DbConnectionBundle\Entity\Auth a WHERE a.host = :host and a.active=1');
        $query->setParameter('host', $host);
        $record = $query->getOneOrNullResult();
        if ($record != null && $record->getTemplate() != null && $record->getTemplate()->getPath() != null)
        {
            $path = trim($record->getTemplate()->getPath());
        }
        return $path;
    }

    private function getTemplateMail($templateName)
    {
        $request        = Request::createFromGlobals();
        $host           = $request->getHttpHost();
        $template       =  $templateName;
        $path_template  = $this->MailPath() . '/' . $this->getTemplatePath() .  '.html.twig';
        $path_host      = $this->MailPath() . '/' . $host .  '.html.twig';
        if ($this->templateExist($path_host) || file_exists($path_host))
        {
            $template = $host;
        }
        else if($this->templateExist($path_template) || file_exists($path_template))
        {
            $template = $this->getTemplatePath();
        }

        return $template;
    }

    public function getTemplatePath()
    {
        $path = false;
        $request = Request::createFromGlobals();
        $host = $request->getHttpHost();
        if (
                $host != '127.0.0.1' &&
                $host != 'localhost'
            )
        {
            $path = $this->getPath($host);
        }

        return $path;
    }


    public function getTemplate($route, $file)
    {
        return $this->getRoutePath($route, $file, 'workspace_base', 'LughWebAppBundle:Angular:',array($this, 'templateExist'), '.twig');
    }

    public function getJs($route, $file)
    {
        $filename = $this->getRoutePath($route, $file, 'workspace_base', $this->jsPath() . '/');
        return array(
            'route'         =>  $filename,
            'content-type'  =>  mime_content_type($filename)
            );
    }

    public function getCss($route, $file)
    {
        $filename = $this->getRoutePath($route, $file, 'workspace_base', $this->cssPath() . '/');
        return array(
            'route'         =>  $filename,
            'content-type'  =>  mime_content_type($filename)
            );
    }

    public function getMail($template = 'default')
    {
        return $this->getTemplateMail($template);
    }

    public function getimages($route, $file)
    {
        $filename = $this->getRoutePath($route, $file, 'workspace_base', $this->imagesPath() . '/');
        return array(
            'route'         =>  $filename,
            'content-type'  =>  mime_content_type($filename)
            );
    }

    public function geti18n($route, $file)
    {
        $filename = $this->getRoutePath($route, $file, 'workspace_base', $this->jsPath() . '/');
        $file_contents = json_decode(file_get_contents($filename), true);

        $fileTemplate = $this->getRouteifExist($route, $file, 'workspace_base', $this->jsPath() . '/');
        $file_contentsTemplate = json_decode(file_get_contents($fileTemplate), true);

        $fileWS =  $this->jsPath(). '/' . 'workspace_base' . $route . $file;
        $file_contentsWs = json_decode(file_get_contents($fileWS), true);


        $fileI18n = array_merge($file_contentsWs, $file_contentsTemplate, $file_contents);

        return array(
            'route'         =>  $filename,
            'content-type'  =>  mime_content_type($filename),
            'contents'      => json_encode($fileI18n)
            );
    }
    public function isAdminAddr($host)
    {
        $admAddr = $this->container->getParameter('admin_addr');
        return is_array($admAddr) ? in_array($host, $admAddr) : false;
    }
}
