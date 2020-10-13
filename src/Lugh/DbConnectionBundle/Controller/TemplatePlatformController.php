<?php

namespace Lugh\DbConnectionBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Lugh\DbConnectionBundle\Lib\PlatformsManager;
use Symfony\Component\Config\Definition\Exception\Exception;
use Lugh\WebAppBundle\Entity\Parametros;
use Lugh\DbConnectionBundle\Entity\Template as TemplateDBconnection;
use Symfony\Component\DomCrawler\Crawler;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Dumper;


/**
 * @Route("/templatesplatform")
 */
class TemplatePlatformController extends Controller
{
    /**
     * @Route("/" ,name="_templatesplatform_index")
     * @Template()
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * @Route("/styles" ,name="_temlatesplatform_content")
     * @Template()
     */
    public function stylesAction()
    {
        $em = $this->getDoctrine()->getManager('db_connection');
        $templates =  $em->getRepository('Lugh\DbConnectionBundle\Entity\Template')->findAll();

        return array('templates' => $templates);
    }

    /**
     * @Route("/platformscontent" ,name="_temlatesplatform_styles_content")
     * @Template()
     */
    public function stylesContentAction()
    {
        $em = $this->getDoctrine()->getManager('db_connection');
        $templates =  $em->getRepository('Lugh\DbConnectionBundle\Entity\Template')->findAll();
        if($templates[0]->getName() === "default"){
            unset($templates[0]);
        }
        return array('templates' => $templates);
    }



    /**
     * @Route("/templates" ,name="_templatesplatform_templates")
     * @Template()
     */
    public function contentTemplatesAction()
    {
        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $template_id = $request->get('template_id');
        return array('template_id' => $template_id);
    }


    /**
     * @Route("/colorstablecontent" ,name="_templatesplatform_colors_table_content")
     * @Template()
     */
    public function colorsTableContentAction()
    {
        $em = $this->getDoctrine()->getManager('db_connection');
        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $template_id = $request->get('template_id');
        $template =  $em->getRepository('Lugh\DbConnectionBundle\Entity\Template')->find($template_id);
        $colors = array();
        if ($template->getPath() != null) {
            $lessFile = $this->readLessFile($template->getPath());
            $params = $this->parseLess($lessFile);
        }
        return array('params' => $params);
    }

//-------------------------------------------------------------------------------------------------------------------

    /**
     * @Route("/logotablecontent" ,name="_templatesplatform_logo_table_content")
     * @Template()
     */
    public function logoTableContentAction()
    {

        $em = $this->getDoctrine()->getManager('db_connection');
        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $template_id = $request->get('template_id');
        $template =  $em->getRepository('Lugh\DbConnectionBundle\Entity\Template')->find($template_id);

        return array('template' => $template_id,'style' => $template->getPath());
    }

    /**
     * @Route("/savelogo" ,name="_template_save_logo")
     */
    public function savelogoAction()
    {

        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $template_id = $request->get('template_id');
        $em = $this->getDoctrine()->getManager('db_connection');
        $template =  $em->getRepository('Lugh\DbConnectionBundle\Entity\Template')->find($template_id);

        $kernel = $this->get('kernel');
        $bundle = $kernel->getBundle('LughWebAppBundle');
        $rootPath = $bundle->getPath();
        $basePath = $rootPath.DIRECTORY_SEPARATOR.'Resources'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'css';

        $style = $template->getPath();


        if (!empty($_FILES)) {
            $allowed = array();
            $allowed[] ='logo';

            $path = realpath($basePath.DIRECTORY_SEPARATOR.$style.DIRECTORY_SEPARATOR);
            if(!($path !== false AND is_dir($path))){
                mkdir($basePath.DIRECTORY_SEPARATOR.$style.DIRECTORY_SEPARATOR);
            }
            $path = realpath($basePath.DIRECTORY_SEPARATOR.$style.DIRECTORY_SEPARATOR.'img');
            if(!($path !== false AND is_dir($path))){
                mkdir($basePath.DIRECTORY_SEPARATOR.$style.DIRECTORY_SEPARATOR.'img');
            }

            foreach( $allowed as $filename ){

                if(isset($_FILES[$filename]) && $_FILES[$filename]['error'] == 0){
                    $uploadfile = $basePath.DIRECTORY_SEPARATOR.$style.DIRECTORY_SEPARATOR.'img'.DIRECTORY_SEPARATOR.'logo.png';
                    if (move_uploaded_file($_FILES[$filename]['tmp_name'], $uploadfile)) {
                        
                        $this->get('lugh.image.db_connection')->resizeImage($uploadfile,220,70);

                        return new Response('{success:1}');
                    } else {
                        return new Response('{error:0}');
                    }
                }

            }

            return new Response('{error:-1}');
        }
        return new Response('{error:1}');
    }

