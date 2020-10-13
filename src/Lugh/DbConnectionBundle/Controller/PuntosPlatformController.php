<?php

namespace Lugh\DbConnectionBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Lugh\DbConnectionBundle\Lib\PlatformsManager;
use Symfony\Component\Config\Definition\Exception\Exception;
use Lugh\WebAppBundle\Entity\PuntoDia;
use Lugh\WebAppBundle\Entity\OpcionesVoto;
use Lugh\WebAppBundle\Entity\GrupoOpcionesVoto;
use Lugh\WebAppBundle\Entity\TipoVoto;
use Doctrine\DBAL\DBALException;


/**
 * @Route("/puntosplatform")
 */
class PuntosPlatformController extends Controller
{
    /**
     * @Route("/" ,name="_puntosplatform_index")
     * @Template()
     */
    public function indexAction()
    {
        return array();
    }
    
    
    /**
     * @Route("/edittextpunto/{id}/{platform_id}/{entity}" ,name="_puntosplatform_edittext_punto")
     * @Template()
     */
    public function editTextPuntoAction($id, $platform_id, $entity)
    {
        $platform = $this->getEm($platform_id);  
        $em = $this->getDoctrine()->getManager();
        
        $punto = $em->getRepository('Lugh\WebAppBundle\Entity\\' .$entity)->find($id);

        $L = $this->get('lugh.langs.db_connection');
        $lang_data = $L->from($platform_id);
        $langs = array();
        foreach ($lang_data  as $lg => $value){
            array_push($langs, $L->langCode($lg));
        }

        $repository = $em->getRepository('Gedmo\Translatable\Entity\Translation');
        $puntos_texts = $repository->findTranslations($punto);
        
        $puntos_text = array();
        foreach ($langs as $lang) {
            $punto_text = array();
            $punto_text['lang'] = $lang;
            $point_text = isset($puntos_texts[$lang]) ? reset($puntos_texts[$lang]) : '';
            $punto_text['text'] = $this->getWidget('textarea', $lang, $point_text);
            $puntos_text[] = $punto_text;
        }
        
        return array('puntos_text' => $puntos_text, 'punto_id' => $id, 'platform' => $platform, 'entity' => $entity);
    }
    
    /**
     * @Route("/editpunto/{entity}" ,name="_puntosplatform_edit_punto")
     * @Template()
     */
    public function editPuntoAction($entity)
    {
        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $em = $this->getDoctrine()->getManager();
        $form = $request->get('params')['form'];
        try {
            $punto = $em->getRepository('Lugh\WebAppBundle\Entity\\' . $entity)->find($request->get('punto_id'));
            if (isset($form['es_es']))
            {
                switch ($entity) {
                    case 'PuntoDia':
                        $punto->setText($form['es_es']);
                        break;
                    case 'OpcionesVoto':
                        $punto->setNombre($form['es_es']);
                        break;
                    default:
                        break;
                }
                $es = $form['es_es'];
                unset($form['es_es']);
                $form['es_es'] = $es;
            }
            $em->persist($punto);
            $em->flush();
            $repository = $em->getRepository('Gedmo\\Translatable\\Entity\\Translation');
            foreach ($form as $lang => $text) {
                switch ($entity) {
                    case 'PuntoDia':
                        $repository->translate($punto, 'text', $lang, $text);
                        //$punto->setText($text);
                        break;
                    case 'OpcionesVoto':
                        $repository->translate($punto, 'nombre', $lang, $text);
                        //$punto->setNombre($text);
                        break;
                    default:
                        break;
                }
                
               // $punto->setTranslatableLocale($lang);
                $em->persist($punto);
                $em->flush();
            }
        } catch (\Exception $exc) {
            return new Response(json_encode(array('error'=> $exc->getMessage())));
        }
        return new Response(json_encode(array('success'=> '1')));
    }
    
