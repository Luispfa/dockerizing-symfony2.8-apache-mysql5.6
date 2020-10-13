<?php

namespace Lugh\WebAppBundle\Controller\ApiRestV1;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use JMS\Serializer\SerializationContext;
use Lugh\WebAppBundle\Annotations\Permissions;
use Symfony\Component\Config\Definition\Exception\Exception;
use Lugh\WebAppBundle\Lib\External\NifCifNiePredictor;
use Lugh\WebAppBundle\DomainLayer\State\StateClass;

/**
 * @RouteResource("Accionista")
 */
class AccionistaController extends Controller {

    /**
     * @Permissions(perm={"ROLE_CUSTOMER"})
     */
    public function cgetAction() { /* @TODO: Apps */
        $valid = $this->checkHeaders();

        if ($valid) {
            $serializer = $this->container->get('jms_serializer');
            $storage = $this->get('lugh.server')->getStorage();
            try {
                $accionista = $storage->getAccionistas();
            } catch (Exception $exc) {
                return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
            }
            return new Response($serializer->serialize($accionista, 'json', SerializationContext::create()->setGroups(array('Default', 'Documents', 'ItemAccionista', 'Personal', 'Apps', 'App'))));
        } else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

// "get_Accionistas"     [GET] /accionistas

    /**
     * @Permissions(perm={"ROLE_CUSTOMER"})
     */
    public function cgetExcelAction() {
        $valid = $this->checkHeaders();

        $valid = $this->checkHeaders();

        if ($valid) {
            $storage = $this->get('lugh.server')->getStorage();
            $accionistas = $storage->getAccionistas();
            
            $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();

            $phpExcelObject->getProperties()->setCreator("Header")
                    ->setLastModifiedBy("Header")
                    ->setTitle("Accionistas")
                    ->setSubject($this->container->get('lugh.parameters')->getByKey('Config.customer.title', 'Lugh sharesholders'))
                    ->setDescription("")
                    ->setKeywords("")
                    ->setCategory("");
            
            $col = 0;
            $phpExcelObject->setActiveSheetIndex(0)
                    ->setCellValueByColumnAndRow($col++, 1, 'Nombre')
                    ->setCellValueByColumnAndRow($col++, 1, 'Tipo Doc')
                    ->setCellValueByColumnAndRow($col++, 1, 'Documento')
                    ->setCellValueByColumnAndRow($col++, 1, 'Telefono')
                    ->setCellValueByColumnAndRow($col++, 1, 'Acciones')
                    ->setCellValueByColumnAndRow($col++, 1, 'Correo electrónico')
                    ->setCellValueByColumnAndRow($col++, 1, 'Fecha registro')
                    ->setCellValueByColumnAndRow($col++, 1, 'Estado')
                    ->setCellValueByColumnAndRow($col++, 1, 'Certificado')
            ;

            $row = 1;
            $maxcol = 0;
            $tipos = [];
            
            foreach ($accionistas as $accionista) {
                $row++;
                $col = 0;
                //$acc = $accion->getAccionista();
                //$del = $method_exists($accion,'getDelegado') ? $accion->getDelegado()->getName() : '';
                //$obs = $accion::nameClass == 'Delegacion' ? $accion->getObservaciones() : '';
                
                $certificado = $accionista->getUser()->getCert();
                $phpExcelObject->getActiveSheet()
                        ->setCellValueByColumnAndRow($col++, $row, $accionista->getName())
                        ->setCellValueByColumnAndRow($col++, $row, $accionista->getDocumentType())
                        ->setCellValueByColumnAndRow($col++, $row, $accionista->getDocumentNum())
                        ->setCellValueByColumnAndRow($col++, $row, $accionista->getTelephone())
                        ->setCellValueByColumnAndRow($col++, $row, $accionista->getSharesNum())
                        ->setCellValueByColumnAndRow($col++, $row, $accionista->getUser()->getEmail())
                        ->setCellValueByColumnAndRow($col++, $row, $accionista->getUser()->getDateTime()->format('d-m-Y H:i:s'))
                        ->setCellValueByColumnAndRow($col++, $row, $this->getStateName($state = $accionista->getItemAccionista()->getState()))
                        ->setCellValueByColumnAndRow($col++, $row, $certificado != null && $certificado != '' ? 'Sí' : 'No')
                ;

                $count = 0;
            }

            $phpExcelObject->getActiveSheet()->setTitle('Simple');
            // Set active sheet index to the first sheet, so Excel opens this as the first sheet
            $phpExcelObject->setActiveSheetIndex(0);
            
            // create the writer
            $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
            // create the response
            $response = $this->get('phpexcel')->createStreamedResponse($writer);
            // adding headers
            $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');

            $response->headers->set('Content-Disposition', 'attachment;filename=stream-file.xls');

            $response->headers->set('Pragma', 'public');
            $response->headers->set('Cache-Control', 'maxage=1');
            
            return $response;
        } else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

// "get_accionistas"     [GET] /accionistas/excel
    
    
    public function getAction($id) { // GET Resource /* @TODO: Apps */
        $valid = $this->checkHeaders();

        if ($valid) {
            $serializer = $this->container->get('jms_serializer');
            $accionista = $this->getAccionista($id);
            if ($accionista instanceof Response)
                return $accionista;
            return new Response($serializer->serialize($accionista, 'json', SerializationContext::create()->setGroups(array('Default', 'Documents', 'ItemAccionista', 'Apps', 'Personal', 'App'))));
        }else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

// "get_Accionista"      [GET] /accionistas/{id}

    public function userAction() { // GET Resource
        $valid = $this->checkHeaders();

        if ($valid) {
            $serializer = $this->container->get('jms_serializer');
            $user = $this->getUser();
            try {
                $accionista = $user->getAccionista();
            } catch (Exception $exc) {
                return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
            }
            return new Response($serializer->serialize($accionista, 'json', SerializationContext::create()->setGroups(array('Default', 'Documents', 'ItemAccionista', 'App', 'Personal'))));
        } else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

// "get_Accionista"      [GET] /accionistas/user

    /**
     * @Permissions(perm={"ROLE_CUSTOMER"})
     */
    public function livesAction() { // GET Resource
        $valid = $this->checkHeaders();

        if ($valid) {

            $serializer = $this->container->get('jms_serializer');
            $storage = $this->get('lugh.server')->getStorage();
            try {
                $accionista = $storage->getAccionistas();
            } catch (Exception $exc) {
                return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
            }
            return new Response($serializer->serialize($accionista, 'json', SerializationContext::create()->setGroups(array('Default', 'Lives'))));
        } else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

// "get_Accionista"      [GET] /accionistas/lives

    /**
     * @Permissions(perm={"ROLE_CUSTOMER"})
     */
    public function requestsavAction() { // GET Resource
        $valid = $this->checkHeaders();

        if ($valid) {
            $serializer = $this->container->get('jms_serializer');
            $storage = $this->get('lugh.server')->getStorage();
            try {
                $accionista = $storage->getAccionistasRequestAV();
            } catch (Exception $exc) {
                return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
            }
            return new Response($serializer->serialize($accionista, 'json', SerializationContext::create()->setGroups(array('Default', 'Documents', 'ItemAccionista', 'App'))));
        } else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

// "get_Accionista"      [GET] /accionistas/requestav

    /**
     * @Permissions(perm={"ROLE_CUSTOMER"})
     */
    public function acreditadosAction() { // GET Resource
        $valid = $this->checkHeaders();

        if ($valid) {
            $serializer = $this->container->get('jms_serializer');
            $storage = $this->get('lugh.server')->getStorage();
            try {
                $accionista = $storage->getAccionistasAcreditados();
            } catch (Exception $exc) {
                return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
            }
            return new Response($serializer->serialize($accionista, 'json', SerializationContext::create()->setGroups(array('Default', 'Documents', 'ItemAccionista', 'App'))));
        } else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

// "get_Accionista"      [GET] /accionistas/acreditados

    /**
     * @Permissions(perm={"ROLE_CUSTOMER"})
     */
    public function getLiveAction($id) { // GET Resource
        $valid = $this->checkHeaders();

        if ($valid) {
            $serializer = $this->container->get('jms_serializer');
            $storage = $this->get('lugh.server')->getStorage();
            try {
                $accionista = $storage->getAccionista($id);
            } catch (Exception $exc) {
                return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
            }
            return new Response($serializer->serialize($accionista, 'json', SerializationContext::create()->setGroups(array('Default', 'Lives'))));
        } else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

// "get_Accionista"      [GET] /accionistas/{id}/live

    /**
     * @Permissions(perm={"ROLE_CUSTOMER"})
     */
    public function postAction() { // Create Resource /* @TODO: Apps */
        $valid = $this->checkHeaders();

        if ($valid) {
            $serializer = $this->get('jms_serializer');
            $request = $this->get('request');
            $builder = $this->get('lugh.server')->getBuilder();
            $storage = $this->get('lugh.server')->getStorage();


            $userjson = $request->get('user', '');
            $username = $userjson['username'];
            $email = $userjson['email'];
            $token = $userjson['token'];
            $locale = $request->get('lang', 'es_es');
            $password = substr(md5(time()), 0, 8);

            $maxShares = $this->get('lugh.parameters')->getByKey('Config.accionista.accionesMin', null);

            $accionistajson = $request->get('accionista', '');
            $recaptchajson = $request->get('reCaptcha', '');
            try {
                $this->DocValid($accionistajson['documentType'], $accionistajson['documentNum']);

                $user = $this->setAccionista($username, $password, $email);
                $user->setLang($locale);
                $user->setDateTime(new \DateTime());
                $accionista = $builder->buildAccionista();
                //$accionista->setApps($this->getApss());
                $accionista->setName($accionistajson['name']);
                $accionista->setUser($user);

                $accionista->setRepresentedBy($accionistajson['representedBy']);
                $accionista->setDocumentNum($accionistajson['documentNum']);
                $accionista->setDocumentType($accionistajson['documentType']);

                if (( $maxShares !== null && $accionistajson['sharesNum'] >= $maxShares) ||
                        ( $maxShares === null && $accionistajson['sharesNum'] >= 1 )) {
                    $accionista->setSharesNum($accionistajson['sharesNum']);
                }
                $accionista->setTelephone($accionistajson['telephone']);

                $itemAccionista = $builder->buildItemAccionista();
                $itemAccionista->setAutor($accionista);
                $itemAccionista->setDateTime(new \DateTime());
                $storage->save($itemAccionista);

                if ($token != '') {
                    $this->setDocumentsOwner($storage->getDocumentsByToken($token), $user);
                }
            } catch (Exception $exc) {
                return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
            }
            return new Response($serializer->serialize(array('success' => $accionista), 'json', SerializationContext::create()->setGroups(array('Default', 'Documents', 'ItemAccionista', 'App'))));
        } else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

// "new_Accionistas"     [POST] /accionistas

    /**
     * @Permissions(perm={"ROLE_CUSTOMER"})
     */
    public function putPasswordAction($id) {
        $valid = $this->checkHeaders();

        if ($valid) {
            $serializer = $this->container->get('jms_serializer');
            $storage = $this->get('lugh.server')->getStorage();
            $mailer = $this->get('lugh.server')->getMailer();
            $user = $storage->getUser($id);

            try {
                $user->setPlainPassword(substr(md5(time()), 0, 8));
                $this->get('fos_user.user_manager')->updateUser($user, false);
                $storage->save($user);
                $mailer->formatandsend($user, 'resetPassword');
            } catch (Exception $exc) {
                return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
            }
            return new Response($serializer->serialize(array('success' => true), 'json'));
        } else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

    /**
     * @Permissions(perm={"IS_AUTHENTICATED_REMEMBERED"})
     */
    
    public function postChangepasswordAction() {
        $valid = $this->checkHeaders();

        if ($valid) {
            $serializer = $this->container->get('jms_serializer');
            $storage = $this->get('lugh.server')->getStorage();
            $mailer = $this->get('lugh.server')->getMailer();
            $user = $this->getUser();
            $request = $this->get('request');
            
            $password = $request->get('current_password');
            $factory = $this->get('security.encoder_factory');
            $encoder = $factory->getEncoder($user);
            $isValid = $encoder->isPasswordValid($user->getPassword(),$password,$user->getSalt());
            
            if($isValid){
            
                try {
                    $user->setPlainPassword($this->checkPassword($request->get('password', substr(md5(time()), 0, 8))));
                    $this->get('fos_user.user_manager')->updateUser($user, false);
                    $storage->save($user);
                    $mailer->formatandsend($user, 'resetPassword');
                } catch (Exception $exc) {
                    return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
                }
                return new Response($serializer->serialize(array('success' => true), 'json'));
            }
        } else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

    public function putAction($id) { // Update Resource
        $serializer = $this->container->get('jms_serializer');
        return new Response($serializer->serialize(array('error' => 'Not implemented'), 'json'));
    }

// "get_Accionista"      [PUT] /accionistas/{id}

    public function deleteAction($id) { // DELETE Resource
        $serializer = $this->container->get('jms_serializer');
        return new Response($serializer->serialize(array('error' => 'Not implemented'), 'json'));
    }

// "delete_Accionista"      [DELETE] /accionista/{id}

    public function getProposalsAction($id) {
        $valid = $this->checkHeaders();

        if ($valid) {
            $serializer = $this->container->get('jms_serializer');
            $accionista = $this->getAccionista($id);
            if ($accionista instanceof Response)
                return $accionista;
            return new Response($serializer->serialize($accionista, 'json', SerializationContext::create()->setGroups(array('Default', 'Proposals', 'NumAdhesion'))));
        }else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

// "get_resource_comments"     [GET] /accionistas/{id}/proposals

    public function getOffersAction($id) {
        $valid = $this->checkHeaders();

        if ($valid) {
            $serializer = $this->container->get('jms_serializer');
            $accionista = $this->getAccionista($id);
            if ($accionista instanceof Response)
                return $accionista;
            return new Response($serializer->serialize($accionista, 'json', SerializationContext::create()->setGroups(array('Default', 'Offers', 'NumAdhesion'))));
        }else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

// "get_resource_comments"     [GET] /accionistas/{id}/offers

    public function getInitiativesAction($id) {
        $valid = $this->checkHeaders();

        if ($valid) {
            $serializer = $this->container->get('jms_serializer');
            $accionista = $this->getAccionista($id);
            if ($accionista instanceof Response)
                return $accionista;
            return new Response($serializer->serialize($accionista, 'json', SerializationContext::create()->setGroups(array('Default', 'Initiatives', 'NumAdhesion'))));
        }else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

// "get_resource_comments"     [GET] /accionistas/{id}/initiatives

    public function getRequestsAction($id) {
        $valid = $this->checkHeaders();

        if ($valid) {
            $serializer = $this->container->get('jms_serializer');
            $accionista = $this->getAccionista($id);
            if ($accionista instanceof Response)
                return $accionista;
            return new Response($serializer->serialize($accionista, 'json', SerializationContext::create()->setGroups(array('Default', 'Requests', 'NumAdhesion'))));
        }else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

// "get_resource_comments"     [GET] /accionistas/{id}/requests

    public function getThreadsAction($id) {
        $valid = $this->checkHeaders();

        if ($valid) {
            $serializer = $this->container->get('jms_serializer');
            $accionista = $this->getAccionista($id);
            if ($accionista instanceof Response)
                return $accionista;
            return new Response($serializer->serialize($accionista, 'json', SerializationContext::create()->setGroups(array('Default', 'Threads', 'messages'))));
        }else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

// "get_resource_comments"     [GET] /accionistas/{id}/threads

    public function getQuestionsAction($id) {
        $valid = $this->checkHeaders();

        if ($valid) {
            $serializer = $this->container->get('jms_serializer');
            $accionista = $this->getAccionista($id);
            if ($accionista instanceof Response)
                return $accionista;
            return new Response($serializer->serialize($accionista, 'json', SerializationContext::create()->setGroups(array('Default', 'Questions', 'messages'))));
        }else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

// "get_resource_comments"     [GET] /accionistas/{id}/questions

    public function getAdhesionsAction($id) {
        $valid = $this->checkHeaders();

        if ($valid) {
            $serializer = $this->container->get('jms_serializer');
            $accionista = $this->getAccionista($id);
            if ($accionista instanceof Response)
                return $accionista;
            return new Response($serializer->serialize($accionista, 'json', SerializationContext::create()->setGroups(array('Default', 'AdhesionsInitiatives', 'AdhesionsOffers', 'AdhesionsRequests', 'AdhesionsProposals', 'proposals', 'requests', 'offers', 'initiatives'))));
        }else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

    public function getAdhesionsinitiativesAction($id) {
        $valid = $this->checkHeaders();

        if ($valid) {
            $serializer = $this->container->get('jms_serializer');
            $accionista = $this->getAccionista($id);
            if ($accionista instanceof Response)
                return $accionista;
            return new Response($serializer->serialize($accionista, 'json', SerializationContext::create()->setGroups(array('Default', 'AdhesionsInitiatives', 'initiatives'))));
        }else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

// "get_resource_comments"     [GET] /accionistas/{id}/adhesionsinitiatives

    public function getAdhesionsoffersAction($id) {
        $valid = $this->checkHeaders();

        if ($valid) {
            $serializer = $this->container->get('jms_serializer');
            $accionista = $this->getAccionista($id);
            if ($accionista instanceof Response)
                return $accionista;
            return new Response($serializer->serialize($accionista, 'json', SerializationContext::create()->setGroups(array('Default', 'AdhesionsOffers', 'offers'))));
        }else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

// "get_resource_comments"     [GET] /accionistas/{id}/adhesionsoffers

    public function getAdhesionsrequestsAction($id) {
        $valid = $this->checkHeaders();

        if ($valid) {
            $serializer = $this->container->get('jms_serializer');
            $accionista = $this->getAccionista($id);
            if ($accionista instanceof Response)
                return $accionista;
            return new Response($serializer->serialize($accionista, 'json', SerializationContext::create()->setGroups(array('Default', 'AdhesionsRequests', 'requests'))));
        }else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

// "get_resource_comments"     [GET] /accionistas/{id}/adhesionsrequests

    public function getAdhesionsproposalsAction($id) {
        $valid = $this->checkHeaders();

        if ($valid) {
            $serializer = $this->container->get('jms_serializer');
            $accionista = $this->getAccionista($id);
            if ($accionista instanceof Response)
                return $accionista;
            return new Response($serializer->serialize($accionista, 'json', SerializationContext::create()->setGroups(array('Default', 'AdhesionsProposals', 'proposals'))));
        }else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

// "get_resource_comments"     [GET] /accionistas/{id}/adhesionsproposals

    public function cgetItemAction($id) {
        $valid = $this->checkHeaders();

        if ($valid) {
            $serializer = $this->container->get('jms_serializer');
            $accionista = $this->getAccionista($id);
            if ($accionista instanceof Response)
                return $accionista;
            return new Response($serializer->serialize($accionista, 'json', SerializationContext::create()->setGroups(array(
                                'Default',
                                'Requests',
                                'Proposals',
                                'Offers',
                                'Initiatives',
                                'Threads',
                                'messages',
                                'NumAdhesion',
                                'adhesions'
            ))));
        }else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

// "get_resources"     [GET] /accionistas/{id}/item

    public function getAccionAction($id) {
        $valid = $this->checkHeaders();

        if ($valid) {
            $serializer = $this->container->get('jms_serializer');
            $accionista = $this->getAccionista($id);
            if ($accionista instanceof Response)
                return $accionista;
            return new Response($serializer->serialize($accionista, 'json', SerializationContext::create()->setGroups(array('Default', 'Accion'))));
        }else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

// "get_resource_comments"     [GET] /accionistas/{id}/accion
    
    public function getAccesoAction($id, $discr) {
        $valid = $this->checkHeaders();
        
        /*$acceso = array
            (
            'voto' => 0,
            'foro' => 1,
            'derecho' => 2,
            'av' => 3
        );*/

        if ($valid) {
            $serializer = $this->container->get('jms_serializer');
            $accionista = $this->getAccionista($id);
            //$accionista->setAcceso = $accionista->getAllAccesoForFind($discr);
            if ($accionista instanceof Response)
                return $accionista;
            return new Response($serializer->serialize($accionista, 'json', SerializationContext::create()->setGroups(array('Default', 'Acceso'))));
        }else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

// "get_resource_comments"     [GET] /accionistas/{id}/acceso/{discr}

    public function getLastaccionAction($id) {
        $valid = $this->checkHeaders();

        if ($valid) {
            $serializer = $this->container->get('jms_serializer');
            $request = $this->get('request');
            $groups = array('Default', 'LastAccion', 'Votacion', 'conseller');
            $groups[] = $request->get('decrypt', false) ? 'VotoSerieDecrypt' : 'VotacionSerie';
            $accionista = $this->getAccionista($id);
            if ($accionista instanceof Response)
                return $accionista;
            return new Response($serializer->serialize($accionista, 'json', SerializationContext::create()->setGroups($groups)));
        }else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

// "get_resource_comments"     [GET] /accionistas/{id}/lastaccion

    /* public function postOffersAction($id)
      {
      $serializer = $this->container->get('jms_serializer');
      $offer = $this->saveItem('Offer', $id);
      return new Response($serializer->serialize(array('success' => $offer), 'json', SerializationContext::create()->setGroups(array('Default'))));
      } // "get_resource_comments"     [POST] /accionistas/{id}/offers

      public function postProposalsAction($id)
      {
      $serializer = $this->container->get('jms_serializer');
      $proposal = $this->saveItem('Proposal', $id);
      return new Response($serializer->serialize(array('success' => $proposal), 'json', SerializationContext::create()->setGroups(array('Default'))));
      } // "get_resource_comments"     [POST] /accionistas/{id}/proposals

      public function postRequestsAction($id)
      {
      $serializer = $this->container->get('jms_serializer');
      $request = $this->saveItem('Request', $id);
      return new Response($serializer->serialize(array('success' => $request), 'json', SerializationContext::create()->setGroups(array('Default'))));
      } // "get_resource_comments"     [POST] /accionistas/{id}/requests

      public function postInitiativesAction($id)
      {
      $serializer = $this->container->get('jms_serializer');
      $initiative = $this->saveItem('Initiative', $id);
      return new Response($serializer->serialize(array('success' => $initiative), 'json', SerializationContext::create()->setGroups(array('Default'))));
      } // "get_resource_comments"     [POST] /accionistas/{id}/initiatives

      public function postThreadsAction($id)
      {
      $serializer = $this->container->get('jms_serializer');
      $thread = $this->saveItem('Thread', $id);
      return new Response($serializer->serialize(array('success' => $thread), 'json', SerializationContext::create()->setGroups(array('Default'))));
      } // "get_resource_comments"     [POST] /accionistas/{id}/threads
     */

    public function putAcreditadoAction($acreditado) { // Update Resource
        $valid = $this->checkHeaders();

        if ($valid) {
            $serializer = $this->container->get('jms_serializer');
            $storage = $this->get('lugh.server')->getStorage();
            $behavior = $this->get('lugh.server')->getBehavior();
            $user = $this->getUser();
            try {
                $accionista = $user->getAccionista();
                $behavior->setAccionistaAcreditado($accionista, $acreditado);
                $storage->save($accionista);
            } catch (Exception $exc) {
                return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
            }
            return new Response($serializer->serialize(array('success' => $accionista), 'json', SerializationContext::create()->setGroups(array('Default'))));
        } else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

// "put_resource"      [PUT] /accionistas/{acreditado}/acreditado 
    /**
     * @Permissions(perm={"ROLE_CUSTOMER"})
     */
    public function putAppAction($id) { // Update Resource /* @TODO: Apps */
        $valid = $this->checkHeaders();

        if ($valid) {
            $serializer = $this->container->get('jms_serializer');
            $storage = $this->get('lugh.server')->getStorage();
            $request = $this->get('request');

            try {
                $apps = $request->get('apps', false);
                $accionista = $this->getAccionista($id);
                if (is_array($apps)) {
                    foreach ($accionista->getApp() as $accionista_app) {
                        if ($apps[strtolower($accionista_app->getAppClass())]) {
                            //$accionista_app->publica();
                        } else {
                            $accionista_app->retorna();
                        }
                    }
                    $storage->save($accionista);
                } else {
                    throw new Exception('Wrong Apps');
                }
            } catch (Exception $exc) {
                return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
            }
            return new Response($serializer->serialize(array('success' => $accionista), 'json', SerializationContext::create()->setGroups(array('Default', 'Apps'))));
        } else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

// "put_resource"      [PUT] /accionistas/{id}/app  

    /**
     * @Permissions(perm={"ROLE_CUSTOMER"})
     */
    public function putLiveAction($id, $idLive) { // Update Resource
        $valid = $this->checkHeaders();

        if ($valid) {
            $serializer = $this->container->get('jms_serializer');
            $storage = $this->get('lugh.server')->getStorage();
            $request = $this->get('request');
            try {
                $active = $request->get('enabled', false);
                $accionista = $this->getAccionista($id);
                $accionista->setLiveActive($idLive, $active);
                $storage->save($accionista);
            } catch (Exception $exc) {
                return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
            }
            return new Response($serializer->serialize(array('success' => $accionista), 'json', SerializationContext::create()->setGroups(array('Default', 'Apps'))));
        } else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

// "put_resource"      [PUT] /accionistas/{id}/live/{idLive}

    /**
     * @Permissions(perm={"ROLE_CUSTOMER"})
     */
    public function putPendingAction($id) { // Update Resource
        $valid = $this->checkHeaders();

        if ($valid) {
            $serializer = $this->container->get('jms_serializer');
            $storage = $this->get('lugh.server')->getStorage();
            $builder = $this->get('lugh.server')->getBuilder();
            $mailer = $this->get('lugh.server')->getMailer();
            $user = $this->getUser();
            try {
                $accionista = $this->getAccionista($id);
                $request = $this->get('request');
                if ($request->get('message', false)) {
                    $message = $builder->buildMessage();
                    $message->setAutor($user);
                    $message->setBody($request->get('message', ''));
                    $message->setDateTime(new \DateTime());
                    $accionista->getItemAccionista()->addMessage($message);
                }
                $mailer->setWorkflowOff(!$request->get('sendMail', true));
                $accionista->pendiente($request->get('message', null));
                $storage->save($accionista);
            } catch (Exception $exc) {
                return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
            }
            return new Response($serializer->serialize(array('success' => $accionista), 'json', SerializationContext::create()->setGroups(array('Default', 'ItemAccionista', 'App'))));
        } else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

// "put_resource"      [PUT] /accionistas/{id}/pending

    /**
     * @Permissions(perm={"ROLE_CUSTOMER"})
     */
    public function putPublicAction($id) { // Update Resource
        $valid = $this->checkHeaders();

        if ($valid) {
            $serializer = $this->container->get('jms_serializer');
            $storage = $this->get('lugh.server')->getStorage();
            $builder = $this->get('lugh.server')->getBuilder();
            $mailer = $this->get('lugh.server')->getMailer();
            $user = $this->getUser();
            try {
                $accionista = $this->getAccionista($id);
                $request = $this->get('request');
                if ($request->get('message', false)) {
                    $message = $builder->buildMessage();
                    $message->setAutor($user);
                    $message->setBody($request->get('message', ''));
                    $message->setDateTime(new \DateTime());
                    $accionista->getItemAccionista()->addMessage($message);
                }
                $mailer->setWorkflowOff(!$request->get('sendMail', true));
                $accionista->publica($request->get('message', null));
                $storage->save($accionista);
            } catch (Exception $exc) {
                return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
            }
            return new Response($serializer->serialize(array('success' => $accionista), 'json', SerializationContext::create()->setGroups(array('Default', 'ItemAccionista', 'App'))));
        } else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

// "put_resource"      [PUT] /accionistas/{id}/public

    /**
     * @Permissions(perm={"ROLE_CUSTOMER"})
     */
    public function putRetornateAction($id) { // Update Resource
        $valid = $this->checkHeaders();

        if ($valid) {
            $serializer = $this->container->get('jms_serializer');
            $storage = $this->get('lugh.server')->getStorage();
            $builder = $this->get('lugh.server')->getBuilder();
            $mailer = $this->get('lugh.server')->getMailer();
            $user = $this->getUser();
            try {
                $accionista = $this->getAccionista($id);
                $request = $this->get('request');
                if ($request->get('message', false)) {
                    $message = $builder->buildMessage();
                    $message->setAutor($user);
                    $message->setBody($request->get('message', ''));
                    $message->setDateTime(new \DateTime());
                    $accionista->getItemAccionista()->addMessage($message);
                }
                $mailer->setWorkflowOff(!$request->get('sendMail', true));
                $accionista->retorna($request->get('message', null));
                $storage->save($accionista);
            } catch (Exception $exc) {
                return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
            }
            return new Response($serializer->serialize(array('success' => $accionista), 'json', SerializationContext::create()->setGroups(array('Default', 'ItemAccionista', 'App'))));
        } else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

// "put_resource"      [PUT] /accionistas/{id}/retornate

    /**
     * @Permissions(perm={"ROLE_CUSTOMER"})
     */
    public function putRejectAction($id) { // Update Resource
        $valid = $this->checkHeaders();

        if ($valid) {
            $serializer = $this->container->get('jms_serializer');
            $storage = $this->get('lugh.server')->getStorage();
            $builder = $this->get('lugh.server')->getBuilder();
            $mailer = $this->get('lugh.server')->getMailer();
            $user = $this->getUser();
            try {
                $accionista = $this->getAccionista($id);
                $request = $this->get('request');
                if ($request->get('message', false)) {
                    $message = $builder->buildMessage();
                    $message->setAutor($user);
                    $message->setBody($request->get('message', ''));
                    $message->setDateTime(new \DateTime());
                    $accionista->getItemAccionista()->addMessage($message);
                }
                $mailer->setWorkflowOff(!$request->get('sendMail', true));
                $accionista->rechaza($request->get('message', null));
                $storage->save($accionista);
            } catch (Exception $exc) {
                return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
            }
            return new Response($serializer->serialize(array('success' => $accionista), 'json', SerializationContext::create()->setGroups(array('Default', 'ItemAccionista', 'App'))));
        } else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

// "put_resource"      [PUT] /accionistas/{id}/reject



    /**
     * @Permissions(perm={"ROLE_CUSTOMER"})
     */
    /*
      public function putChangestateAction($id, $state)
      {
      $serializer = $this->container->get('jms_serializer');
      $storage = $this->get('lugh.server')->getStorage();
      $accionista = $this->getAccionista($id);
      $statesMethod = array
      (
      'pending'   =>  'pendiente',
      'public'    =>  'publica',
      'retornate' =>  'retorna',
      'reject'    =>  'rechaza',
      );
      if (!isset($statesMethod[$state]))
      {
      return new Response($serializer->serialize(array('error' => 'Not State'), 'json'));
      }
      try {
      $accionista = $accionista->{$statesMethod[$state]}();
      $storage->save($accionista);
      } catch (Exception $exc) {
      return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
      }
      return new Response($serializer->serialize(array('success' => $accionista), 'json', SerializationContext::create()->setGroups(array('Default'))));
      } // "get_resource_comments"     [PUT] /accionistas/{id}/changestates/{state}
     * 
     */

    /**
     * @Permissions(perm={"ROLE_CUSTOMER"})
     */
    public function putAppPendingAction($id, $discr) { // Update Resource
        $valid = $this->checkHeaders();

        if ($valid) {
            $serializer = $this->container->get('jms_serializer');
            try {
                $accionista = $this->putAppState($id, $discr, 'pending');
            } catch (Exception $exc) {
                return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
            }
            return new Response($serializer->serialize(array('success' => $accionista), 'json', SerializationContext::create()->setGroups(array('Default', 'App'))));
        } else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

// "put_resource"      [PUT] /accionistas/{id}/apps/{discr}/pending

    /**
     * @Permissions(perm={"ROLE_CUSTOMER"})
     */
    public function putAppPublicAction($id, $discr) { // Update Resource
        $valid = $this->checkHeaders();

        if ($valid) {
            $serializer = $this->container->get('jms_serializer');
            try {
                $accionista = $this->putAppState($id, $discr, 'public');
            } catch (Exception $exc) {
                return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
            }
            return new Response($serializer->serialize(array('success' => $accionista), 'json', SerializationContext::create()->setGroups(array('Default', 'App'))));
        } else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

// "put_resource"      [PUT] /accionistas/{id}/apps/{discr}/public

    /**
     * @Permissions(perm={"ROLE_CUSTOMER"})
     */
    public function putAppRetornateAction($id, $discr) { // Update Resource
        $valid = $this->checkHeaders();

        if ($valid) {
            $serializer = $this->container->get('jms_serializer');
            try {
                $accionista = $this->putAppState($id, $discr, 'retornate');
            } catch (Exception $exc) {
                return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
            }
            return new Response($serializer->serialize(array('success' => $accionista), 'json', SerializationContext::create()->setGroups(array('Default', 'App'))));
        } else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

// "put_resource"      [PUT] /accionistas/{id}/apps/{discr}/retornate

    /**
     * @Permissions(perm={"ROLE_CUSTOMER"})
     */
    public function putAppRejectAction($id, $discr) { // Update Resource
        $valid = $this->checkHeaders();

        if ($valid) {
            $serializer = $this->container->get('jms_serializer');
            try {
                $accionista = $this->putAppState($id, $discr, 'reject');
            } catch (Exception $exc) {
                return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
            }
            return new Response($serializer->serialize(array('success' => $accionista), 'json', SerializationContext::create()->setGroups(array('Default', 'App'))));
        } else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

// "put_resource"      [PUT] /accionistas/{id}/apps/{discr}/reject
    
    public function putAccesoAction($id, $discr) { // Update Resource
        $valid = $this->checkHeaders();

        if ($valid) {
            $serializer = $this->container->get('jms_serializer');
            try {
                
                $storage = $this->get('lugh.server')->getStorage();
                $builder = $this->get('lugh.server')->getBuilder();
                
                $accesos = array(
                    'voto'      =>  $builder->buildAccesoVoto(),
                    'foro'      =>  $builder->buildAccesoForo(),
                    'derecho'   =>  $builder->buildAccesoDerecho(),
                    'av'        =>  $builder->buildAccesoAV(),
                );

                if (!isset($accesos[$discr])) {
                    throw new Exception('Not Acceso');
                }

                $accionista = $this->getAccionista($id);
                $accionista->addAcceso($accesos[$discr]);
                $storage->save($accionista);
                
            } catch (Exception $exc) {
                return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
            }
            return new Response($serializer->serialize(array('success' => $accionista), 'json', SerializationContext::create()->setGroups(array('Default', 'App'))));
        } else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

// "put_resource"      [PUT] /accionistas/{id}/accesos/{discr}/

    public function postRegrantVotoAction() {
        $valid = $this->checkHeaders();

        if ($valid) {
            $serializer = $this->container->get('jms_serializer');
            $behavior = $this->get('lugh.server')->getBehavior();
            $request = $this->get('request');

            try {
                $user = $this->getUser();

                $userData = $request->get('user', false);
                $userData['token'] = $request->get('token', false);

                $accionistaElement['name'] = $request->get('name', '');
                $accionistaElement['representedBy'] = $request->get('represented_by', '');
                $accionistaElement['documentNum'] = $request->get('document_num', '');
                $accionistaElement['documentType'] = $request->get('document_type', '');
                $accionistaElement['sharesNum'] = $request->get('shares_num', '');
                $message = $request->get('message', false);

                $accionista = $behavior->regrantVoto($user, $userData, $accionistaElement, $message);
            } catch (Exception $exc) {
                return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
            }
            return new Response($serializer->serialize(array('success' => $accionista), 'json', SerializationContext::create()->setGroups(array('Default', 'Documents', 'ItemAccionista'))));
        } else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

// "get_resource_comments"     [POST] /regrants/votos

    public function postRegrantForoAction() {
        $valid = $this->checkHeaders();

        if ($valid) {
            $serializer = $this->container->get('jms_serializer');
            $behavior = $this->get('lugh.server')->getBehavior();
            $request = $this->get('request');

            try {
                $user = $this->getUser();

                $userData = $request->get('user', false);
                $userData['token'] = $request->get('token', false);

                $accionistaElement['name'] = $request->get('name', '');
                $accionistaElement['representedBy'] = $request->get('represented_by', '');
                $accionistaElement['documentNum'] = $request->get('document_num', '');
                $accionistaElement['documentType'] = $request->get('document_type', '');
                $accionistaElement['sharesNum'] = $request->get('shares_num', '');
                $message = $request->get('message', false);

                $accionista = $behavior->regrantForo($user, $userData, $accionistaElement, $message);
            } catch (Exception $exc) {
                return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
            }
            return new Response($serializer->serialize(array('success' => $accionista), 'json', SerializationContext::create()->setGroups(array('Default', 'Documents', 'ItemAccionista'))));
        } else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

// "get_resource_comments"     [POST] /regrants/foros

    public function postRegrantDerechoAction() {
        $valid = $this->checkHeaders();

        if ($valid) {
            $serializer = $this->container->get('jms_serializer');
            $behavior = $this->get('lugh.server')->getBehavior();
            $request = $this->get('request');

            try {
                $user = $this->getUser();

                $userData = $request->get('user', false);
                $userData['token'] = $request->get('token', false);

                $accionistaElement['name'] = $request->get('name', '');
                $accionistaElement['representedBy'] = $request->get('represented_by', '');
                $accionistaElement['documentNum'] = $request->get('document_num', '');
                $accionistaElement['documentType'] = $request->get('document_type', '');
                $accionistaElement['sharesNum'] = $request->get('shares_num', '');
                $message = $request->get('message', false);

                $accionista = $behavior->regrantDerecho($user, $userData, $accionistaElement, $message);
            } catch (Exception $exc) {
                return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
            }
            return new Response($serializer->serialize(array('success' => $accionista), 'json', SerializationContext::create()->setGroups(array('Default', 'Documents', 'ItemAccionista'))));
        } else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

// "get_resource_comments"     [POST] /regrants/derechos

    public function postRegrantAvAction() {
        $valid = $this->checkHeaders();

        if ($valid) {
            $serializer = $this->container->get('jms_serializer');
            $behavior = $this->get('lugh.server')->getBehavior();
            $request = $this->get('request');

            try {
                $user = $this->getUser();

                $userData = $request->get('user', false);
                $userData['token'] = $request->get('token', false);

                $accionistaElement['name'] = $request->get('name', '');
                $accionistaElement['representedBy'] = $request->get('represented_by', '');
                $accionistaElement['documentNum'] = $request->get('document_num', '');
                $accionistaElement['documentType'] = $request->get('document_type', '');
                $accionistaElement['sharesNum'] = $request->get('shares_num', '');
                $message = $request->get('message', false);

                $accionista = $behavior->regrantAv($user, $userData, $accionistaElement, $message);
            } catch (Exception $exc) {
                return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
            }
            return new Response($serializer->serialize(array('success' => $accionista), 'json', SerializationContext::create()->setGroups(array('Default', 'Documents', 'ItemAccionista'))));
        } else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

// "get_resource_comments"     [POST] /regrants/avs

    /**
     * @Permissions(perm={"ROLE_CUSTOMER"})
     */
    public function getDocumentAction($id) {
        $valid = $this->checkHeaders();

        if ($valid) {
            $serializer = $this->container->get('jms_serializer');
            $accionista = $this->getAccionista($id);
            $user = $accionista->getUser();
            $documents = $user->getDocuments();
            if ($accionista instanceof Response)
                return $accionista;
            return new Response($serializer->serialize(array('document' => $documents), 'json', SerializationContext::create()->setGroups(array('Default'))));
        }else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

// "get_resource_comments"     [GET] /accionistas/{id}/document

    public function getActionsAction($id) {
        $valid = $this->checkHeaders();

        if ($valid) {
            $serializer = $this->container->get('jms_serializer');
            $accionista = $this->getAccionista($id);
            $actions = $accionista->getAccion();
            if ($accionista instanceof Response)
                return $accionista;
            return new Response($serializer->serialize(array('actions' => $actions), 'json', SerializationContext::create()->setGroups(array('Default'))));
        }else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

// "get_resource_comments"     [GET] /accionistas/{id}/actions

    private function getAccionista($id) {
        $valid = $this->checkHeaders();

        if ($valid) {
            $storage = $this->get('lugh.server')->getStorage();
            $serializer = $this->container->get('jms_serializer');
            try {
                $accionista = $storage->getAccionista($id);
            } catch (Exception $exc) {
                return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
            }
            return $accionista;
        } else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

    private function setAccionista($username, $password, $email) {
        $valid = $this->checkHeaders();

        if ($valid) {
            $builder = $this->get('lugh.server')->getBuilder();
            try {
                $user = $builder->buildUser();
                $user->setUsername($username);
                $user->setPlainPassword($password);
                $user->setEmail($email);
            } catch (\Exception $ex) {
                throw new Exception($ex->getMessage());
            }

            return $user;
        } else {
            return $this->redirect($this->get('request')->headers->get('host'));
        }
    }

    /* private function saveItem($itemName, $id)
      {
      $itemsMethod = array
      (
      'Offer'         =>  'buildOffer',
      'Proposal'      =>  'buildProposal',
      'Request'       =>  'buildRequest',
      'Initiative'    =>  'buildInitiative',
      'Thread'        =>  'buildThread',
      );

      $storage = $this->get('lugh.server')->getStorage();
      $builder = $this->get('lugh.server')->getBuilder();
      $serializer = $this->container->get('jms_serializer');
      try {
      $accionista = $storage->getAccionista($id);
      $item = $builder->{$itemsMethod[$itemName]}();
      $item->setAutor($accionista);
      $storage->save($item);
      } catch (Exception $exc) {
      return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
      }
      return $item;
      } */

    private function checkPassword($password) {
        if (ctype_alpha($password)) {
            throw new Exception('Wrong Password');
        }
        if (ctype_upper($password)) {
            throw new Exception('Wrong Password');
        }
        if (ctype_lower($password)) {
            throw new Exception('Wrong Password');
        }
        if (ctype_digit($password)) {
            throw new Exception('Wrong Password');
        }

        if (strlen($password) <= 3 || strlen($password) > 8) {
            throw new Exception('Wrong Password');
        }
        return $password;
    }

    private function DocValid($tipo, $numDoc) {
        if ($numDoc != "") {
            $type = NifCifNiePredictor::predict($numDoc);
            if ($type != $tipo) {
                throw new Exception("NumeroDoc predict as " . $type . " but " . $tipo . " recived");
            }
        }
        return true;
    }

    private function setDocumentsOwner($documents, $user) {
        $storage = $this->get('lugh.server')->getStorage();
        try {
            foreach ($documents as $document) {
                $document->setOwner($user);
                $document->setOwnerbkp($user->getId());
                $document->setToken('');
                //StoreManager::StoreFile($document->getNombreInterno(), $user->getId());
                $storage->addStack($document);
            }
            $storage->saveStack();
        } catch (Exception $exc) {
            throw new Exception($exc->getMessage());
        }
    }

    private function putAppState($id, $discr, $action) {
        $storage = $this->get('lugh.server')->getStorage();
        $builder = $this->get('lugh.server')->getBuilder();
        $mailer = $this->get('lugh.server')->getMailer();
        $user = $this->getUser();
        $apps = array
            (
            'voto' => 'AppVoto',
            'foro' => 'AppForo',
            'derecho' => 'AppDerecho',
            'av' => 'AppAV'
        );

        $actions = array
            (
            'pending' => 'pendiente',
            'public' => 'publica',
            'retornate' => 'retorna',
            'reject' => 'rechaza'
        );
        if (!isset($apps[$discr])) {
            throw new Exception('Not App');
        }
        if (!isset($actions[$action])) {
            throw new Exception('Not Action');
        }


        try {
            $accionista = $this->getAccionista($id);
            $request = $this->get('request');
            if ($request->get('message', false)) {
                $message = $builder->buildMessage();
                $message->setAutor($user);
                $message->setBody($request->get('message', ''));
                $message->setDateTime(new \DateTime());
                foreach ($accionista->getApp() as $app) {
                    if ($app::nameClass == $apps[$discr]) {
                        $app->addMessage($message);
                    }
                }
            }
            $mailer->setWorkflowOff(!$request->get('sendMail', true));
            foreach ($accionista->getApp() as $app) {
                if ($app::nameClass == $apps[$discr]) {
                    $app->{$actions[$action]}($request->get('message', null));
                }
            }
            $storage->save($accionista);
        } catch (Exception $exc) {
            throw new Exception($exc->getMessage());
        }
        return $accionista;
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

    public function getStateName($state){
        
        $stateName = '';
        switch ($state) {
            case StateClass::statePending:
                $stateName = StateClass::actionPending;
                break;
            case StateClass::statePublic:
                $stateName = StateClass::actionPublic;
                break;
            case StateClass::stateRetornate:
                $stateName = StateClass::actionRetornate;
                break;
            case StateClass::stateReject:
                $stateName = StateClass::actionReject;
            default:
                $stateName = 'nuevo';
        }
        
        return $stateName;
    }
    
}