    /**
     * @Route("/loadlogo" ,name="_template_load_logo")
     */
    public function loadLogoAction()
    {

        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $template_id = $request->get('template_id');
        $em = $this->getDoctrine()->getManager('db_connection');
        $template =  $em->getRepository('Lugh\DbConnectionBundle\Entity\Template')->find($template_id);

        $style = $template->getPath();

        $kernel = $this->get('kernel');
        $bundle = $kernel->getBundle('LughWebAppBundle');
        $rootPath = $bundle->getPath();
        $basePath = $rootPath.DIRECTORY_SEPARATOR.'Resources'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'css';

        $result = array();
        $storeFolder = '';

        $path = realpath($basePath.DIRECTORY_SEPARATOR.$style.DIRECTORY_SEPARATOR);
        if(!($path !== false AND is_dir($path))){
            mkdir($basePath.DIRECTORY_SEPARATOR.$style.DIRECTORY_SEPARATOR);
        }
        $path = realpath($basePath.DIRECTORY_SEPARATOR.$style.DIRECTORY_SEPARATOR.'img');
        if(!($path !== false AND is_dir($path))){
            mkdir($basePath.DIRECTORY_SEPARATOR.$style.DIRECTORY_SEPARATOR.'img');
        }

        $storeFolder = $basePath.DIRECTORY_SEPARATOR.$style.DIRECTORY_SEPARATOR.'img'.DIRECTORY_SEPARATOR;
        $files = scandir($storeFolder);                 //1
        if ( $files !== false )
        {
            foreach ( $files as $file )
            {
                if ( '.'!=$file && '..'!=$file)
                {                                       //2
                    if ( strpos($file,'logo') !== false )
                    {
                        $obj['name'] = $file;
                        $obj['size'] = filesize($storeFolder.DIRECTORY_SEPARATOR.$file);
                        $result[] = $obj;
                    }
                }
            }
        }

        header('Content-type: text/json');              //3
        header('Content-type: application/json');
        return new Response(json_encode($result));

    }

    /**
     * @Route("/removelogo" ,name="_template_remove_logo")
     */
    public function removeLogoAction()
    {
        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $template_id = $request->get('template_id');
        $em = $this->getDoctrine()->getManager('db_connection');
        $template =  $em->getRepository('Lugh\DbConnectionBundle\Entity\Template')->find($template_id);

        $style = $template->getPath();

        $kernel = $this->get('kernel');
        $bundle = $kernel->getBundle('LughWebAppBundle');
        $rootPath = $bundle->getPath();
        $basePath = $rootPath.DIRECTORY_SEPARATOR.'Resources'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'css';

        $result = array();
        $storeFolder = '';

        $path  = $basePath.DIRECTORY_SEPARATOR.$style.DIRECTORY_SEPARATOR.'img'.DIRECTORY_SEPARATOR;
        $filename   = $request->get('filename');

        if($path !== false AND is_dir($path)){
            try{
                unlink($path . $filename);
            }
            catch(Exception $exc){
                return new Response($exc.getMessage());
            }

            return new Response('{success: 1}');
        }
        else{
            return new Response('{error: 0}');
        }
    }

//-------------------------------------------------------------------------------------------------------------------

    /**
     * @Route("/addstyle" ,name="_templatesplatform_add_style")
     * @Template()
     */
    public function addStyleAction()
    {
        try{
            $em = $this->getDoctrine()->getManager('db_connection');
            $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
            $params = $request->get('params')['form'];

            $reference =  $em->getRepository('Lugh\DbConnectionBundle\Entity\Template')->find( $params['newstyle-modal-select'] );
            $path = $reference->getPath();
            if(!$path)
                $path = 'default';
            $colors =  $this->readLessFile($path) ;

            $name   = $params['newstyle-modal-name'];
            $path2   = $params['newstyle-modal-path'];

            if( $this->parseLess( $this->readLessFile($path2) ) === false )
                throw new Exception('Requested file path already exists');

            $this->writeLessFile($path2,$colors);
            $this->cloneMailTemplate($path,$path2);


            $template = new TemplateDBconnection();
            if( $name )
                $template->setName( $name );
            if( $path2 )
                $template->setPath( $path2 );

            $em->persist($template);
            $em->flush();

        }catch (Exception $exc) {

            return new Response(json_encode(array('error'=> $exc->getMessage())));
        }
        return new Response(json_encode(array('success'=> '1')));
    }