    /**
     * @Route("/editpuntos" ,name="_puntosplatform_edit_puntos")
     * @Template()
     */
    public function editPuntosAction()
    {
        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $platform_id = $request->get('platform_id');
        $platform = $this->getEm($platform_id);        
        $em = $this->getDoctrine()->getManager();
        
        $puntos = $em->getRepository('Lugh\WebAppBundle\Entity\PuntoDia')->findBy(array(), array('orden' => 'ASC'));
        $puntos_line = array();
        
        foreach ($puntos as $punto) {
            $punto_line = array();
            $punto_line['id'] = $punto->getId();
            $punto_line['numPunto'] = $punto->getNumPunto();
            $punto_line['text'] = $punto->getText();
            $punto_line['parent'] = $punto->getParent() == null ? null : $punto->getParent()->getNumPunto();
            $punto_line['orden'] = $punto->getOrden();
            $punto_line['tipoVoto'] = $punto->getTipoVoto()->getTipo();
            $punto_line['opcionesVoto'] = $punto->getGruposOV()->getName();
            $punto_line['informativo'] = $this->getWidget(
                    'checkbox', 
                    'informativo', 
                    $punto->getInformativo() ? true : false,
                    array('value' => 'activate')
                    );
            $punto_line['retirado'] = $this->getWidget(
                    'checkbox', 
                    'retirado', 
                    $punto->getRetirado() ? true : false,
                    array('value' => 'activate')
                    );
            $punto_line['extra'] = $punto->getExtra();

            $puntos_line[] = $punto_line;
        }
        $form_puntos = array();
        //$form_puntos['id'] = str_replace('-', '_',$punto->getId());
        $form_puntos['numPunto'] = $this->getWidget(
                'text', 
                'numpunto',
                ''
                );

        $langs = $this->get('lugh.langs.db_connection')->from($platform_id);

        foreach ($langs as $lang => $value){

            $form_puntos['text_'.$lang] = $this->getWidget(
                'text',
                'text_'.$lang,
                ''
            );

        }

        $form_puntos['parent'] = $this->getWidget(
                'choice', 
                'parent', 
                '',
                array('choices' => $this->getAllPoints())
                );
        $form_puntos['orden'] = $this->getWidget(
                'text', 
                'orden',
                ''
                );
        $form_puntos['tipoVoto'] = $this->getWidget(
                'choice', 
                'tipovoto', 
                '',
                array('choices' => $this->getAllTipoVotos(),'empty_value' => false)
                );
        $form_puntos['opcionesVoto'] = $this->getWidget(
                'choice', 
                'opcionesvoto', 
                '',
                array('choices' => $this->getAllGrupoOpcionesVoto(),'empty_value' => false)
                );
        $form_puntos['informativo'] = $this->getWidget(
                'checkbox', 
                'informativo', 
                false,
                array('value' => 'activate')
                );
        $form_puntos['retirado'] = $this->getWidget(
                'checkbox', 
                'retirado', 
                false,
                array('value' => 'activate')
                );
        $form_puntos['extra'] = $this->getWidget(
                'choice', 
                'extra', 
                '',
                array('choices' => $this->getOptionsNum())
                );
        
        //die(var_dump($parameters_time));
        $L = $this->get('lugh.langs.db_connection');
        $langs = $L->from($platform_id);

        foreach ($langs as $lang => $value){
            $langs[$lang] = array('code' => $L->langCode($lang), 'name' => $L->langName($lang));
        }


        return array('puntos_line' => $puntos_line, 'form_puntos' => $form_puntos,'platform' => $platform, 'langs' => $langs);
    }
    
