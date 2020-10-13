<?php

namespace Lugh\DbConnectionBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Lugh\DbConnectionBundle\Lib;
use Lugh\DbConnectionBundle\Lib\PlatformsManager;

/**
 * @Route("/translations")
 */
class TranslationsPlatformController extends Controller
{
    /**
     * @Route("/")
     * @Template()
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * @Route("/content", name="_translationsplatform_translations")
     * @Template()
     */

    //file_getcontents php
    //mirar controlador de los mails
    public function contentTranslationsAction()
    {
        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $platform_id = $request->get('platform_id');

        $langs = $this->getLangs($platform_id);

        $platform = array();
        $host = array();
        foreach ( $langs as $lang => $value){
            $tags = $this->getTagsFor($platform_id, $lang);
            $platform[$lang] = $tags[0];
            $host[$lang]     = $tags[1];
        }
        $orderedHost = array();
        $tagIDs = array();
        foreach ( $langs as $lang => $value){
            $tagIDs = array_merge($tagIDs, array_keys( $host[$lang] ) );
        }

        for($i = 0; $i < sizeof($tagIDs); $i++ ){
            $tag = $tagIDs[$i];
            foreach ( $langs as $lang => $value){
                $orderedHost[$tag][$lang] = ( array_key_exists($tag,$host[$lang]) )? $host[$lang][$tag] : '';
            }
        }
        return array('platform_id' => $platform_id, 'tags_platform' => $platform, 'tags_host' => $orderedHost);
    }



    /**
     * @Route("/savetag", name="_translationsplatform_save", options={"expose"= true})
     * @Template()
     */
    public function saveTagAction(){

        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $tagData = $request->get('data');
        $platform_id = $request->get('platform');
        try{
            //Check if file already exists & makes a backup
            $langs = $this->getLangs($platform_id);
            $folder =  $this->getHostFolderPath( $platform_id );
            $date = date("_ymd-Hi");

            foreach ( $langs as $lang => $value )
            {

                $data = $tagData[$lang];

                $oldFileContent = array();
                if ( file_exists($folder.'i18n/resources-locale_'.$lang.'.js') )
                    $oldFileContent = json_decode(file_get_contents($folder.'i18n/resources-locale_'.$lang.'.js'),true);

                if(true || strcasecmp($data['value'],"") > 0)
                {
                    $this->createBackupFromFile($folder.'i18n/', 'resources-locale_'.$data['lang'], '.js', $date);
                    $newTag = array($data['tag'] => $data['value']);

                    $newFileContent = $this->encodeOutput(json_encode( array_merge($oldFileContent,$newTag), JSON_PRETTY_PRINT));

                    file_put_contents($folder.'i18n/resources-locale_'.$lang.'.js', $newFileContent);
                }

            }

        }catch(\Exception $exc){
                return new Response(json_encode(array('error'=> $exc->getMessage())));
        }
        return new Response(json_encode(array('success'=> '1', 'data' => $tagData )));

    }

    /**
     * @Route("/createtag", name="_translationsplatform_new", options={"expose"= true})
     * @Template()
     */
    public function newTagAction(){

        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $tagData = $request->get('data');
        $platform_id = $request->get('platform');
        try{
            //Check if file already exists & makes a backup
            $langs = $this->getLangs($platform_id);
            $folder =  $this->getHostFolderPath( $platform_id );
            $date = date("_ymd-Hi");

            foreach ( $langs as $lang => $value )
            {

                $data = $tagData[$lang];

                $oldFileContent = array();
                if ( file_exists($folder.'i18n/resources-locale_'.$lang.'.js') ){
                    $oldFileContent = json_decode(file_get_contents($folder.'i18n/resources-locale_'.$lang.'.js'),true);

                }

                if(true || strcasecmp($data['value'],"") > 0)
                {
                    $this->createBackupFromFile($folder.'i18n/', 'resources-locale_'.$data['lang'], '.js', $date);
                    $newTag = array($data['tag'] => $data['value']);

                    $newFileContent = $this->encodeOutput(json_encode( array_merge($oldFileContent,$newTag), JSON_PRETTY_PRINT));
                    file_put_contents($folder.'i18n/resources-locale_'.$lang.'.js', $newFileContent);
                }

            }

        }catch(\Exception $exc){
            return new Response(json_encode(array('error'=> $exc->getMessage())));
        }
        return new Response(json_encode(array('success'=> '2', 'data' => $tagData )));

    }
    /**
     * @Route("/removetag", name="_translationsplatform_remove", options={"expose"= true})
     * @Template()
     */
    public function removeTagAction(){
        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $tagData = $request->get('data');
        $platform_id = $request->get('platform');
        try{
            //Check if file already exists & makes a backup
            $langs = $this->getLangs($platform_id);
            $folder =  $this->getHostFolderPath( $platform_id );
            $date = date("_ymd-Hi");

            foreach ( $langs as $lang => $value )
            {
                $data = $tagData[$lang];

                $oldFileContent = array();
                if ( file_exists($folder.'i18n/resources-locale_'.$lang.'.js') ){
                    $oldFileContent = json_decode(file_get_contents($folder.'i18n/resources-locale_'.$lang.'.js'),true);
                    unset( $oldFileContent[$data['tag']] );
                    $newFileContent = $this->encodeOutput(json_encode( $oldFileContent, JSON_PRETTY_PRINT));
                    file_put_contents($folder.'i18n/resources-locale_'.$lang.'.js', $newFileContent);
                }
            }

        }catch(\Exception $exc){
            return new Response(json_encode(array('error'=> $exc->getMessage())));
        }
        return new Response(json_encode(array('success'=> '3', 'data' => $tagData )));
    }