    /**
     * @Route("/savestyle" ,name="_templatesplatform_save_style")
     * @Template()
     */
    public function saveStyleAction()
    {
        $em = $this->getDoctrine()->getManager('db_connection');
        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $params = $request->get('params');

        $ids = $params['id']['parameter'];
        $colors = $params['form'];

        $template_id = $request->get('template_id');
        $template =  $em->getRepository('Lugh\DbConnectionBundle\Entity\Template')->find($template_id);
        $oldLessPath = $this->readLessFile($template->getPath());
        $oldColors = $this->parseLess( $oldLessPath );


        foreach($oldColors as $index => $oldColor){

            if($oldColor['key'] ===  $ids[$index]){
                if( isset($colors[$ids[$index]]) ){
                    $oldColors[$index]['value'] = $colors[$ids[$index]];
                }
            }
        }
        $data = $this->recodeLess($oldLessPath,$ids, $oldColors);

        try{
            $this->writeLessFile( $template->getPath(),$data );
        }
        catch (\Exception $exc) {
            return new Response(json_encode(array('error'=> $exc->getMessage())));
        }
        return new Response(json_encode(array('success'=> '1')));
    }
/*------------------------------------------------------------------------------*/
    /**
     * @Route("/translations-template", name="_translationstemplate_translations")
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

        for($i = 0; $i < sizeof($tagIDs); $i++ ){
            $tag = $tagIDs[$i];
            foreach ( $langs as $lang => $value){
                $orderedTemplate[$tag][$lang] = ( array_key_exists($tag,$template[$lang]) )? $template[$lang][$tag] : '';
            }
        }
        return array('template_id' => $template_id, 'tags_stack' => $stack, 'tags_template' => $orderedTemplate);
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
                if ( file_exists($folder.'i18n'.DIRECTORY_SEPARATOR.'resources-locale_'.$lang.'.js') ){
                    $oldFileContent = json_decode(file_get_contents($folder.'i18n'.DIRECTORY_SEPARATOR.'resources-locale_'.$lang.'.js'),true);
                }

                if(true || strcasecmp($data['value'],"") > 0)
                {
                    $this->createBackupFromFile($folder.'i18n'.DIRECTORY_SEPARATOR, 'resources-locale_'.$data['lang'], '.js', $date);
                    $newTag = array($data['tag'] => $data['value']);

                    $newFileContent = $this->encodeOutput(json_encode( array_merge($oldFileContent,$newTag), JSON_PRETTY_PRINT));
                    file_put_contents($folder.'i18n'.DIRECTORY_SEPARATOR.'resources-locale_'.$lang.'.js', $newFileContent);
                }

            }

        }catch(\Exception $exc){
            return new Response(json_encode(array('error'=> $exc->getMessage())));
        }
        return new Response(json_encode(array('success'=> '2', 'data' => $tagData )));

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
                if ( file_exists($folder.'i18n'.DIRECTORY_SEPARATOR.'resources-locale_'.$lang.'.js') )
                    $oldFileContent = json_decode(file_get_contents($folder.'i18n'.DIRECTORY_SEPARATOR.'resources-locale_'.$lang.'.js'),true);

                if(true || strcasecmp($data['value'],"") > 0)
                {
                    $this->createBackupFromFile($folder.'i18n'.DIRECTORY_SEPARATOR, 'resources-locale_'.$data['lang'], '.js', $date);
                    $newTag = array($data['tag'] => $data['value']);

                    $newFileContent = $this->encodeOutput(json_encode( array_merge($oldFileContent,$newTag), JSON_PRETTY_PRINT));

                    file_put_contents($folder.'i18n'.DIRECTORY_SEPARATOR.'resources-locale_'.$lang.'.js', $newFileContent);
                }

            }

        }catch(\Exception $exc){
            return new Response(json_encode(array('error'=> $exc->getMessage())));
        }
        return new Response(json_encode(array('success'=> '1', 'data' => $tagData )));

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
                if ( file_exists($folder.'i18n'.DIRECTORY_SEPARATOR.'resources-locale_'.$lang.'.js') ){
                    $oldFileContent = json_decode(file_get_contents($folder.'i18n'.DIRECTORY_SEPARATOR.'resources-locale_'.$lang.'.js'),true);
                    unset( $oldFileContent[$data['tag']] );
                    $newFileContent = $this->encodeOutput(json_encode( $oldFileContent, JSON_PRETTY_PRINT));
                    file_put_contents($folder.'i18n'.DIRECTORY_SEPARATOR.'resources-locale_'.$lang.'.js', $newFileContent);
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
        $basePath = $rootPath .DIRECTORY_SEPARATOR. 'Resources'.DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR.'Angular'.DIRECTORY_SEPARATOR;

        $files = array(
            'workspace_base',
        );
        $tagData = array();
        try{
            $langs = $this->getLangs();

            foreach( $langs as $lang => $value ){
                $tagDataLang = array();
                foreach ($files as $file){
                    $path = $basePath.$file.DIRECTORY_SEPARATOR.'i18n'.DIRECTORY_SEPARATOR.'resources-locale_'.$lang.'.js';
                    if (file_exists($path)){
                        $aux = json_decode(file_get_contents($path),true);
                        if(isset($aux[$tag]))
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

/*------------------------------------------------------------------------------*/
    /**
     * @Route("/mails-template", name="_mailstemplate_mails")
     * @Template()
     */
    public function contentMailsAction(){
        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $template_id = $request->get('template_id');
        $t = $this->getTemplate($template_id);

        $tags_base = $this->readYamlFile('messages');
        $tags_template =  $this->readYamlFile( $t );
        $langs = $this->getLangs($template_id);
        $tags_stack   = array();

        foreach ( $langs as $lang => $value){
                $tags_stack[$lang] = array_merge($tags_base[$lang],$tags_template[$lang]);
        }

        $tagIDs = array();
        $orderedTemplate = array();
        foreach ( $langs as $lang => $value){
                $tagIDs = array_merge($tagIDs, array_keys( $tags_template[$lang] ) );
        }

        for($i = 0; $i < sizeof($tagIDs); $i++ ){
            $tag = $tagIDs[$i];
            foreach ( $langs as $lang => $value){
                    $orderedTemplate[$tag][$lang] = ( array_key_exists($tag,$tags_template[$lang]) )? $tags_template[$lang][$tag] : '';
            }
        }

        return array('template_id' => $template_id, 'tags_stack' => $tags_stack, 'tags_template' => $orderedTemplate);
    }

/*------------------------------------------------------------------------------*/
    /**
     * @Route("/mails-plantilla", name="_mailsplantilla")
     * @Template()
     */
    public function contentMailsPlantillaAction(){

        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $template_id = $request->get('template_id');

        $kernel = $this->get('kernel');
        $bundle = $kernel->getBundle('LughWebAppBundle');
        $rootPath = $bundle->getPath();
        $basePath = $rootPath . DIRECTORY_SEPARATOR.'Resources'.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'Mail'.DIRECTORY_SEPARATOR;
        $base = $request->server->get('BASE')?$request->server->get('BASE') : '';

        $file = file_get_contents($basePath . $this->getTemplate( $template_id ) . '.html.twig');
        $logo_path = $base.'/bundles/lughwebapp/css/'.$this->getTemplate( $template_id ).'/img/logo.png';
        if(!file_exists($logo_path))
            $logo_path = $base.'/bundles/lugh/workspace_base/logo.png';
        $file = str_replace('{{ app.request.getSchemeAndHttpHost() }}/{{ app.request.getBasePath() }}/bundles/lugh/workspace_base/logo.png',$logo_path,$file);
        $widget['id']    ='html';
        $widget['clase'] = 'html';
        $widget['html']  = $this->getWidget('textarea', 'html_html', $file);

        return array('template_id' => $template_id, 'plantilla' => $file, 'widget'=> $widget);
    }