    /**
     * @Route("/editopcionesvoto" ,name="_puntosplatform_edit_opcionesvoto")
     * @Template()
     */
    public function editOpcionesVotoAction()
    {
        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $platform_id = $request->get('platform_id');
        $platform = $this->getEm($platform_id);        
        $em = $this->getDoctrine()->getManager();
        
        $opcionesVoto = $em->getRepository('Lugh\WebAppBundle\Entity\OpcionesVoto')->findAll();
        $opcionesVoto_line = array();
        
        foreach ($opcionesVoto as $opcionVoto) {
            $opcionVoto_line = array();
            $opcionVoto_line['id'] = $opcionVoto->getId();
            $opcionVoto_line['nombre'] = $opcionVoto->getNombre();
            $opcionVoto_line['orden'] = $opcionVoto->getOrden();
            $opcionVoto_line['symbol'] = $opcionVoto->getSymbol();
            $opcionesVoto_line[] = $opcionVoto_line;
        }
        $form_opcionesVoto = array();
        //$form_opcionesVoto['id'] = str_replace('-', '_',$opcionVoto->getId());
        $L = $this->get('lugh.langs.db_connection');
        $langs = $L->from($platform_id);

        $elements = array('symbol');
        foreach ($langs as $lang => $value) {
            array_push($elements, 'text_'.$lang);
        }
        array_push($elements, 'orden');
        
        foreach ($elements as $element) {
            $form_opcionesVoto[$element] = $this->getWidget(
                'text', 
                $element,
                ''
                );
        }

        foreach ($langs as $lang => $value){
            $langs[$lang] = array('code' => $L->langCode($lang), 'name' => $L->langName($lang));
        }

        return array('opcionesVoto_line' => $opcionesVoto_line, 'form_opcionesVoto' => $form_opcionesVoto,'platform' => $platform, 'langs' => $langs);
    }
    
    
    /**
     * @Route("/editgrupoopcionesvoto" ,name="_puntosplatform_edit_grupoopcionesvoto")
     * @Template()
     */
    public function editGrupoOpcionesVotoAction()
    {
        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $platform_id = $request->get('platform_id');
        $platform = $this->getEm($platform_id);        
        $em = $this->getDoctrine()->getManager();
        
        $opcionesVoto = $em->getRepository('Lugh\WebAppBundle\Entity\GrupoOpcionesVoto')->findAll();
        $opcionesVoto_line = array();
        
        foreach ($opcionesVoto as $opcionVoto) {
            $opcionVoto_line = array();
            $opcionVoto_line['id'] = $opcionVoto->getId();
            $opcionVoto_line['name'] = $opcionVoto->getName();
            $opcionVoto_line['opcionesvoto'] = $this->concatOpcionesVoto($opcionVoto->getOpcionesVoto());
            $opcionesVoto_line[] = $opcionVoto_line;
        }
        $form_opcionesVoto = array();
        //$form_opcionesVoto['id'] = str_replace('-', '_',$opcionVoto->getId());
        $form_opcionesVoto['name'] = $this->getWidget(
                'text', 
                'name',
                ''
                );
        $form_opcionesVoto['opcionesvoto'] = $this->getWidget(
                'choice', 
                'opcionesvoto',
                array(),
                array('choices' => $this->getAllOpcionesVoto(),'multiple'  => true)
                );

        //die(var_dump($parameters_time));
        return array('opcionesVoto_line' => $opcionesVoto_line, 'form_opcionesVoto' => $form_opcionesVoto,'platform' => $platform);
    }
    
    
    /**
     * @Route("/edittipovoto" ,name="_puntosplatform_edit_tipovoto")
     * @Template()
     */
    public function editTipoVotoAction()
    {
        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $platform_id = $request->get('platform_id');
        $platform = $this->getEm($platform_id);        
        $em = $this->getDoctrine()->getManager();

        $tiposVoto = $em->getRepository('Lugh\WebAppBundle\Entity\TipoVoto')->findAll();
        $tiposVoto_line = array();
        
        foreach ($tiposVoto as $tipoVoto) {
            $tipoVoto_line = array();
            $tipoVoto_line['id'] = $tipoVoto->getId();
            $tipoVoto_line['tipo'] = $tipoVoto->getTipo();
            $tipoVoto_line['name'] = $tipoVoto->getName();
            $tipoVoto_line['tag']  = $tipoVoto->getTag();
            $tipoVoto_line['maxvotos'] = $tipoVoto->getMaxVotos();
            $tipoVoto_line['minvotos'] = $tipoVoto->getMinVotos();
            $tipoVoto_line['claseDecrypt'] = $tipoVoto->getClaseDecrypt();
            $tipoVoto_line['isserie'] = $this->getWidget(
                    'checkbox', 
                    'isserie', 
                    $tipoVoto->getIsSerie() ? true : false,
                    array('value' => 'activate')
                    );
            
            $tiposVoto_line[] = $tipoVoto_line;
        }
        $form_tiposVoto = array();
        //$form_tiposVoto['id'] = str_replace('-', '_',$tipoVoto->getId());
        $elements = array(
            'tipo',
            'name',
            'tag',
            'maxvotos',
            'minvotos',
            'claseDecrypt'
        );
        
        foreach ($elements as $element) {
            $form_tiposVoto[$element] = $this->getWidget(
                'text', 
                $element,
                ''
                );
        }
        $form_tiposVoto['isserie'] = $this->getWidget(
                'checkbox', 
                'isserie', 
                false,
                array('value' => 'activate')
                );
        return array('tiposVoto_line' => $tiposVoto_line, 'form_tiposVoto' => $form_tiposVoto,'platform' => $platform);
    }
    
    /**
     * @Route("/puntos" ,name="_puntosplatform_puntos")
     * @Template()
     */
    public function contentPuntosAction()
    {
        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $platform_id = $request->get('platform_id');
        return array('platform_id' => $platform_id);
    }
    