    /**
     * @Route("/gettag", name="_translationsplatform_getTag", options={"expose"= true})
     * @Template()
     */
    public function getTagAction(){
        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $platform_id = $request->get('platform');
        $tag = $request->get('data');

        $kernel = $this->get('kernel');
        $bundle = $kernel->getBundle('LughWebAppBundle');
        $rootPath = $bundle->getPath();
        $basePath = $rootPath . '/Resources/js/Angular/';

        $files = array(
            'workspace_base',
            $this->getTemplate($platform_id),
        );
        $tagData = array();
        try{
            $langs = $this->getLangs($platform_id);

            foreach( $langs as $lang => $value ){
                $tagDataLang = array();
                foreach ($files as $file){
                    $path = $basePath.$file.'/i18n/resources-locale_'.$lang.'.js';
                    if (file_exists($path)){
                        $aux = json_decode(file_get_contents($path),true);
                        $tagDataLang = array_merge( $tagDataLang, array($aux[$tag]));
                    }
                }
                $tagData[$lang] = $tagDataLang;
            }
        }catch(\Exception $exc){
            return new Response(json_encode(array('error'=> $exc->getMessage())));
        }

        return new Response( $this->encodeOutput(json_encode( array('tag' => $tag, 'data' => $tagData) ) ) );
    }


    private function createBackupFromFile($filePath, $fileName, $ext, $date = false ){
        if(!$date)
            $date = date("_ymd-Hi");
        try{

            if (file_exists($filePath.$fileName.$ext)){
                $oldFileContent = json_decode(file_get_contents($filePath.$fileName.$ext),true);

                $backup = array();
                if (file_exists($filePath.$fileName.$date.$ext))
                    $backup = json_decode(file_get_contents($fileName.$date.$ext),true);

                $newbackup = array_merge($oldFileContent , $backup);
                file_put_contents($filePath.$fileName.$date.$ext,$this->encodeOutput(json_encode($newbackup , JSON_PRETTY_PRINT)));
            }

        } catch(\Exception $exc){
            return new Response(json_encode(array('error'=> $exc->getMessage())));
        }
        return 1;
    }

    private function getHostFolderPath( $platform_id ){
        $kernel = $this->get('kernel');
        $bundle = $kernel->getBundle('LughWebAppBundle');
        $rootPath = $bundle->getPath();
        $basePath = $rootPath . '/Resources/js/Angular/';

        $host = $this->getHost($platform_id);
        $folder =  $basePath.$host."/";

        try{

            $path = realpath($folder);
            if(!($path !== false AND is_dir($path))){
                mkdir($folder);
            }
            $path = realpath($folder."i18n/");
            if(!($path !== false AND is_dir($path))){
                mkdir($folder."i18n/");
            }

        }
        catch(\Exception $exc){

            return new Response(json_encode(array('error'=> $exc->getMessage())));

        }
        return $folder;
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

    private function getTagsFor($id,$lang){

        $kernel = $this->get('kernel');
        $bundle = $kernel->getBundle('LughWebAppBundle');
        $rootPath = $bundle->getPath();
        $basePath = $rootPath . '/Resources/js/Angular/';

        $files = array(
            'workspace_base',
            $this->getTemplate($id),
            $this->getHost($id)
        );

        $platform_tags = array();
        $host_tags = array();
        foreach ($files as $file)
        {
            $path = $basePath.$file.'/i18n/resources-locale_'.$lang.'.js';
            if (file_exists($path))
            {
                if($file == $this->getHost($id)){
                    $host_tags = array_merge( $host_tags,json_decode(file_get_contents($path),true));
                }
                $platform_tags = array_merge($platform_tags,json_decode(file_get_contents($path),true));
            }
        }

        return array($platform_tags,$host_tags);
    }

    private function getLangs($platform_id){

        return $this->get('lugh.langs.db_connection')->from($platform_id);
    }

    private function encodeOutput( $input ){
        $replacedString = preg_replace("/\\\\u([0-9abcdef]{4})/", "&#x$1;", $input );
        $unicodeString = mb_convert_encoding($replacedString, 'UTF-8', 'HTML-ENTITIES');
        return $unicodeString;
    }

}