    /**
     * @Route("/mails-plantilla-save-", name="_mailsplantilla-save")
     * @Template()
     */
    public function saveMailsPlantillaAction(){

        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $html = $request->get('html');
        $template_id = $request->get('template_id');

        $base = $request->server->get('BASE')?$request->server->get('BASE') : '';

        $logo_path =  $base .'/bundles/lughwebapp/css/'.$this->getTemplate( $template_id ).'/img/logo.png';
        if(!file_exists($logo_path))
            $logo_path = $base . '/bundles/lugh/workspace_base/logo.png';
        $html = str_replace($logo_path,'{{ app.request.getSchemeAndHttpHost() }}/{{ app.request.getBasePath() }}/bundles/lugh/workspace_base/logo.png',$html);

        $kernel = $this->get('kernel');
        $bundle = $kernel->getBundle('LughWebAppBundle');
        $rootPath = $bundle->getPath();
        $basePath = $rootPath . DIRECTORY_SEPARATOR.'Resources'.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'Mail'.DIRECTORY_SEPARATOR;

        try{
            file_put_contents($basePath . $this->getTemplate( $template_id ) . '.html.twig',$html);
        }catch(\Exception $exc){
            return new Response(json_encode(array('error'=> $exc->getMessage())));
        }
        return new Response(json_encode(array('success'=> '1')));
    }
/*-------------------------------------------------------------------------------*/
    /**
     * @Route("/template-preview", name="_templatesplatform_preview")
     * @Template()
     */
    public function previewTemplateContentAction(){

        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $template_id = $request->get('template_id');
        $base = $request->server->get('BASE')?$request->server->get('BASE') : '';
        $path = $base . DIRECTORY_SEPARATOR.'bundles'.DIRECTORY_SEPARATOR.'lughwebapp'.DIRECTORY_SEPARATOR;

        return array('path' => $path, 'style' => $this->getTemplate( $template_id ) );
    }
/*-------------------------------------------------------------------------------*/
    private function readYamlFile($style_path){
        $out = array();
        try{
            $kernel = $this->get('kernel');
            $bundle = $kernel->getBundle('LughWebAppBundle');
            $rootPath = $bundle->getPath();
            $langs = $this->getLangs();
            foreach ($langs as $lang => $nothing){
                $path = $rootPath . DIRECTORY_SEPARATOR."Resources".DIRECTORY_SEPARATOR."translations".DIRECTORY_SEPARATOR . $style_path . "." . $lang . ".yml";
                $out[$lang] = array();
                if (file_exists($path)){
                    $yaml = file_get_contents($path);
                    $parser = new Parser();
                    $out[$lang] = $parser->parse($yaml);
                }
            }
        }
        catch(\Exception $exc){
            throw new Exception($exc->getMessage());
            return false;
        }
        return $out;
    }
    /**
     * @Route("/newmailtag", name="_mailstemplate_newmailtag", options={"expose"= true})
     * @Template()
     */
    public function newMailTagAction(){



        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $tagData = $request->get('data');
        $template_id = $request->get('template');


        $kernel = $this->get('kernel');
        $bundle = $kernel->getBundle('LughWebAppBundle');
        $rootPath = $bundle->getPath();

        try{
            //Check if file already exists & makes a backup
            $langs = $this->getLangs();
            $style_path =  $this->getTemplate( $template_id );
            $date = date("_ymd-Hi");



            foreach ( $langs as $lang => $value )
            {

                $data = $tagData[$lang];

                $oldFileContent = array();
                if ( file_exists($rootPath . DIRECTORY_SEPARATOR."Resources".DIRECTORY_SEPARATOR."translations" .DIRECTORY_SEPARATOR. $style_path . "." . $lang . ".yml") ){
                    $parser = new Parser();
                    $oldFileContent = $parser->parse(file_get_contents( $rootPath . DIRECTORY_SEPARATOR."Resources".DIRECTORY_SEPARATOR."translations" .DIRECTORY_SEPARATOR . $style_path . "." . $lang . ".yml"));
                }

                if(true || strcasecmp($data['value'],"") > 0)
                {
                    $this->createBackupFromFile($rootPath . DIRECTORY_SEPARATOR."Resources".DIRECTORY_SEPARATOR."translations" .DIRECTORY_SEPARATOR, $style_path . "." . $lang , ".yml", $date);
                    $newTag = array($data['tag'] => $data['value']);

                    $newFileContent =  array_merge($oldFileContent,$newTag);
                    $dumper = new Dumper();
                    file_put_contents($rootPath . DIRECTORY_SEPARATOR."Resources".DIRECTORY_SEPARATOR."translations" .DIRECTORY_SEPARATOR . $style_path . "." . $lang . ".yml", $dumper->dump($newFileContent, 1));
                }

            }

        }catch(\Exception $exc){
            return new Response(json_encode(array('error'=> $exc->getMessage())));
        }
        return new Response(json_encode(array('success'=> '2', 'data' => $tagData )));


    }
    /**
     * @Route("/savemailtag", name="_mailstemplate_savemailtag", options={"expose"= true})
     * @Template()
     */
    public function saveMailTagAction(){

        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $tagData = $request->get('data');
        $template_id = $request->get('template');


        $kernel = $this->get('kernel');
        $bundle = $kernel->getBundle('LughWebAppBundle');
        $rootPath = $bundle->getPath();
        try{
            //Check if file already exists & makes a backup
            $langs = $this->getLangs();
            $style_path =  $this->getTemplate( $template_id );
            $date = date("_ymd-Hi");

            foreach ( $langs as $lang => $value )
            {
                $data = $tagData[$lang];

                $oldFileContent = array();
                if ( file_exists($rootPath . DIRECTORY_SEPARATOR."Resources".DIRECTORY_SEPARATOR."translations" .DIRECTORY_SEPARATOR . $style_path . "." . $lang . ".yml") ){
                    $parser = new Parser();
                    $oldFileContent = $parser->parse(file_get_contents( $rootPath . DIRECTORY_SEPARATOR."Resources".DIRECTORY_SEPARATOR."translations" .DIRECTORY_SEPARATOR . $style_path . "." . $lang . ".yml"));
                }

                if(true || strcasecmp($data['value'],"") > 0)
                {

                    $this->createBackupFromFile($rootPath . DIRECTORY_SEPARATOR."Resources".DIRECTORY_SEPARATOR."translations" .DIRECTORY_SEPARATOR, $style_path . "." . $lang , ".yml", $date);
                    $newTag = array($data['tag'] => $data['value']);

                    $newFileContent =  array_merge($oldFileContent,$newTag);
                    $dumper = new Dumper();
                    file_put_contents($rootPath . DIRECTORY_SEPARATOR."Resources".DIRECTORY_SEPARATOR."translations" .DIRECTORY_SEPARATOR . $style_path . "." . $lang . ".yml", $dumper->dump($newFileContent, 1));
                }

            }

        }catch(\Exception $exc){
            return new Response(json_encode(array('error'=> $exc->getMessage())));
        }
        return new Response(json_encode(array('success'=> '1', 'data' => $tagData )));



    }
    /**
     * @Route("/removemailtag", name="_mailstemplate_removemailtag", options={"expose"= true})
     * @Template()
     */
    public function removeMailTagAction(){

        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $tagData = $request->get('data');
        $template_id = $request->get('template');

        $kernel = $this->get('kernel');
        $bundle = $kernel->getBundle('LughWebAppBundle');
        $rootPath = $bundle->getPath();
        try{
            //Check if file already exists & makes a backup
            $langs = $this->getLangs();
            $style_path =  $this->getTemplate( $template_id );
            $date = date("_ymd-Hi");

            foreach ( $langs as $lang => $value )
            {
                $data = $tagData[$lang];

                $oldFileContent = array();
                if ( file_exists($rootPath . DIRECTORY_SEPARATOR."Resources".DIRECTORY_SEPARATOR."translations" .DIRECTORY_SEPARATOR . $style_path . "." . $lang . ".yml") ){
                    $parser = new Parser();
                    $oldFileContent = $parser->parse(file_get_contents($rootPath . DIRECTORY_SEPARATOR."Resources".DIRECTORY_SEPARATOR."translations" .DIRECTORY_SEPARATOR . $style_path . "." . $lang . ".yml"));
                    unset( $oldFileContent[$data['tag']] );

                    $dumper = new Dumper();
                    file_put_contents($rootPath . DIRECTORY_SEPARATOR."Resources".DIRECTORY_SEPARATOR."translations" .DIRECTORY_SEPARATOR . $style_path . "." . $lang . ".yml", $dumper->dump($oldFileContent, 1));
                }
            }

        }catch(\Exception $exc){
            return new Response(json_encode(array('error'=> $exc->getMessage())));
        }
        return new Response(json_encode(array('success'=> '3', 'data' => $tagData )));
    }