    /**
     * @Route("/puntostablecontent" ,name="_puntosplatform_puntos_table_content")
     * @Template()
     */
    public function puntosTableContentAction()
    {
        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $platform_id = $request->get('platform_id');
        $platform = $this->getEm($platform_id);
        $em = $this->getDoctrine()->getManager();
        
        $puntos = $em->getRepository('Lugh\WebAppBundle\Entity\PuntoDia')->findBy(array(), array('orden' => 'ASC'));

        $puntos_lines = array();
        
        foreach ($puntos as $punto) {
            $punto_line = array();
            $punto_line['ide'] = $punto->getId();
            $punto_line['id'] = str_replace('-', '_',$punto->getId());
            $punto_line['numPunto'] = $this->getWidget(
                    'text', 
                    'numpunto_' . $punto_line['id'], 
                    $punto->getNumPunto()
                    );
            $punto_line['text'] = $this->getWidget(
                    'text', 
                    'text_' . $punto_line['id'], 
                    $punto->getText()
                    );
            $punto_line['parent'] = $this->getWidget(
                    'choice', 
                    'parent_' . $punto_line['id'], 
                    $punto->getParent() == null ? null : $punto->getParent()->getId(),
                    array('choices' => $this->getAllPoints($punto))
                    );
            $punto_line['orden'] = $this->getWidget(
                    'text', 
                    'orden_' . $punto_line['id'], 
                    $punto->getOrden()
                    );
            $punto_line['tipoVoto'] = $this->getWidget(
                    'choice', 
                    'tipovoto_' . $punto_line['id'], 
                    $punto->getTipoVoto()->getId(),
                    array('choices' => $this->getAllTipoVotos(),'empty_value' => false)
                    );
            $punto_line['opcionesVoto'] = $this->getWidget(
                    'choice', 
                    'opcionesvoto_' . $punto_line['id'], 
                    $punto->getGruposOV()->getId(),
                    array('choices' => $this->getAllGrupoOpcionesVoto(),'empty_value' => false)
                    );
            $punto_line['informativo'] = $this->getWidget(
                    'checkbox', 
                    'informativo_' . $punto_line['id'], 
                    $punto->getInformativo() ? true : false,
                    array('value' => 'activate')
                    );
            $punto_line['retirado'] = $this->getWidget(
                    'checkbox', 
                    'retirado_' . $punto_line['id'], 
                    $punto->getRetirado() ? true : false,
                    array('value' => 'activate')
                    );
            $punto_line['extra'] = $this->getWidget(
                    'choice', 
                    'extra_' . $punto_line['id'], 
                    $punto->getExtra(),
                    array('choices' => $this->getOptionsNum())
                    );

            $puntos_lines[] = $punto_line;
        }
        
        return array('puntos_lines' => $puntos_lines, 'platform' => $platform);
    }
    
    /**
     * @Route("/opcionesvototablecontent" ,name="_puntosplatform_opcionesvoto_table_content")
     * @Template()
     */
    public function opcionesVotoTableContentAction()
    {
        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $platform_id = $request->get('platform_id');
        $platform = $this->getEm($platform_id);
        $em = $this->getDoctrine()->getManager();
        
        $opcionesVoto = $em->getRepository('Lugh\WebAppBundle\Entity\OpcionesVoto')->findAll();

        $opcionesvoto_lines = array();
        
        foreach ($opcionesVoto as $opcionVoto) {
            $opcionvoto_line = array();
            $opcionvoto_line['ide'] = $opcionVoto->getId();
            $opcionvoto_line['id'] = str_replace('-', '_',$opcionVoto->getId());
            $opcionvoto_line['nombre'] = $this->getWidget(
                    'text', 
                    'nombre_' . $opcionvoto_line['id'], 
                    $opcionVoto->getNombre()
                    );
            $opcionvoto_line['symbol'] = $this->getWidget(
                    'text', 
                    'symbol_' . $opcionvoto_line['id'], 
                    $opcionVoto->getSymbol()
                    );
            $opcionvoto_line['orden'] = $this->getWidget(
                    'text', 
                    'orden_' . $opcionvoto_line['id'], 
                    $opcionVoto->getOrden()
                    );

            $opcionesvoto_lines[] = $opcionvoto_line;
        }
        return array('opcionesvoto_lines' => $opcionesvoto_lines, 'platform' => $platform);
    }
    
    /**
     * @Route("/grupoopcionesvototablecontent" ,name="_puntosplatform_grupoopcionesvoto_table_content")
     * @Template()
     */
    public function grupoOpcionesVotoTableContentAction()
    {
        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $platform_id = $request->get('platform_id');
        $platform = $this->getEm($platform_id);
        $em = $this->getDoctrine()->getManager();
        
        $opcionesVoto = $em->getRepository('Lugh\WebAppBundle\Entity\GrupoOpcionesVoto')->findAll();

        $opcionesvoto_lines = array();
        
        foreach ($opcionesVoto as $opcionVoto) {
            $opcionvoto_line = array();
            $opcionvoto_line['ide'] = $opcionVoto->getId();
            $opcionvoto_line['id'] = str_replace('-', '_',$opcionVoto->getId());
            $opcionvoto_line['name'] = $this->getWidget(
                    'text', 
                    'name_' . $opcionvoto_line['id'], 
                    $opcionVoto->getName()
                    );
            $opcionvoto_line['opcionesvoto'] = $this->getWidget(
                'choice', 
                'opcionesvoto_' . $opcionvoto_line['id'],
                $opcionVoto->getOpcionesVoto()->map(function($element){return $element->getId();})->toArray(),
                array('choices' => $this->getAllOpcionesVoto(),'multiple'  => true)
                );

            /*
            $opcionvoto_line['opcionesvoto'] = $this->getWidget(
                    'textarea', 
                    'opcionesvoto_' . $opcionvoto_line['id'], 
                    $this->concatOpcionesVoto($opcionVoto->getOpcionesVoto())
                    );
            */
            $opcionesvoto_lines[] = $opcionvoto_line;
        }
        return array('opcionesvoto_lines' => $opcionesvoto_lines, 'platform' => $platform);
    }
    
