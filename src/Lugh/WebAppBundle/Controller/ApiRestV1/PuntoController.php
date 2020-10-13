<?php

namespace Lugh\WebAppBundle\Controller\ApiRestV1;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Config\Definition\Exception\Exception;
use Lugh\WebAppBundle\Annotations\Permissions;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use JMS\Serializer\SerializationContext;

 /**
 * @RouteResource("Punto")
 */
class PuntoController extends Controller {
    
    
    public function cgetAction()
    {
        $request = $this->get('request');
        //die(var_dump($request->getLocale()));
        $storage = $this->get('lugh.server')->getStorage();
        $serializer = $this->container->get('jms_serializer');
        try {
            $puntos = $storage->getPuntos();
            $items = array('puntos' => $puntos);
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize($items, 'json', SerializationContext::create()->setGroups(array('Default', 'tipoVoto', 'opcionesVoto'))));
    }// "get_puntos"     [GET] /puntos
    
    /**
     * @Permissions(perm={"ROLE_CUSTOMER"})
     */
    public function cgetAdminAction()
    {
        $storage = $this->get('lugh.server')->getStorage();
        $serializer = $this->container->get('jms_serializer');
        try {
            $puntos = $storage->getAdminPuntos();
            $items = array('puntos' => $puntos);
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize($items, 'json', SerializationContext::create()->setGroups(array('Default'))));
    }// "get_puntos"     [GET] /puntos/admin
    
    public function cgetTipoAction($id)
    {
        $storage = $this->get('lugh.server')->getStorage();
        $serializer = $this->container->get('jms_serializer');
        try {
            $puntos = $storage->getTipoPuntos($id);
            $items = array('puntos' => $puntos);
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize($items, 'json', SerializationContext::create()->setGroups(array('Default', 'tipoVoto', 'opcionesVoto'))));
    }// "get_puntos"     [GET] /puntos/{id}/tipo
    
    public function getAction($id) // GET Resource
    {
        $storage = $this->get('lugh.server')->getStorage();
        $serializer = $this->container->get('jms_serializer');
        try {
            $punto = $storage->getPunto($id);
            $items = array('puntos' => $punto);
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize($items, 'json', SerializationContext::create()->setGroups(array('Default'))));

    }// "get_resource"      [GET] /puntos/{id}
    
    /**
     * @Permissions(perm={"ROLE_CUSTOMER"})
     */
    public function postAction() // Create Resource
    {
        $serializer = $this->container->get('jms_serializer');
        $storage = $this->get('lugh.server')->getStorage();
        $builder = $this->get('lugh.server')->getBuilder();
        $request = $this->get('request');
        try {       
           $punto = $builder->buildPunto();
           $punto->setNumPunto($request->get('numPunto',''));
           $punto->setOrden($request->get('orden',0));
           $punto->setText($request->get('text',''));
           $punto->setRetirado($request->get('retirado',false));
           if ($request->get('parent_id', false))
           {
               $parent = $storage->getPunto($request->get('parent_id'));
               $punto->setParent($parent);
           }
           $tipoVoto = $storage->getTipoVotobyTipoSerie($request->get('tipo',0), $request->get('is_serie',0));
           if ($tipoVoto == null)
           {
               $tipoVoto = $builder->buildTipoVoto();
               $tipoVoto->setTipo($request->get('tipo',0));
               $tipoVoto->setIsSerie($request->get('is_serie',0));
               $tipoVoto->setMaxVotos($request->get('maxvotos',99999999));
               $tipoVoto->setMinVotos($request->get('is_serie',0));
           }
           $punto->setTipoVoto($tipoVoto);
           $grupoOpcionesVoto = $storage->getGrupoOpcionesVoto($request->get('grupo_ov'));
           $punto->setGruposOV($grupoOpcionesVoto);
           $storage->save($punto);
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize(array('success' => $punto), 'json', SerializationContext::create()->setGroups(array('Default'))));
    }// "new_puntos"     [POST] /puntos
    
    /**
     * @Permissions(perm={"ROLE_CUSTOMER"})
     */
    public function putAction($id) // Update Resource
    { 
        $serializer = $this->container->get('jms_serializer');
        $storage = $this->get('lugh.server')->getStorage();
        $request = $this->get('request');
        try {       
           $punto = $storage->getPunto($id);
           $punto->setNumPunto($request->get('numPunto',$punto->getNumPunto()));
           $punto->setOrden($request->get('orden',$punto->getOrden()));
           $punto->setText($request->get('text',$punto->getText()));
           $punto->setRetirado($request->get('retirado',$punto->getRetirado()));
           if ($request->get('parent_id', false))
           {
               $parent = $storage->getPunto($request->get('parent_id'));
               $punto->setParent($parent);
           }
           $storage->save($punto);
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize(array('success' => $punto), 'json', SerializationContext::create()->setGroups(array('Default'))));
    }// "put_resource"      [PUT] /puntos/{id}
    
    public function deleteAction($id) // DELETE Resource
    {
        $serializer = $this->container->get('jms_serializer');
        return new Response($serializer->serialize(array('error' => 'Not implemented'), 'json'));
    } // "delete_resource"      [DELETE] /resource/{id} 
    
    
}