    /**
     * @Route("/getmailtag", name="_mailstemplate_getmailtag", options={"expose"= true})
     * @Template()
     */
    public function getMailTagAction(){


        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $template_id = $request->get('template');
        $tag = $request->get('data');

        $kernel = $this->get('kernel');
        $bundle = $kernel->getBundle('LughWebAppBundle');
        $rootPath = $bundle->getPath();
        $basePath = $rootPath . DIRECTORY_SEPARATOR.'Resources'.DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR.'Angular'.DIRECTORY_SEPARATOR;

        $files = array(
            'workspace_base',
        );
        $tagData = array();
        try{
            $langs = $this->getLangs();
            $tags_base = $this->readYamlFile('messages');
            foreach( $langs as $lang => $value ){
                if(isset($tags_base[$lang][$tag]))
                    $tagData[$lang][$tag] = $tags_base[$lang][$tag];
            }
        }catch(\Exception $exc){
            return new Response(json_encode(array('error'=> $exc->getMessage())));
        }

        return new Response( $this->encodeOutput(json_encode( array('tag' => $tag, 'data' => $tagData) ) ) );
    }


    private function readLessFile($style)
    {
        try{
            $kernel = $this->get('kernel');
            $bundle = $kernel->getBundle('LughDbConnectionBundle');
            $rootPath = $bundle->getPath();
            $file_less = $rootPath . DIRECTORY_SEPARATOR.'Resources'.DIRECTORY_SEPARATOR.'less'.DIRECTORY_SEPARATOR . $style . '.less';
            return file_get_contents($file_less);
        }
        catch(\Exception $exc){
            return false;
        }

    }