    /**
     * @Route("/tipovototablecontent" ,name="_puntosplatform_tipovoto_table_content")
     * @Template()
     */
    public function tipoVotoTableContentAction()
    {
        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $platform_id = $request->get('platform_id');
        $platform = $this->getEm($platform_id);
        $em = $this->getDoctrine()->getManager();

        $tiposVoto = $em->getRepository('Lugh\WebAppBundle\Entity\TipoVoto')->findAll();

        $tipovoto_lines = array();
        
        foreach ($tiposVoto as $tipoVoto) {
            $tipovoto_line = array();
            $tipovoto_line['ide'] = $tipoVoto->getId();
            $tipovoto_line['id'] = str_replace('-', '_',$tipoVoto->getId());
            $tipovoto_line['tipo'] = $this->getWidget(
                    'text', 
                    'tipo_' . $tipovoto_line['id'], 
                    $tipoVoto->getTipo()
                    );
            $tipovoto_line['maxvotos'] = $this->getWidget(
                    'text', 
                    'maxvotos_' . $tipovoto_line['id'], 
                    $tipoVoto->getMaxVotos()
                    );
            $tipovoto_line['name'] = $this->getWidget(
                    'text', 
                    'name_' . $tipovoto_line['id'], 
                    $tipoVoto->getName()
                    );
            $tipovoto_line['tag'] = $this->getWidget(
                    'text', 
                    'tag_' . $tipovoto_line['id'], 
                    $tipoVoto->getTag()
                    );
            $tipovoto_line['minvotos'] = $this->getWidget(
                    'text', 
                    'minvotos_' . $tipovoto_line['id'], 
                    $tipoVoto->getMinVotos()
                    );
            $tipovoto_line['isserie'] = $this->getWidget(
                    'checkbox', 
                    'isserie_' . $tipovoto_line['id'], 
                    $tipoVoto->getIsSerie() ? true : false,
                    array('value' => 'activate')
                    );
            $tipovoto_line['clasedecrypt'] = $this->getWidget(
                    'text', 
                    'clasedecrypt_' . $tipovoto_line['id'], 
                    $tipoVoto->getClaseDecrypt()
                    );

            $tipovoto_lines[] = $tipovoto_line;
        }
        return array('tipovoto_lines' => $tipovoto_lines, 'platform' => $platform);
    }
    
    /**
     * @Route("/removepunto/{table}" ,name="_puntosplatform_remove_punto")
     * @Template()
     */
    public function removePuntoAction($table)
    {
        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $em = $this->getDoctrine()->getManager();
        try {
            $punto = $em->getRepository('Lugh\WebAppBundle\Entity\\' . $table)->find($request->get('id'));
            $em->remove($punto);
            $em->flush();
        } catch (\Exception $exc) {
            return new Response(json_encode(array('error'=> $exc->getMessage())));
        }
        return new Response(json_encode(array('success'=> '1')));
    }
    
