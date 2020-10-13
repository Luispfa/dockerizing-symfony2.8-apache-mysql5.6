<?php

namespace Lugh\DbConnectionBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Response;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Lugh\DbConnectionBundle\Lib\PlatformsManager;

/**
 * @Route("/styleplatform")
 */
class StylePlatformController extends Controller{

	/**
     * @Route("/" ,name="_styleplatform_index")
     * @Template()
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * @Route("/style" ,name="_styleplatform_colors_content")
     * @Template()
     */
    public function stylesAction(){

        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $platform_id = $request->get('platform_id');
        $platform = $this->getEm($platform_id);
        
        $style = $this->getHost($platform_id);     
        $lessFile = $this->readLessFile($style);
        if(!$lessFile){
            $style = $this->getTemplate($platform_id);   
            $lessFile = $this->readLessFile($style);
        }
        if(!$lessFile){
            $style = 'defaut';
            $lessFile = $this->readLessFile($style);
        }

        $params = $this->parseLess($lessFile);


        $logoPath = $this->getHost($platform_id);

        return array('params' => $params,'platform' => $platform_id,'style' => $style, 'logoPath' =>  $logoPath);

    }

    
    /**
     * @Route("/savestyle" ,name="_styleplatform_colors_save")
     * @Template()
     */
    public function saveStyleAction()
    {

        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $platform_id = $request->get('platform_id');
        $platform = $this->getEm($platform_id);

        $em = $this->getDoctrine()->getManager('db_connection');

        $params = $request->get('params');
        $ids    = $params['id']['parameter'];
        $colors = $params['form'];

        $oldLessPath = $this->readLessFile($this->getHost($platform_id));
        if(!$oldLessPath){
            $oldLessPath = $this->readLessFile($this->getTemplate($platform_id));
        }
        if(!$oldLessPath){
            $oldLessPath = $this->readLessFile('default');
        }

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
            $this->writeLessFile( $this->getHost($platform_id),$data );
        }
        catch (\Exception $exc) {
            return new Response(json_encode(array('error'=> $exc->getMessage())));
        }
        return new Response(json_encode(array('success'=> '1')));
    }

    /**
     * @Route("/removestyle" ,name="_styleplatform_colors_remove")
     */
    public function removeStyleAction()
    {


        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $style = $request->get('style');
        
        try{
            $this->removeStyleFiles($style);
        }
        catch (\Exception $exc) {
            return new Response(json_encode(array('error'=> $exc->getMessage())));
        }
        return new Response(json_encode(array('success'=> '1')));
    }

    

    /**
     * @Route("/logoLoad" ,name="_styleLogo_load")
     */
    public function styleLogoLoadAction(){
        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $platform_id = $request->get('platform_id');
        $em = $this->getDoctrine()->getManager('db_connection');

        

        $kernel = $this->get('kernel');
        $bundle = $kernel->getBundle('LughWebAppBundle');
        $rootPath = $bundle->getPath();
        $basePath = $rootPath.DIRECTORY_SEPARATOR.'Resources'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'css';

        $result = array();
        $storeFolder = '';

        $style = $this->getHost($platform_id);

        $path = realpath($basePath.DIRECTORY_SEPARATOR.$style.DIRECTORY_SEPARATOR);
        if(!($path !== false AND is_dir($path))){
            $style = $this->getTemplate($platform_id);
            $path = realpath($basePath.DIRECTORY_SEPARATOR.$style.DIRECTORY_SEPARATOR);
            if(!($path !== false AND is_dir($path))){
                $style = 'header';
            }
        }
        $path = realpath($basePath.DIRECTORY_SEPARATOR.$style.DIRECTORY_SEPARATOR.'img');
        if(!($path !== false AND is_dir($path))){
            $style = $this->getTemplate($platform_id);
            $path = realpath($basePath.DIRECTORY_SEPARATOR.$style.DIRECTORY_SEPARATOR.'img');
            if(!($path !== false AND is_dir($path))){
                $style = 'header';
            }
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
     * @Route("/logoSave" ,name="_styleLogo_save")
     */
    public function styleLogoSaveAction(){
        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $platform_id = $request->get('platform_id');
        $em = $this->getDoctrine()->getManager('db_connection');
        
        $style = $this->getHost($platform_id);

        $kernel = $this->get('kernel');
        $bundle = $kernel->getBundle('LughWebAppBundle');
        $rootPath = $bundle->getPath();
        $basePath = $rootPath.DIRECTORY_SEPARATOR.'Resources'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'css';

        


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
     * @Route("/logoRemove" ,name="_styleLogo_remove")
     */
    public function styleLogoRemoveAction(){
        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $platform_id = $request->get('platform_id');
        $em = $this->getDoctrine()->getManager('db_connection');

        $style = $this->getHost($platform_id);

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


    public function previewStyleContentAction(){

    }

    
//$basePath = $rootPath.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'web'.DIRECTORY_SEPARATOR.'bundles'.DIRECTORY_SEPARATOR.'lughwebapp'.DIRECTORY_SEPARATOR.'css'.DIRECTORY_SEPARATOR. ;

/* = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = */

    private function getEm($platform_id)
    {
        $em = $this->getDoctrine()->getManager('db_connection');
        $platform =  $em->getRepository('Lugh\DbConnectionBundle\Entity\Auth')->find($platform_id);
        PlatformsManager::switchDb($platform->getDbname());

        return $platform;
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

    private function removeStyleFiles($style){
        
        $kernel = $this->get('kernel');
        $bundle = $kernel->getBundle('LughDbConnectionBundle');
        $rootPath = $bundle->getPath();
        $file_less = $rootPath . DIRECTORY_SEPARATOR.'Resources'.DIRECTORY_SEPARATOR.'less' .DIRECTORY_SEPARATOR. $style . '.less';
        $this->createBackupFromFile($rootPath . DIRECTORY_SEPARATOR.'Resources'.DIRECTORY_SEPARATOR.'less'.DIRECTORY_SEPARATOR, $style,  '.less');
        
        if(file_exists($file_less)){
            unlink($file_less);
        }

        $bundle = $kernel->getBundle('LughWebAppBundle');
        $rootPath = $bundle->getPath();
        $css_folder = $rootPath . DIRECTORY_SEPARATOR."Resources".DIRECTORY_SEPARATOR."public".DIRECTORY_SEPARATOR."css".DIRECTORY_SEPARATOR;
        $path = realpath($css_folder.$style.DIRECTORY_SEPARATOR);
        if($path && file_exists($css_folder.$style.DIRECTORY_SEPARATOR.'style.css')){
            unlink($css_folder.$style.DIRECTORY_SEPARATOR.'style.css');
        }

        $path = $rootPath . DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR;
        set_time_limit(120);
        exec('php '.$path.'app'.DIRECTORY_SEPARATOR.'console assets:install ' . $path . 'web',
            $output, $ret);
        set_time_limit(30);
        if ($ret != 0) {
            throw new Exception("Command assets:install no run correctly");
        }

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