<?php

namespace Lugh\WebAppBundle\Controller\ApiRestV1;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use JMS\Serializer\SerializationContext;
use Lugh\WebAppBundle\Annotations\Permissions;
use Symfony\Component\Config\Definition\Exception\Exception;
use Lugh\WebAppBundle\DomainLayer\State\StateClass;

/**
 * @RouteResource("Item")
 */
class ItemController extends Controller {

    public function cgetAction() {
        $valid = $this->checkHeaders();

        if ($valid) {
            $storage = $this->get('lugh.server')->getStorage();
            $serializer = $this->container->get('jms_serializer');
            try {
                $proposals = $storage->getProposals();
                $initiatives = $storage->getInitiatives();
                $requests = $storage->getRequests();
                $offers = $storage->getOffers();
                $threads = $storage->getThreads();
                $items = array
                    (
                    'proposals' => $proposals,
                    'initiatives' => $initiatives,
                    'requests' => $requests,
                    'offers' => $offers,
                    'threads' => $threads
                );
            } catch (Exception $exc) {
                return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
            }
            return new Response($serializer->serialize($items, 'json', SerializationContext::create()->setGroups(array('Default'))));
        } else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

// "get_resources"     [GET] /items

    public function getAction($state) { // GET Resource
        $valid = $this->checkHeaders();

        if ($valid) {
            $storage = $this->get('lugh.server')->getStorage();
            $serializer = $this->container->get('jms_serializer');
            $states = array
                (
                'pending' => StateClass::statePending,
                'public' => StateClass::statePublic,
                'retornate' => StateClass::stateRetornate,
                'reject' => StateClass::stateReject,
            );
            if (!isset($states[$state])) {
                return new Response($serializer->serialize(array('error' => 'Not State'), 'json'));
            }
            try {
                $proposals = $storage->getProposalsByState($states[$state]);
                $initiatives = $storage->getInitiativesByState($states[$state]);
                $requests = $storage->getRequestsByState($states[$state]);
                $offers = $storage->getOffersByState($states[$state]);
                $threads = $storage->getThreadsByState($states[$state]);
                $questions = $storage->getQuestionsByState($states[$state]);
                $items = array
                    (
                    'proposals' => $proposals,
                    'initiatives' => $initiatives,
                    'requests' => $requests,
                    'offers' => $offers,
                    'threads' => $threads,
                    'questions' => $questions
                );
            } catch (Exception $exc) {
                return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
            }
            return new Response($serializer->serialize($items, 'json', SerializationContext::create()->setGroups(array('Default'))));
        } else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

// "get_resource"      [GET] /items/{state}

    public function postAction() { // Create Resource
        $serializer = $this->container->get('jms_serializer');
        return new Response($serializer->serialize(array('error' => 'Not implemented'), 'json'));
    }

// "new_resources"     [POST] /items

    public function putAction($id) { // Update Resource
        $serializer = $this->container->get('jms_serializer');
        return new Response($serializer->serialize(array('error' => 'Not implemented'), 'json'));
    }

// "put_resource"      [PUT] /resources/{id}

    public function deleteAction($id) { // DELETE Resource
        $serializer = $this->container->get('jms_serializer');
        return new Response($serializer->serialize(array('error' => 'Not implemented'), 'json'));
    }

// "delete_resource"      [DELETE] /resource/{id} 

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