    private function createBackupFromFile($filePath, $fileName, $ext, $date = false ){
        if(!$date)
            $date = date("_ymd-Hi");
        try{

            if (file_exists($filePath.$fileName.$ext)){
                $oldFileContent = file_get_contents($filePath.$fileName.$ext);

                $path = realpath($filePath.DIRECTORY_SEPARATOR.'backups'.DIRECTORY_SEPARATOR);
                if(!($path !== false AND is_dir($path))){
                    mkdir($filePath.DIRECTORY_SEPARATOR.'backups'.DIRECTORY_SEPARATOR);
                }

                $path = realpath($filePath.DIRECTORY_SEPARATOR.'backups'.DIRECTORY_SEPARATOR.$fileName.DIRECTORY_SEPARATOR);
                if(!($path !== false AND is_dir($path))){
                    mkdir($filePath.DIRECTORY_SEPARATOR.'backups'.DIRECTORY_SEPARATOR.$fileName.DIRECTORY_SEPARATOR);
                }

                file_put_contents($filePath.DIRECTORY_SEPARATOR.'backups'.DIRECTORY_SEPARATOR.$fileName.DIRECTORY_SEPARATOR.$fileName.$date.$ext,$oldFileContent);
            }

        } catch(\Exception $exc){
            throw new Exception( $exc->getMessage() );
        }
        return 1;
    }