    /**
     * @Route("/addpunto/{platform_id}" ,name="_puntosplatform_add_punto")
     * @Template()
     */
    public function addPuntosAction($platform_id)
    {
        $this->getEm($platform_id);        
        $em = $this->getDoctrine()->getManager();
        
        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $params = $request->get('params');
        $type = $request->get('type');      
        $form = $params['form'];
        try {

            switch ($type) {
                case 'punto':
                    $required = array(
                        'numpunto',
                        'orden',
                        'tipovoto',
                        'opcionesvoto'
                    );
                    foreach ($required as $value) {
                         if (!isset($form[$value]) || $form[$value]=='')
                        {
                            throw new Exception($value . " Blank");
                        }
                    }
                    /*if ($em->getRepository('Lugh\WebAppBundle\Entity\PuntoDia')->findOneBy(array('numPunto' => $form['numpunto'])) != null)
                    {
                        throw new Exception("Duplicate Point");
                    }*/
                   
                    $punto = new PuntoDia();
                    $punto->setNumPunto($form['numpunto']);
                    if (isset($form['parent']))
                    {
                        $parent = $em->getRepository('Lugh\WebAppBundle\Entity\PuntoDia')->find($form['parent']);
                        $punto->setParent($parent);
                    }
                    $punto->setOrden($form['orden']);
                    $punto->setTipoVoto($em->getRepository('Lugh\WebAppBundle\Entity\TipoVoto')->find($form['tipovoto']));
                    $punto->setGruposOV($em->getRepository('Lugh\WebAppBundle\Entity\GrupoOpcionesVoto')->find($form['opcionesvoto']));
                    if(isset($form['informativo'])){
                        $punto->setInformativo(true);
                    }
                    else{
                        $punto->setInformativo(false);
                    }
                    if(isset($form['retirado'])){
                        $punto->setRetirado(true);
                    }
                    else{
                        $punto->setRetirado(false);
                    }
                    $punto->setExtra(isset($form['extra']) ? $form['extra'] : 0);
                    $punto->setVoteProxy(isset($form['voteproxy']) ? $form['voteproxy'] : 0);
                    $punto->setText($form['text_es']);
                    $repository = $em->getRepository('Gedmo\\Translatable\\Entity\\Translation');

                    $L = $this->get('lugh.langs.db_connection');
                    $langs = $L->from($platform_id);

                    foreach ($langs as $lang => $value) {
                        $repository->translate($punto, 'text', $L->langCode($lang), $form['text_'.$lang]);
                    }

                    $em->persist($punto); 
                    $em->flush();
                    break;
                case 'opcionvoto':
                    $required = array(
                        'symbol',
                        'orden'
                    );
                    foreach ($required as $value) {
                         if (!isset($form[$value]) || $form[$value]=='')
                        {
                            throw new Exception($value . " Blank");
                        }
                    }
                    if ($em->getRepository('Lugh\WebAppBundle\Entity\OpcionesVoto')->findOneBy(array('symbol' => $form['symbol'])) != null)
                    {
                        throw new Exception("Duplicate Opcion Voto");
                    }
                    
                    $opcionVoto = new OpcionesVoto();
                    $opcionVoto->setSymbol($form['symbol']);
                    $opcionVoto->setNombre($form['text_es']);
                    $opcionVoto->setOrden($form['orden']);
                    $repository = $em->getRepository('Gedmo\\Translatable\\Entity\\Translation');

                    $L = $this->get('lugh.langs.db_connection');
                    $langs = $L->from($platform_id);

                    foreach ($langs as $lang => $value) {
                        $repository->translate($opcionVoto, 'nombre', $L->langCode($lang), $form['text_'.$lang]);
                    }

                    $em->persist($opcionVoto); 
                    $em->flush();
                   
                    break;
                case 'grupoopcionvoto':
                    $required = array(
                        'opcionesvoto'
                    );
                    foreach ($required as $value) {
                         if (!isset($form[$value]) || $form[$value]=='')
                        {
                            throw new Exception($value . " Blank");
                        }
                    }
                   
                    $grupoOpcionVoto = new GrupoOpcionesVoto();
                    $grupoOpcionVoto->setName($form['name']);
                    foreach ($form['opcionesvoto'] as $opcion) {
                        $opcionVoto = $em->getRepository('Lugh\WebAppBundle\Entity\OpcionesVoto')->find($opcion);
                        $grupoOpcionVoto->addOpcionesVoto($opcionVoto);
                    }
                    $em->persist($grupoOpcionVoto); 
                    $em->flush();
                   
                    break;
                case 'tipovoto':
                    $required = array(
                        'tipo',
                        'name'
                    );
                    foreach ($required as $value) {
                         if (!isset($form[$value]) || $form[$value]=='')
                        {
                            throw new Exception($value . " Blank");
                        }
                    }
                   
                    $tipoVoto = new TipoVoto();
                    $tipoVoto->setTipo($form['tipo']);
                    $tipoVoto->setName($form['name']);
                    $tipoVoto->setTag($form['tag']);
                    $tipoVoto->setMaxVotos($form['maxvotos'] != '' ? intval($form['maxvotos']) : 999999);
                    $tipoVoto->setMinVotos($form['minvotos'] != '' ? intval($form['minvotos']) : 1);
                    $tipoVoto->setClaseDecrypt($form['claseDecrypt']);
                    $tipoVoto->setIsSerie(isset($form['isserie']) ? $form['isserie'] : false);
                    $em->persist($tipoVoto); 
                    $em->flush();
                   
                    break;
                default:
                    break;
            }
            
        } catch (Exception $exc) {
            return new Response(json_encode(array('error'=> $exc->getMessage())));
        }
        return new Response(json_encode(array('success'=> '1')));
    }
    
