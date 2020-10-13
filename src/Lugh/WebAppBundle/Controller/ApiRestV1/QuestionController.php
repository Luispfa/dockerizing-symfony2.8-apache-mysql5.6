<?php

namespace Lugh\WebAppBundle\Controller\ApiRestV1;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Symfony\Component\HttpFoundation\Response;
use JMS\Serializer\SerializationContext;
use Lugh\WebAppBundle\DomainLayer\State\StateClass;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use Symfony\Component\Config\Definition\Exception\Exception;
use Lugh\WebAppBundle\Annotations\Permissions;

/**
 * @RouteResource("Question")
 */
class QuestionController extends Controller {

    public function cgetAction() {
        $valid = $this->checkHeaders();

        if ($valid) {
            $storage = $this->get('lugh.server')->getStorage();
            $serializer = $this->container->get('jms_serializer');
            try {
                $questions = $storage->getQuestions();
                $items = array('questions' => $questions);
            } catch (Exception $exc) {
                return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
            }
            return new Response($serializer->serialize($items, 'json', SerializationContext::create()->setGroups(array('Default', 'messages'))));
        } else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

// "get_resources"     [GET] /questions

    public function getAction($id) { // GET Resource
        $valid = $this->checkHeaders();

        if ($valid) {
            $storage = $this->get('lugh.server')->getStorage();
            $serializer = $this->container->get('jms_serializer');
            try {
                $question = $storage->getQuestion($id);
                $items = array('questions' => $question);
            } catch (Exception $exc) {
                return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
            }
            return new Response($serializer->serialize($items, 'json', SerializationContext::create()->setGroups(array('Default', 'messages', 'DocumentsQuestions'))));
        } else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

// "get_resource"      [GET] /questions/{id}

    public function postAction() { // Create Resource
        $valid = $this->checkHeaders();

        if ($valid) {
            $serializer = $this->container->get('jms_serializer');
            $storage = $this->get('lugh.server')->getStorage();
            $builder = $this->get('lugh.server')->getBuilder();
            $user = $this->getUser();
            $request = $this->get("request");

            try {
                $accionista = $user->getAccionista();
                $item = $builder->buildQuestion();
                $item->setDateTime(new \DateTime());
                $item->setAutor($accionista);
                $item->setSubject($request->get('subject', ''));
                $item->setBody($request->get('body', ''));


                if (($token = $request->get('token', false)) && $token != '') {
                    $documents = $storage->getDocumentsByToken($token);
                    $this->setDocumentsOwnerQuestion($documents, $user, $item);
                }

                $storage->saveAttachment($item, true, $documents);
            } catch (Exception $exc) {
                return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
            }
            return new Response($serializer->serialize(array('success' => $item), 'json', SerializationContext::create()->setGroups(array('Default'))));
        } else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

// "new_resources"     [POST] /questions

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

    public function getStateAction($state) {
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
                $questions = $storage->getQuestionsByState($states[$state]);
                $items = array('questions' => $questions);
            } catch (Exception $exc) {
                return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
            }
            return new Response($serializer->serialize($items, 'json', SerializationContext::create()->setGroups(array('Default', 'messages'))));
        } else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

// "get_resource_comments"     [GET] /questions/{state}/state

    public function getMessageAction($id) {
        $valid = $this->checkHeaders();

        if ($valid) {
            $serializer = $this->container->get('jms_serializer');
            $storage = $this->get('lugh.server')->getStorage();

            try {
                $question = $storage->getQuestion($id);
                $messages = $question->getMessages();
                $items = array('messages' => $messages);
            } catch (Exception $exc) {
                return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
            }
            return new Response($serializer->serialize(array('success' => $messages), 'json', SerializationContext::create()->setGroups(array('Default'))));
        } else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

// "get_user_comments"   [GET] /threads/{$id}/message

    public function putMessageAction($id) {
        $valid = $this->checkHeaders();

        if ($valid) {
            $serializer = $this->container->get('jms_serializer');
            //return new Response($serializer->serialize(array('error' => 'Not implemented'), 'json'));
            $request = $this->get('request');
            $builder = $this->get('lugh.server')->getBuilder();
            $storage = $this->get('lugh.server')->getStorage();
            $mailer = $this->get('lugh.server')->getMailer();
            $user = $this->getUser();

            try {
                $question = $storage->getQuestion($id);
                $message = $builder->buildMessage();
                $message->setAutor($user);
                $message->setBody($request->get('message', ''));
                $message->setDateTime(new \DateTime());
                if (($token = $request->get('token', false)) && $token != '') {
                    $documents = $storage->getDocumentsByToken($token);
                    $this->setDocumentsOwnerMessage($documents, $question->getAutor()->getUser(), $message);
                }
                $mailer->setWorkflowOff(!$request->get('sendMail', true));
                $question->addMessage($message);
                $storage->save($question);
            } catch (Exception $exc) {
                return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
            }
            return new Response($serializer->serialize(array('success' => $message), 'json', SerializationContext::create()->setGroups(array('Default'))));
        } else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

// "post_user_comments"   [POST] /threads/{$id}/message

    public function putPendingAction($id) { // Update Resource
        $valid = $this->checkHeaders();

        if ($valid) {
            $serializer = $this->container->get('jms_serializer');
            $storage = $this->get('lugh.server')->getStorage();
            $builder = $this->get('lugh.server')->getBuilder();
            $mailer = $this->get('lugh.server')->getMailer();
            $user = $this->getUser();
            try {
                $question = $storage->getQuestion($id);
                $request = $this->get('request');
                if ($request->get('message', false)) {
                    $message = $builder->buildMessage();
                    $message->setAutor($user);
                    $message->setBody($request->get('message', ''));
                    $message->setDateTime(new \DateTime());
                    if (($token = $request->get('token', false)) && $token != '') {
                        $documents = $storage->getDocumentsByToken($token);
                        $this->setDocumentsOwnerMessage($documents, $question->getAutor()->getUser(), $message);
                    }
                    $mailer->setWorkflowOff(!$request->get('sendMail', true));
                    $question->addMessage($message);
                }
                $question->pendiente();
                $storage->save($question);
            } catch (Exception $exc) {
                return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
            }
            return new Response($serializer->serialize(array('success' => $question), 'json', SerializationContext::create()->setGroups(array('Default', 'messages'))));
        } else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

// "put_resource"      [PUT] /threads/{id}/pending

    public function putPublicAction($id) { // Update Resource
        $valid = $this->checkHeaders();

        if ($valid) {
            $serializer = $this->container->get('jms_serializer');
            $storage = $this->get('lugh.server')->getStorage();
            $builder = $this->get('lugh.server')->getBuilder();
            $mailer = $this->get('lugh.server')->getMailer();
            $user = $this->getUser();
            try {
                $question = $storage->getQuestion($id);
                $request = $this->get('request');
                $question->publica();
                if ($request->get('message', false)) {
                    $message = $builder->buildMessage();
                    $message->setAutor($user);
                    $message->setBody($request->get('message', ''));
                    $message->setDateTime(new \DateTime());
                    if (($token = $request->get('token', false)) && $token != '') {
                        $documents = $storage->getDocumentsByToken($token);
                        $this->setDocumentsOwnerMessage($documents, $question->getAutor()->getUser(), $message);
                    }
                    $mailer->setWorkflowOff(!$request->get('sendMail', true));
                    $question->addMessage($message);
                }
                $storage->save($question);
            } catch (Exception $exc) {
                return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
            }
            return new Response($serializer->serialize(array('success' => $question), 'json', SerializationContext::create()->setGroups(array('Default', 'messages'))));
        } else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

// "put_resource"      [PUT] /threads/{id}/public

    public function putRetornateAction($id) { // Update Resource
        $valid = $this->checkHeaders();

        if ($valid) {
            $serializer = $this->container->get('jms_serializer');
            $storage = $this->get('lugh.server')->getStorage();
            $builder = $this->get('lugh.server')->getBuilder();
            $mailer = $this->get('lugh.server')->getMailer();
            $user = $this->getUser();
            try {
                $question = $storage->getQuestion($id);
                $request = $this->get('request');
                $question->retorna();
                if ($request->get('message', false)) {
                    $message = $builder->buildMessage();
                    $message->setAutor($user);
                    $message->setBody($request->get('message', ''));
                    $message->setDateTime(new \DateTime());
                    if (($token = $request->get('token', false)) && $token != '') {
                        $documents = $storage->getDocumentsByToken($token);
                        $this->setDocumentsOwnerMessage($documents, $question->getAutor()->getUser(), $message);
                    }
                    $mailer->setWorkflowOff(!$request->get('sendMail', true));
                    $question->addMessage($message);
                }
                $storage->save($question);
            } catch (Exception $exc) {
                return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
            }
            return new Response($serializer->serialize(array('success' => $question), 'json', SerializationContext::create()->setGroups(array('Default', 'messages'))));
        } else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

// "put_resource"      [PUT] /threads/{id}/retornate

    public function putRejectAction($id) { // Update Resource
        $valid = $this->checkHeaders();

        if ($valid) {
            $serializer = $this->container->get('jms_serializer');
            $storage = $this->get('lugh.server')->getStorage();
            $builder = $this->get('lugh.server')->getBuilder();
            $mailer = $this->get('lugh.server')->getMailer();
            $user = $this->getUser();
            try {
                $question = $storage->getQuestion($id);
                $request = $this->get('request');
                $question->rechaza();
                if ($request->get('message', false)) {
                    $message = $builder->buildMessage();
                    $message->setAutor($user);
                    $message->setBody($request->get('message', ''));
                    $message->setDateTime(new \DateTime());
                    if (($token = $request->get('token', false)) && $token != '') {
                        $documents = $storage->getDocumentsByToken($token);
                        $this->setDocumentsOwnerMessage($documents, $question->getAutor()->getUser(), $message);
                    }
                    $mailer->setWorkflowOff(!$request->get('sendMail', true));
                    $question->addMessage($message);
                }
                $storage->save($question);
            } catch (Exception $exc) {
                return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
            }
            return new Response($serializer->serialize(array('success' => $question), 'json', SerializationContext::create()->setGroups(array('Default', 'messages'))));
        } else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

// "put_resource"      [PUT] /threads/{id}/reject

    private function setDocumentsOwnerMessage($documents, $user, $message) {
        $storage = $this->get('lugh.server')->getStorage();
        try {
            foreach ($documents as $document) {
                $document->setOwner($user);
                $document->setToken('');
                //StoreManager::StoreFile($document->getNombreInterno(), $user->getId());
                $document->setMessage($message);
                $storage->addStack($document);
            }
            $storage->saveStack();
        } catch (Exception $exc) {
            throw new Exception($exc->getMessage());
        }
    }

    private function setDocumentsOwnerQuestion($documents, $user, $question) {
        $storage = $this->get('lugh.server')->getStorage();
        try {
            foreach ($documents as $document) {
                $document->setOwner($user);
                $document->setToken('');
                //StoreManager::StoreFile($document->getNombreInterno(), $user->getId());
                $document->setQuestion($question);
                $storage->addStack($document);
            }
            //$storage->saveStack();
        } catch (Exception $exc) {
            throw new Exception($exc->getMessage());
        }
    }

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