    private function writeLessFile($style,$data){

        try{
            $kernel = $this->get('kernel');
            $bundle = $kernel->getBundle('LughDbConnectionBundle');
            $rootPath = $bundle->getPath();
            $file_less = $rootPath . DIRECTORY_SEPARATOR.'Resources'.DIRECTORY_SEPARATOR.'less' .DIRECTORY_SEPARATOR. $style . '.less';
            $this->createBackupFromFile($rootPath . DIRECTORY_SEPARATOR.'Resources'.DIRECTORY_SEPARATOR.'less'.DIRECTORY_SEPARATOR, $style,  '.less');
            $o = file_put_contents($file_less,$data);

        }
        catch(\Exception $exc){
            throw new Exception($exc->getMessage());
        }

            $bundle = $kernel->getBundle('LughWebAppBundle');
            $rootPath = $bundle->getPath();
            //$css_folder = $rootPath . "\Resources\less\css\\";
            $css_folder = $rootPath . DIRECTORY_SEPARATOR."Resources".DIRECTORY_SEPARATOR."public".DIRECTORY_SEPARATOR."css".DIRECTORY_SEPARATOR;

            $path = realpath($css_folder.$style.DIRECTORY_SEPARATOR);
            if(!($path !== false AND is_dir($path))){
                mkdir($css_folder.$style.DIRECTORY_SEPARATOR);
            }

            $output_path =  $css_folder.$style.DIRECTORY_SEPARATOR.'style.css';

            try {
                $parser = new \Less_Parser();
                $parser->parseFile( $file_less );
                $css = $parser->getCss();
                file_put_contents($output_path, $css);
            } catch (\Exception $e) {
                throw new Exception($e->getMessage());
            }

            $path = $rootPath . DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR;
            set_time_limit(120);
            exec('php '.$path.'app'.DIRECTORY_SEPARATOR.'console assets:install ' . $path . 'web',
                $output, $ret);
            set_time_limit(30);
            if ($ret != 0) {
                throw new Exception("Command assets:install no run correctly");
            }

        return $o;
    }

    private function parseLess($lessFile)
    {
        $variables = array(
            /* Colores */
            array('brand-primary'   ,'color',1),//*
            array('brand-success'   ,'color',1),//*
            array('brand-info'      ,'color',0),
            array('brand-info-alt'  ,'color',0),
            array('brand-warning'   ,'color',0),
            array('brand-danger'    ,'color',0),

            array('bg-foro'         ,'color',0),
            array('bg-voto'         ,'color',0),
            array('bg-derecho'      ,'color',0),
            array('bg-av'           ,'color',0),

            array('dark'            ,'color',0),
            array('bright'          ,'color',0),
            array('reverse'         ,'color',1),//*
            array('body-bg'         ,'color',0),

            array('text-color'      ,'color',1),//*
            array('link-color'      ,'color',1),//*
            array('link-hover-color','color',1),//*

            /*Tamaño PX*/
            array('header_height'   ,'size',1), //*

            /* Fonts */

            array('font-family-sans-serif'  ,'font',0),//:  Verdana, Geneva, Arial, Helvetica, sans-serif;
            array('font-family-serif'       ,'font',0),//:  = @font-family-sans-serif
            array('font-family-base'        ,'font',1),// = @font-family-sans-serif  //*

            /*Tamaño PX*/
            array('font-size-base'          ,'size',0)//: 12px

        );

        $params = array();
        $less = substr($lessFile, 0, 30000);
        foreach ($variables as $variable) {
            $pos_start = strpos($less, '@' . $variable[0] . ':');
            $pos_end = strpos($less, ';', $pos_start);
            $param['key'] = $variable[0];
            list($key,$param['value']) = sscanf(substr($less, $pos_start, $pos_end-$pos_start), "%s%*[ ]%[^\n]s");
            $param['type'] = $variable[1];
            $param['relevant'] = $variable[2];
            $param['alt'] = $param['value'];
            $params[] = $param;
        }

        foreach( $params as $param ){

            if($param['type'] == 'color'){
                switch($param['value'][0]){
                    case '@':
                        $clave1 = array_search($param['key'], $params); // $clave = 2;
                        $clave2 = array_search(substr($param['value'], 1), $params); // $clave = 2;
                        $params[$clave1]['alt'] = $params[$clave2]['value'];
                        break;
                    case '#':break;
                    default:
                        $clave1 = array_search($param['key'], $params); // $clave = 2;
                        $params[$clave1]['alt'] = '#EEEEEE';
                        break;
                }
            }

        }
        return $params;
    }