    /**
     * @Route("/savepuntos/{platform_id}" ,name="_puntosplatform_save_puntos")
     * @Template()
     */
    public function savePuntosAction($platform_id)
    {
        $this->getEm($platform_id);        
        $em = $this->getDoctrine()->getManager();
        
        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $params = $request->get('params');
       
        $form = $params['form'];
        $form_id = $params['id'];
        try {

            foreach ($form_id as $key => $ids) {
                switch ($key) {
                    case 'punto':
                        foreach ($ids as $id) {
                            $punto = $em->getRepository('Lugh\WebAppBundle\Entity\PuntoDia')->find(str_replace('_', '-',$id));
                            $tipoVoto = $em->getRepository('Lugh\WebAppBundle\Entity\TipoVoto')->find($form['tipovoto_' . $id]);
                            
                            if (!isset($form['numpunto_' . $id]) || $form['numpunto_' . $id]=='')
                            {
                                throw new Exception("NumPunto Blank");
                            }
                            if ($tipoVoto != $punto->getTipoVoto() && $form['parent_' . $id] != '')
                            {
                                throw new Exception("TipoVoto with parent");
                            }
                            $punto->setNumPunto($form['numpunto_' . $id]);
                            if ($form['parent_' . $id] != '') {
                                $punto->setParent($em->getRepository('Lugh\WebAppBundle\Entity\PuntoDia')->find($form['parent_' . $id]));
                            }
                            else
                            {
                                $punto->setParent(null);
                            }
                            $punto->setOrden($form['orden_' . $id]);
                            $punto->setTipoVoto($tipoVoto);
                            $punto->setGruposOV($em->getRepository('Lugh\WebAppBundle\Entity\GrupoOpcionesVoto')->find($form['opcionesvoto_' . $id]));
                            $punto->setExtra($form['extra_' . $id]); 
                            $punto->setInformativo(isset($form['informativo_' . $id]) ? true : false);
                            $punto->setRetirado(isset($form['retirado_' . $id]) ? true : false);
                            $em->persist($punto); 
                        }

                        break;
                    case 'opcionvoto':
                        foreach ($ids as $id) {
                            $opcionvoto = $em->getRepository('Lugh\WebAppBundle\Entity\OpcionesVoto')->find(str_replace('_', '-',$id));
                            $opcionvoto->setSymbol($form['symbol_' . $id]);
                            $opcionvoto->setOrden($form['orden_' . $id]);
                            $em->persist($opcionvoto); 
                        }

                        break;
                    case 'grupoopcionvoto':
                        foreach ($ids as $id) {
                            $grupoopcionvoto = $em->getRepository('Lugh\WebAppBundle\Entity\GrupoOpcionesVoto')->find(str_replace('_', '-',$id));
                            $grupoopcionvoto->setName($form['name_' . $id]);
                            $grupoopcionvoto->resetOpcionesVoto();
                            foreach ($form['opcionesvoto_' . $id] as $opcionVotoid) {
                                $opcionvoto = $em->getRepository('Lugh\WebAppBundle\Entity\OpcionesVoto')->find($opcionVotoid);
                                $grupoopcionvoto->addOpcionesVoto($opcionvoto);
                            }
                            $em->persist($grupoopcionvoto); 
                        }

                        break;
                    case 'tipovoto':
                        foreach ($ids as $id) {
                            $tipoVoto = $em->getRepository('Lugh\WebAppBundle\Entity\TipoVoto')->find(str_replace('_', '-',$id));
                            $tipoVoto->setTipo($form['tipo_' . $id]);
                            $tipoVoto->setName($form['name_' . $id]);
                            $tipoVoto->setClaseDecrypt($form['clasedecrypt_' . $id]);
                            $tipoVoto->setTag($form['tag_' . $id]);
                            $tipoVoto->setMaxVotos(intval($form['maxvotos_' . $id]));
                            $tipoVoto->setMinVotos(intval($form['minvotos_' . $id]));
                            $tipoVoto->setIsSerie(isset($form['isserie_' . $id]) ? true : false);
                            $em->persist($tipoVoto); 
                        }

                        break;
                    default:
                        break;
                }
            }

            $em->flush();
        } catch (\Exception $exc) {
            return new Response(json_encode(array('error'=> $exc->getMessage())));
        }
        return new Response(json_encode(array('success'=> '1')));
    }
    
