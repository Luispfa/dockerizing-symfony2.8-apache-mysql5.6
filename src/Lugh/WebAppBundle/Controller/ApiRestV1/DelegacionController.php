<?php

namespace Lugh\WebAppBundle\Controller\ApiRestV1;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Config\Definition\Exception\Exception;
use Lugh\WebAppBundle\Annotations\Permissions;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use JMS\Serializer\SerializationContext;

 /**
 * @RouteResource("Delegacion")
 */
class DelegacionController extends Controller {
    
    /**
     * @Permissions(perm={"ROLE_CUSTOMER"})
     */
    public function cgetAction()
    { 
        $storage = $this->get('lugh.server')->getStorage();
        $serializer = $this->container->get('jms_serializer');
        $request = $this->get('request');
        $groups = array('Default', 'Votacion', 'Personal');
        $groups[] = $request->get('decrypt', false) ? 'VotoSerieDecrypt' : 'VotacionSerie';
        try {
            $delegaciones = $storage->getLastDelegaciones();
            $items = array('delegaciones' => $delegaciones);
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize($items, 'json', SerializationContext::create()->setGroups($groups)));
    }// "get_votos"     [GET] /delegacions
    
    public function getAction($id) // GET Resource
    {
        $storage = $this->get('lugh.server')->getStorage();
        $serializer = $this->container->get('jms_serializer');
        $request = $this->get('request');
        $groups = array('Default', 'Votacion');
        $groups[] = $request->get('decrypt', false) ? 'VotoSerieDecrypt' : 'VotacionSerie';
        try {
            $delegacion = $storage->getDelegacion($id);
            $items = array('delegaciones' => $delegacion);
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize($items, 'json', SerializationContext::create()->setGroups($groups)));

    }// "get_resource"      [GET] /delegacions/{id}
    
    public function postAction() // Create Resource
    {
        $serializer = $this->container->get('jms_serializer');
        $storage = $this->get('lugh.server')->getStorage();
        $builder = $this->get('lugh.server')->getBuilder();
        $request = $this->get('request');
        $user = $this->getUser();
        if ($request->get('delegado_id', false) == false)
        {
            return new Response($serializer->serialize(array('error' => 'Delegado id is null'), 'json'));
        }
        $votos = $request->get('votacion', array());
        try {       
           $accionista = $user->getAccionista();
           $delegacion = $builder->buildDelegacion();
           $delegacion->setDateTime(new \DateTime());
           $delegacion->setAccionista($accionista);
           $delegado = $storage->getDelegado($request->get('delegado_id'));
           if(count($request->get('abs_adicional',array())) > 0){

             //$opcionvoto = $storage->getOpcionesVoto($request->get('abs_adicional'));
             //$delegacion->setAbsAdicional($opcionvoto);
            foreach ($request->get('abs_adicional') as $absAdicionalTipo) {
                $votoAbsAdicional   = $builder->buildVotoAbsAdicional();
                $absAdicional       = $storage->getAbsAdicional($absAdicionalTipo['absAdicional_id']);
                $opcionvoto         = $storage->getOpcionesVoto($absAdicionalTipo['opcionVoto_id']);

                $votoAbsAdicional->setAbsAdicional($absAdicional);
                $votoAbsAdicional->setOpcionVoto($opcionvoto);
                $delegacion->addVotoAbsAdicional($votoAbsAdicional);

            }
           }
           $delegacion->setSharesNum(intval($request->get('sharesnum',0)));
           $delegacion->setObservaciones($request->get('observaciones',''));
           //$delegacion->setSustitucion($request->get('sustitucion',false));
           if($request->get('sustitucion') == null || $request->get('sustitucion') == "true"){
               $delegacion->setSustitucion(1);
           }
           elseif ($request->get('sustitucion') == "false"){
               $delegacion->setSustitucion(0);
           }
           $delegacion->setDelegado($delegado);
           $delegacion->addVotos($votos);
           $storage->save($delegacion);
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize(array('success' => $delegacion), 'json', SerializationContext::create()->setGroups(array('Default', 'Votacion', 'VotacionSerie', 'tipoVoto', 'opcionesVoto'))));       
    }// "new_opcionesvotos"     [POST] /delegacions
    
    public function putAction($id) // Update Resource
    { 
        $serializer = $this->container->get('jms_serializer');
        return new Response($serializer->serialize(array('error' => 'Not implemented'), 'json'));
    }// "put_resource"      [PUT] /votos/{id}
    
    public function deleteAction($id) // DELETE Resource
    {
        $serializer = $this->container->get('jms_serializer');
        return new Response($serializer->serialize(array('error' => 'Not implemented'), 'json'));
    } // "delete_resource"      [DELETE] /resource/{id} 
    
    public function getCommentsAction($slug, $id)
    {
        $serializer = $this->container->get('jms_serializer');
        return new Response($serializer->serialize(array('error' => 'Not implemented'), 'json'));
    } // "get_resource_comments"     [GET] /opcionesvotos/{slug}/comments/{id}
    
}

