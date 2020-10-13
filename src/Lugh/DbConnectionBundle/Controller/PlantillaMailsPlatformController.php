<?php

namespace Lugh\DbConnectionBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Lugh\DbConnectionBundle\Lib\PlatformsManager;
use Symfony\Component\HttpFoundation\Response;


/**
 * @Route("/plantillamails")
 */
class PlantillaMailsPlatformController extends Controller{

    /**
     * @Route("/" ,name="_plantillamailsplatform_plantilla")
     * @Template()
     */
    public function plantillaMailsPlatformContentAction(){
        /*$request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $platform_id = $request->get('platform_id');

        $kernel = $this->get('kernel');
        $bundle = $kernel->getBundle('LughWebAppBundle');
        $rootPath = $bundle->getPath();
        $basePath = $rootPath . '/Resources/views/Mail/';

        $em = $this->getDoctrine()->getManager('db_connection');
        $platform =  $em->getRepository('Lugh\DbConnectionBundle\Entity\Auth')->find($platform_id);
        PlatformsManager::switchDb($platform->getDbname());

        $path = $basePath .DIRECTORY_SEPARATOR.$platform->getHost().DIRECTORY_SEPARATOR;//. mailTemplate.html.twig;

        $path = realpath( $path );
        if(!($path !== false AND is_dir($path))){
            mkdir($path);
        }

        if(!file_exists($path.'mailTemplate.html.twig')){

            $storeFolder = $basePath;
            $files = scandir($storeFolder);
            if ( false!==$files )
            {
                $found = array();
                foreach ( $files as $file )
                {
                    if ( '.'!=$file && '..'!=$file)
                    {
                        //2
                        if( strpos($file,'.twig') !== false ){

                            $value = str_replace(".twig", "", $file);
                            $value = str_replace(" ", "", $value);
                            $value = str_replace(".html", "", $value);

                            $found['styles'][$value]= $file;

                        }else{
                            $found['folders'][] = $file;
                        }
                    }
                }
                if(count( $found['folders'] ) > 0)
                {
                    foreach ( $found['folders'] as $folder )
                    {
                        $files = scandir($storeFolder.DIRECTORY_SEPARATOR.$folder);
                        foreach ( $files as $file )
                        {
                            if ( '.'!=$file && '..'!=$file)
                            {
                                //2
                                if( strpos($file,'.twig') !== false ){
                                    $found['hosts'][$folder] = $folder.DIRECTORY_SEPARATOR.$file;
                                }
                            }
                        }
                    }

                }
            }

            return array('platform_id' => $platform_id, 'error' => -2, 'templates' => $found);

        }

        $file = file_get_contents($path.DIRECTORY_SEPARATOR.'mailTemplate.html.twig');

        $logo_path = '/lugh/logic/web/bundles/lughwebapp/css/'.$platform->getHost().'/img/logo.png';
        if(!file_exists($logo_path))
            $logo_path = '/lugh/logic/web/bundles/lugh/workspace_base/logo.png';
        $file = str_replace('{{ app.request.getSchemeAndHttpHost() }}/{{ app.request.getBasePath() }}/bundles/lugh/workspace_base/logo.png',$logo_path,$file);


        $widget['id']    ='html';
        $widget['clase'] = 'html';
        $widget['html']  = $this->getWidget('textarea', 'html_html', $file);


        return array('platform_id' => $platform_id, 'widget'=> $widget);*/
        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $platform_id = $request->get('platform_id');

        $kernel = $this->get('kernel');
        $bundle = $kernel->getBundle('LughWebAppBundle');
        $rootPath = $bundle->getPath();
        $basePath = $rootPath . DIRECTORY_SEPARATOR.'Resources'.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'Mail'.DIRECTORY_SEPARATOR;
        $base = $request->server->get('BASE')?$request->server->get('BASE') : '';
        $mailpath = $basePath . $this->getHost( $platform_id ) . '.html.twig';
        if(!file_exists($mailpath)){
            $mailpath = $basePath . $this->getTemplate( $platform_id ) . '.html.twig';
            if(!file_exists($mailpath)){
                $mailpath = $basePath . 'default.html.twig';
            }
        }
        $file = file_get_contents($mailpath);

        $logopath = $base.'/bundles/lughwebapp/css/'.$this->getHost($platform_id);
        if( !is_dir($logopath) || !file_exists($logopath.'/img/logo.png')){
            $logopath = $base.'/bundles/lughwebapp/css/'.$this->getTemplate($platform_id);
            if( !is_dir($logopath) || !file_exists($logopath.'/img/logo.png')){
                $logopath = $base.'/bundles/lugh/workspace_base/logo.png';
            }
        }

        $file = str_replace('{{ app.request.getSchemeAndHttpHost() }}/{{ app.request.getBasePath() }}/bundles/lugh/workspace_base/logo.png',$logopath,$file);
        $widget['id']    ='html';
        $widget['clase'] = 'html';
        $widget['html']  = $this->getWidget('textarea', 'html_html', $file);

        return array('platform_id' => $platform_id,'style' => $this->getHost($platform_id), 'plantilla' => $file, 'widget'=> $widget);
    }

    /**
     * @Route("/save" ,name="_plantillamailsplatform_save")
     * @Template()
     */
    public function plantillaMailsPlatformSaveAction()
    {
        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $html = $request->get('html');
        $platform_id = $request->get('platform_id');

        $base = $request->server->get('BASE')?$request->server->get('BASE') : '';

        $logopath = $base.'/bundles/lughwebapp/css/'.$this->getHost($platform_id);
        if( !is_dir($logopath) || !file_exists($logopath.'/img/logo.png')){
            $logopath = $base.'/bundles/lughwebapp/css/'.$this->getTemplate($platform_id);
            if( !is_dir($logopath) || !file_exists($logopath.'/img/logo.png')){
                $logopath = $base.'/bundles/lugh/workspace_base/logo.png';
            }
        }
       
        $html = str_replace($logopath,'{{ app.request.getSchemeAndHttpHost() }}/{{ app.request.getBasePath() }}/bundles/lugh/workspace_base/logo.png',$html);

        $kernel = $this->get('kernel');
        $bundle = $kernel->getBundle('LughWebAppBundle');
        $rootPath = $bundle->getPath();

        $basePath = $rootPath . DIRECTORY_SEPARATOR.'Resources'.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'Mail'.DIRECTORY_SEPARATOR;

        try{
            file_put_contents($basePath . $this->getHost( $platform_id ) . '.html.twig',$html);
        }catch(\Exception $exc){
            return new Response(json_encode(array('error'=> $exc->getMessage())));
        }
        return new Response(json_encode(array('success'=> '1')));
    }

    /**
     * @Route("/remove" ,name="_plantillamailsplatform_remove")
     * @Template()
     */
    public function plantillaMailsPlatformRemoveAction()
    {
        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $style = $request->get('style');

        $kernel = $this->get('kernel');
        $bundle = $kernel->getBundle('LughWebAppBundle');
        $rootPath = $bundle->getPath();
        $basePath = $rootPath . DIRECTORY_SEPARATOR.'Resources'.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'Mail'.DIRECTORY_SEPARATOR;

        try{
            $filePath = $basePath . $style . '.html.twig';
            if(file_exists($filePath))
            {
                unlink($filePath);
            }
           
        }catch(\Exception $exc){
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

}