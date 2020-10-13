<?php

namespace Lugh\WebAppBundle\Controller\ApiRestV1;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Config\Definition\Exception\Exception;
use Lugh\WebAppBundle\Annotations\Permissions;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use JMS\Serializer\SerializationContext;

/**
 * @RouteResource("Voto")
 */
class VotoController extends Controller {

    /**
     * @Permissions(perm={"ROLE_CUSTOMER"})
     */
    public function cgetAction() {
        $valid = $this->checkHeaders();

        if ($valid) {
            $storage = $this->get('lugh.server')->getStorage();
            $serializer = $this->container->get('jms_serializer');
            $request = $this->get('request');
            $groups = array('Default', 'Votacion', 'Personal');
            $groups[] = $request->get('decrypt', false) ? 'VotoSerieDecrypt' : 'VotacionSerie';
            try {
                $votos = $storage->getLastVotos();
                $items = array('votos' => $votos);
            } catch (Exception $exc) {
                return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
            }
            return new Response($serializer->serialize($items, 'json', SerializationContext::create()->setGroups($groups)));
        }else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

// "get_votos"     [GET] /votos

    public function getAction($id) { // GET Resource
        $valid = $this->checkHeaders();

        if ($valid) {
            $storage = $this->get('lugh.server')->getStorage();
            $serializer = $this->container->get('jms_serializer');
            $request = $this->get('request');
            $groups = array('Default', 'Votacion');
            $groups[] = $request->get('decrypt', false) ? 'VotoSerieDecrypt' : 'VotacionSerie';
            try {
                $voto = $storage->getVoto($id);
                $items = array('votos' => $voto);
            } catch (Exception $exc) {
                return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
            }
            return new Response($serializer->serialize($items, 'json', SerializationContext::create()->setGroups($groups)));
        }else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

// "get_resource"      [GET] /votos/{id}

    public function postAction() { // Create Resource
        $valid = $this->checkHeaders();

        if ($valid) {
            $serializer = $this->container->get('jms_serializer');
            $storage = $this->get('lugh.server')->getStorage();
            $builder = $this->get('lugh.server')->getBuilder();
            $request = $this->get('request');
            $user = $this->getUser();
            $votos = $request->get('votacion', array());
            $votos = ($votos == null) ? array() : $votos;
            try {
                $accionista = $user->getAccionista();
                $votacion = $builder->buildVoto();
                $votacion->setSharesNum(intval($request->get('sharesnum', 0)));
                $votacion->setDateTime(new \DateTime());
                $votacion->setAccionista($accionista);
                if (count($request->get('abs_adicional', array())) > 0) {

                    //$opcionvoto = $storage->getOpcionesVoto($request->get('abs_adicional'));
                    //$delegacion->setAbsAdicional($opcionvoto);
                    foreach ($request->get('abs_adicional') as $absAdicionalTipo) {
                        $votoAbsAdicional = $builder->buildVotoAbsAdicional();
                        $absAdicional = $storage->getAbsAdicional($absAdicionalTipo['absAdicional_id']);
                        $opcionvoto = $storage->getOpcionesVoto($absAdicionalTipo['opcionVoto_id']);

                        $votoAbsAdicional->setAbsAdicional($absAdicional);
                        $votoAbsAdicional->setOpcionVoto($opcionvoto);
                        $votacion->addVotoAbsAdicional($votoAbsAdicional);
                    }
                }
                $votacion->addVotos($votos);
                $storage->save($votacion);
            } catch (Exception $exc) {
                return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
            }
            return new Response($serializer->serialize(array('success' => $votacion), 'json', SerializationContext::create()->setGroups(array('Default', 'Votacion', 'VotacionSerie', 'tipoVoto', 'opcionesVoto'))));
        }else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

// "new_opcionesvotos"     [POST] /votos

    public function putAction($id) { // Update Resource
        $serializer = $this->container->get('jms_serializer');
        return new Response($serializer->serialize(array('error' => 'Not implemented'), 'json'));
    }

// "put_resource"      [PUT] /votos/{id}

    public function deleteAction($id) { // DELETE Resource
        $serializer = $this->container->get('jms_serializer');
        return new Response($serializer->serialize(array('error' => 'Not implemented'), 'json'));
    }

// "delete_resource"      [DELETE] /resource/{id} 

    public function getCommentsAction($slug, $id) {
        $serializer = $this->container->get('jms_serializer');
        return new Response($serializer->serialize(array('error' => 'Not implemented'), 'json'));
    }

// "get_resource_comments"     [GET] /opcionesvotos/{slug}/comments/{id}

    public function checkHeaders() {

        $request = $this->get('request');

        $host = $request->headers->get('host');
        $origin = $request->headers->get('origin');
        $referer = $request->headers->get('referer');
        $valid = true;

        if ($origin != null || $referer != null) {

            if ($origin != null && !strpos($origin, $host)) {
                $valid = false;
            }
            if ($referer != null && !strpos($referer, $host)) {
                $valid = false;
            }
        } else {

            $valid = false;
        }

        return $valid && $this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED');
    }

}