    private function recodeLess($lessFile,$ids, $colors)
    {
        $less = $lessFile;//substr($lessFile, 0, 30000);

        foreach ($colors as $index => $variable) {
            $color = explode(';', $variable['value']);
            $pos_start = strpos($less, '@' . $variable['key'] . ':');
            $pos_end = strpos($less, ';', $pos_start);

            $string = substr($less, $pos_start, $pos_end-$pos_start);
            list($key,$value) = sscanf(substr($less, $pos_start, $pos_end-$pos_start), "%s%*[ ]%[^\n]s");
            $replacement = substr_replace($string,$color,strlen($string)-strlen($value),$pos_end-$pos_start);

            $less = substr_replace($less,$replacement,$pos_start,strlen($string));
        }
        return $less;

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

    private function getLangs(){

        return $this->get('lugh.langs.db_connection')->from(-1);
    }

    private function getTagsFor($id,$lang){

        $kernel = $this->get('kernel');
        $bundle = $kernel->getBundle('LughWebAppBundle');
        $rootPath = $bundle->getPath();
        $basePath = $rootPath . DIRECTORY_SEPARATOR.'Resources'.DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR.'Angular'.DIRECTORY_SEPARATOR;

        $em = $this->getDoctrine()->getManager('db_connection');
        //$t =  $em->getRepository('Lugh\DbConnectionBundle\Entity\Template')->find($id);

        $files = array(
            'workspace_base',
            $this->getTemplate($id)
        );

        $stack = array();
        $template = array();
        foreach ($files as $file)
        {
            $path = $basePath.$file.DIRECTORY_SEPARATOR.'i18n'.DIRECTORY_SEPARATOR.'resources-locale_'.$lang.'.js';
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

    private function encodeOutput( $input ){
        $replacedString = preg_replace("/\\\\u([0-9abcdef]{4})/", "&#x$1;", $input );
        $unicodeString = mb_convert_encoding($replacedString, 'UTF-8', 'HTML-ENTITIES');
        return $unicodeString;
    }

    private function getTemplateFolderPath( $template_id ){
        $kernel = $this->get('kernel');
        $bundle = $kernel->getBundle('LughWebAppBundle');
        $rootPath = $bundle->getPath();
        $basePath = $rootPath . DIRECTORY_SEPARATOR.'Resources'.DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR.'Angular'.DIRECTORY_SEPARATOR;

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
            throw new Exception($exc->getMessage());
        }
        return $folder;
    }

    private function getTemplate($template_id)
    {
        $em = $this->getDoctrine()->getManager('db_connection');
        $t =  $em->getRepository('Lugh\DbConnectionBundle\Entity\Template')->find($template_id);
        return $t->getPath();
    }

    private function cloneMailTemplate($path,$path2){
        $kernel = $this->get('kernel');
        $bundle = $kernel->getBundle('LughWebAppBundle');
        $rootPath = $bundle->getPath();
        $basePath = $rootPath . DIRECTORY_SEPARATOR.'Resources'.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'Mail'.DIRECTORY_SEPARATOR;

        if( file_exists($basePath . $path . '.html.twig' ) AND !is_dir($basePath . $path . '.html.twig' ) )
        {
            file_put_contents($basePath . $path2 . '.html.twig', file_get_contents($basePath . $path . '.html.twig'));
        }else{
            file_put_contents($basePath . $path2 . '.html.twig', file_get_contents($basePath . 'default.html.twig'));
        }
    }

}
