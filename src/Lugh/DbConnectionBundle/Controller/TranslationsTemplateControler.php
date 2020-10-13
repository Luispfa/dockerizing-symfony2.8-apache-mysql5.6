<?php

namespace Lugh\DbConnectionBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Lugh\DbConnectionBundle\Lib;
use Lugh\DbConnectionBundle\Lib\PlatformsManager;

/**
 * @Route("/translations-template")
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
     * @Route("/content", name="_translationstemplate_translations")
     * @Template()
     */

    public function contentTranslationsAction()
    {
        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $template_id = $request->get('template_id');

        $langs = $this->getLangs($template_id);

        $stack    = array();
        $template = array();

        //Pedimos por cada lenguaje todos los tags existentes, por una parte stackeados wsb<-template y por la otra parte solo template.
        foreach ( $langs as $lang => $value){
            $tags = $this->getTagsFor($template_id, $lang);
            $stack[$lang]    = $tags[0];
            $template[$lang] = $tags[1];
        }

        $tagIDs = array();
        $orderedTemplate = array();
        foreach ( $langs as $lang => $value){
            $tagIDs = array_merge($tagIDs, array_keys( $template[$lang] ) );
        }

        for($i = 0; $i < sizeof($template['es']); $i++ ){
            $tag = $tagIDs[$i];
            foreach ( $langs as $lang => $value){
                $orderedTemplate[$tag][$lang] = ( array_key_exists($tag,$template[$lang]) )? $template[$lang][$tag] : '';
            }
        }
        return array('template_id' => $template_id, 'tags_stack' => $stack, 'tags_template' => $orderedTemplate);
    }

    /**
     * @Route("/savetag", name="_translationstemplate_save", options={"expose"= true})
     * @Template()
     */
    public function saveTagAction(){

        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $tagData = $request->get('data');
        $template_id = $request->get('template');
        try{
            //Check if file already exists & makes a backup
            $langs = $this->getLangs();

            $folder =  $this->getTemplateFolderPath( $template_id );
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
     * @Route("/createtag", name="_translationstemplate_new", options={"expose"= true})
     * @Template()
     */
    public function newTagAction(){

        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $tagData = $request->get('data');
        $template_id = $request->get('template');
        try{
            //Check if file already exists & makes a backup
            $langs = $this->getLangs();
            $folder =  $this->getTemplateFolderPath( $template_id );
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
     * @Route("/removetag", name="_translationstemplate_remove", options={"expose"= true})
     * @Template()
     */
    public function removeTagAction(){
        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $tagData = $request->get('data');
        $template_id = $request->get('template');
        try{
            //Check if file already exists & makes a backup
            $langs = $this->getLangs();
            $folder =  $this->getTemplateFolderPath( $template_id );
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
     * @Route("/gettag", name="_translationstemplate_getTag", options={"expose"= true})
     * @Template()
     */
    public function getTagAction(){
        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $template_id = $request->get('template');
        $tag = $request->get('data');

        $kernel = $this->get('kernel');
        $bundle = $kernel->getBundle('LughWebAppBundle');
        $rootPath = $bundle->getPath();
        $basePath = $rootPath . '/Resources/js/Angular/';

        $files = array(
            'workspace_base',
            $this->getTemplate($template_id),
        );
        $tagData = array();
        try{
            $langs = $this->getLangs();

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

    private function getTemplateFolderPath( $template_id ){
        $kernel = $this->get('kernel');
        $bundle = $kernel->getBundle('LughWebAppBundle');
        $rootPath = $bundle->getPath();
        $basePath = $rootPath . '/Resources/js/Angular/';

        $em = $this->getDoctrine()->getManager('db_connection');
        $template =  $em->getRepository('Lugh\DbConnectionBundle\Entity\Template')->find($template_id);

        $folder =  $basePath.$template->getPath()."/";

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

    private function getTemplate($template_id)
    {
        $em = $this->getDoctrine()->getManager('db_connection');
        $t =  $em->getRepository('Lugh\DbConnectionBundle\Entity\Template')->find($template_id);
        return $t->getPath();
    }

    private function getTagsFor($id,$lang){

        $kernel = $this->get('kernel');
        $bundle = $kernel->getBundle('LughWebAppBundle');
        $rootPath = $bundle->getPath();
        $basePath = $rootPath . '/Resources/js/Angular/';

        $em = $this->getDoctrine()->getManager('db_connection');
        $t =  $em->getRepository('Lugh\DbConnectionBundle\Entity\Template')->find($id);

        $files = array(
            'workspace_base',
            $this->getTemplate($id)
        );

        $stack = array();
        $template = array();
        foreach ($files as $file)
        {
            $path = $basePath.$file.'/i18n/resources-locale_'.$lang.'.js';
            if (file_exists($path))
            {
                if($file == $this->getTemplate($id)){
                    $template = array_merge( $template,json_decode(file_get_contents($path),true));
                }
                $stack = array_merge($stack,json_decode(file_get_contents($path),true));
            }
        }

        return array($stack,$template);
    }

    private function getLangs(){

        return $this->get('lugh.langs.db_connection')->from(-1);
    }

    private function encodeOutput( $input ){
        $replacedString = preg_replace("/\\\\u([0-9abcdef]{4})/", "&#x$1;", $input );
        $unicodeString = mb_convert_encoding($replacedString, 'UTF-8', 'HTML-ENTITIES');
        return $unicodeString;
    }

}