    /**
     * @Route("/savepuntos/table/{platform_id}" ,name="_puntosplatform_save_puntos_table")
     * @Template()
     */
    public function savePuntosTableAction($platform_id)
    {
        $this->getEm($platform_id);        
        $em = $this->getDoctrine()->getManager();
        
        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $params = $request->get('params');
       
        $form = $params['form'];
        $form_id = $params['id'];
        try {

            foreach ($form_id as $key => $ids) {
                switch ($key) {
                    case 'punto':
                        foreach ($ids as $id) {
                            $punto = $em->getRepository('Lugh\WebAppBundle\Entity\PuntoDia')->find(str_replace('_', '-',$id));
                            $tipoVoto = $em->getRepository('Lugh\WebAppBundle\Entity\TipoVoto')->find($form['tipovoto_' . $id]);
                            
                            if (!isset($form['numpunto_' . $id]) || $form['numpunto_' . $id]=='')
                            {
                                throw new Exception("NumPunto Blank");
                            }
                            if ($tipoVoto != $punto->getTipoVoto() && $form['parent_' . $id] != '')
                            {
                                throw new Exception("TipoVoto with parent");
                            }
                            $punto->setNumPunto($form['numpunto_' . $id]);
                            if ($form['parent_' . $id] != '') {
                                $punto->setParent($em->getRepository('Lugh\WebAppBundle\Entity\PuntoDia')->find($form['parent_' . $id]));
                            }
                            else
                            {
                                $punto->setParent(null);
                            }
                            $punto->setOrden($form['orden_' . $id]);
                            $punto->setTipoVoto($tipoVoto);
                            $punto->setGruposOV($em->getRepository('Lugh\WebAppBundle\Entity\GrupoOpcionesVoto')->find($form['opcionesvoto_' . $id]));
                            $punto->setExtra($form['extra_' . $id]); 
                            $punto->setInformativo(isset($form['informativo_' . $id]) ? true : false);
                            $punto->setRetirado(isset($form['retirado_' . $id]) ? false : true);
                            $em->persist($punto); 
                        }

                        break;
                    default:
                        break;
                }
            }

            $em->flush();
        } catch (\Exception $exc) {
            return new Response(json_encode(array('error'=> $exc->getMessage())));
        }
        return new Response(json_encode(array('success'=> '1')));
    }
    
    private function getEm($platform_id)
    {
        $em = $this->getDoctrine()->getManager('db_connection');
        $platform =  $em->getRepository('Lugh\DbConnectionBundle\Entity\Auth')->find($platform_id);
        PlatformsManager::switchDb($platform->getDbname());
        
        return $platform;
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
    
    private function getOptionsNum() {
        $nums = array();
        for ($index = 0; $index < 10; $index++) {
            $nums[$index] = $index;
        }
        return $nums;
    }
    
    private function getAllPoints($point = null) {
        $em = $this->getDoctrine()->getManager();
        $puntos = $point != null ? 
                $em->getRepository('Lugh\WebAppBundle\Entity\PuntoDia')->findByNot(array('numPunto' => $point->getNumPunto())) :
                $em->getRepository('Lugh\WebAppBundle\Entity\PuntoDia')->findAll();
        
        $puntosArray = array();
        if ($point != null) {
            foreach ($puntos as $punto) {
                if (!$point->getSubpuntos()->contains($punto) && $point->getTipoVoto() == $punto->getTipoVoto())
                {
                    $puntosArray[$punto->getId()] = $punto->getNumPunto();
                }
            }
        }
        else {
            foreach ($puntos as $punto) {
                $puntosArray[$punto->getId()] = $punto->getNumPunto();
            }
        }
        
        return $puntosArray;
    }
    
    private function getAllTipoVotos() {
        $em = $this->getDoctrine()->getManager();
        $tipoVotos = $em->getRepository('Lugh\WebAppBundle\Entity\TipoVoto')->findAll();
        $tipoVotosArray = array();
        foreach ($tipoVotos as $tipoVoto) {
            $tipoVotosArray[$tipoVoto->getId()] = $tipoVoto->getName();
        }
        return $tipoVotosArray;
    }
    
    private function getAllGrupoOpcionesVoto() {
        $em = $this->getDoctrine()->getManager();
        $tipoVotos = $em->getRepository('Lugh\WebAppBundle\Entity\GrupoOpcionesVoto')->findAll();
        $tipoVotosArray = array();
        foreach ($tipoVotos as $tipoVoto) {
            $tipoVotosArray[$tipoVoto->getId()] = $tipoVoto->getName();
        }
        return $tipoVotosArray;
    }
    
    private function getAllOpcionesVoto() {
        $em = $this->getDoctrine()->getManager();
        $tipoVotos = $em->getRepository('Lugh\WebAppBundle\Entity\OpcionesVoto')->findAll();
        $tipoVotosArray = array();
        foreach ($tipoVotos as $tipoVoto) {
            $tipoVotosArray[$tipoVoto->getId()] = $tipoVoto->getNombre();
        }
        return $tipoVotosArray;
    }
    private function concatOpcionesVoto($opcionesVoto)
    {
        $text = '';
        foreach ($opcionesVoto as $opcionVoto) {
            $text .= $opcionVoto->getNombre() .', ';
        }
        $text = substr($text, 0, strlen($text)-2);
        return $text;
    }
}
