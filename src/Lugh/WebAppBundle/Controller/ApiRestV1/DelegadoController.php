<?php

namespace Lugh\WebAppBundle\Controller\ApiRestV1;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Config\Definition\Exception\Exception;
use Lugh\WebAppBundle\Annotations\Permissions;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use JMS\Serializer\SerializationContext;

 /**
 * @RouteResource("Delegado")
 */
class DelegadoController extends Controller {
    

    public function cgetAction()
    { 
        $storage = $this->get('lugh.server')->getStorage();
        $serializer = $this->container->get('jms_serializer');
        try {
            $delegados = $storage->getDelegados();
            $items = array('delegados' => $delegados);
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize($items, 'json', SerializationContext::create()->setGroups(array('Default', 'Votacion', 'VotacionSerie'))));
    }// "get_votos"     [GET] /delegados
    
    public function cgetConsellersAction()
    { 
        $storage = $this->get('lugh.server')->getStorage();
        $serializer = $this->container->get('jms_serializer');
        try {
            $delegados = $storage->getDirectors();
            $items = array('delegados' => $delegados);
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize($items, 'json', SerializationContext::create()->setGroups(array('Default','conseller'))));
    }// "get_votos"     [GET] /delegados/consellers
    
    public function cgetDirectorAction()
    { 
        $storage = $this->get('lugh.server')->getStorage();
        $builder = $this->get('lugh.server')->getBuilder();
        $serializer = $this->container->get('jms_serializer');
        try {
            $delegados = $storage->getDirectors();
            $director = null;
            foreach ($delegados as $delegado) {
                if ($delegado->getIsDirector())
                {
                    $director = $delegado;
                }
            }
            if ($director == null)
            {
                $director = $builder->buildDelegado();
                $director->setNombre('Presidente');
                $director->setIsConseller(true);
                $director->setIsDirector(true);
                $director->setDocumentNum('No Document');
                $director->setDocumentType('No Type');
                $storage->save($director);
            }
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize($director, 'json', SerializationContext::create()->setGroups(array('Default','conseller'))));
    }// "get_votos"     [GET] /delegados/director
    
    public function cgetSecretaryAction()
    { 
        $storage = $this->get('lugh.server')->getStorage();
        $builder = $this->get('lugh.server')->getBuilder();
        $serializer = $this->container->get('jms_serializer');
        try {
            $delegados = $storage->getSecretarys();
            $secretary = null;
            foreach ($delegados as $delegado) {
                if ($delegado->getIsSecretary())
                {
                    $secretary = $delegado;
                }
            }
            if ($secretary == null)
            {
                $secretary = $builder->buildDelegado();
                $secretary->setNombre('Secretario');
                $secretary->setIsConseller(true);
                $secretary->setIsSecretary(true);
                $secretary->setDocumentNum('No Document');
                $secretary->setDocumentType('No Type');
                $storage->save($secretary);
            }
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize($secretary, 'json', SerializationContext::create()->setGroups(array('Default','conseller'))));
    }// "get_votos"     [GET] /delegados/secretary
    
    public function getAction($id) // GET Resource
    {
        $storage = $this->get('lugh.server')->getStorage();
        $serializer = $this->container->get('jms_serializer');
        try {
            $delegado = $storage->getDelegado($id);
            $items = array('delegados' => $delegado);
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize($items, 'json', SerializationContext::create()->setGroups(array('Default', 'Votacion', 'VotacionSerie'))));

    }// "get_resource"      [GET] /delegados/{id}
    
    public function getBydocumentAction($document) // GET Resource
    {
        $storage = $this->get('lugh.server')->getStorage();
        $serializer = $this->container->get('jms_serializer');
        try {
            $delegado = $storage->getDelegadoByDocument($document);
            $items = array('delegados' => $delegado);
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize($items, 'json', SerializationContext::create()->setGroups(array('Default', 'Votacion', 'VotacionSerie'))));

    }// "get_resource"      [GET] /delegados/{document}/bydocument

    public function postAction() // Create Resource
    {
        $serializer = $this->container->get('jms_serializer');
        $storage = $this->get('lugh.server')->getStorage();
        $builder = $this->get('lugh.server')->getBuilder();
        $request = $this->get('request');
        try {       
           $delegado = $builder->buildDelegado();
           $delegado->setNombre($request->get('nombre',''));
           //@TODO: validar documento
           $delegado->setDocumentNum($request->get('documentNum',''));
           $delegado->setDocumentType($request->get('documentType',''));
           $delegado->setIsConseller(false); 
           $storage->save($delegado);
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize(array('success' => $delegado), 'json', SerializationContext::create()->setGroups(array('Default', 'Votacion', 'VotacionSerie'))));    
    }// "new_opcionesvotos"     [POST] /delegados
    
    /**
     * @Permissions(perm={"ROLE_CUSTOMER"})
     */
    public function postConsellersAction() // Create Resource
    {
        $serializer = $this->container->get('jms_serializer');
        $storage = $this->get('lugh.server')->getStorage();
        $builder = $this->get('lugh.server')->getBuilder();
        $request = $this->get('request');
        try {       
           $delegado = $builder->buildDelegado();
           $delegado->setNombre($request->get('name',''));
           $delegado->setIsConseller(true); 
           $storage->save($delegado);
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize(array('success' => $delegado), 'json', SerializationContext::create()->setGroups(array('Default'))));
    }// "new_opcionesvotos"     [POST] /delegados/consellers
    
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

