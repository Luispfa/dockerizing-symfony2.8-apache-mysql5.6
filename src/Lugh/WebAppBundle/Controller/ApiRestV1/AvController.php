<?php

namespace Lugh\WebAppBundle\Controller\ApiRestV1;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Config\Definition\Exception\Exception;
use Lugh\WebAppBundle\Annotations\Permissions;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use JMS\Serializer\SerializationContext;
use Lugh\WebAppBundle\Lib\External;

 /**
 * @RouteResource("Av")
 */
class AvController extends Controller {
    
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
            $avs = $storage->getLastAvs();
            $items = array('avs' => $avs);
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize($items, 'json', SerializationContext::create()->setGroups($groups)));
    }// "get_votos"     [GET] /avs
    
    public function getAction($id) // GET Resource
    {
        $storage = $this->get('lugh.server')->getStorage();
        $serializer = $this->container->get('jms_serializer');
        $request = $this->get('request');
        $groups = array('Default', 'Votacion');
        $groups[] = $request->get('decrypt', false) ? 'VotoSerieDecrypt' : 'VotacionSerie';
        try {
            $av = $storage->getAv($id);
            $items = array('votos' => $av);
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize($items, 'json', SerializationContext::create()->setGroups($groups)));

    }// "get_resource"      [GET] /avs/{id}
    
    public function postAction() // Create Resource
    {
        $serializer = $this->container->get('jms_serializer');
        $storage = $this->get('lugh.server')->getStorage();
        $builder = $this->get('lugh.server')->getBuilder();
        $request = $this->get('request');
        $user = $this->getUser();
        $votos = $request->get('votacion', array());
        $votos = ($votos == null) ? array() : $votos;
        try {       
           $accionista = $user->getAccionista();
           $votacion = $builder->buildAv();
           $votacion->setSharesNum(intval($accionista->getSharesNum()));
           $votacion->setDateTime(new \DateTime());
           $votacion->setAccionista($accionista);
           if(count($request->get('abs_adicional',array())) > 0){

             //$opcionvoto = $storage->getOpcionesVoto($request->get('abs_adicional'));
             //$delegacion->setAbsAdicional($opcionvoto);
            foreach ($request->get('abs_adicional') as $absAdicionalTipo) {
                $votoAbsAdicional   = $builder->buildVotoAbsAdicional();
                $absAdicional       = $storage->getAbsAdicional($absAdicionalTipo['absAdicional_id']);
                $opcionvoto         = $storage->getOpcionesVoto($absAdicionalTipo['opcionVoto_id']);

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
    }// "new_opcionesvotos"     [POST] /avs
    
    public function getCredentialsAction()
    {
        $serializer     = $this->container->get('jms_serializer');
        $storage        = $this->get('lugh.server')->getStorage();
        $user           = $this->getUser();
        $accionista     = $user->getAccionista();
        $data           = array(
            'firstname'     => '',
            'secondname'    => '',
            'email'         => '',
            'organization'  => '',
            'ref_id'        => '',
            'language'      => '',
            'account'       => '',
            'proxy'         => '',
            'time'          => '',
            'hash'          => ''
        );

        try {
            $enabled = true;
            //$url                = $lives[0]->getUrl();
            //
            $url = array();
            
            $credentials = array();
            $lives              = $storage->getLivesByAccionista($accionista);

            foreach ($lives as $key => $live) {
                $url = $live->getUrl();
                $data['account']    = $this->container->get('lugh.parameters')->getByKey('Av.live.account', '392');
                $key                = $this->container->get('lugh.parameters')->getByKey('Av.live.psk', '');
                $data['email']      = $user->getEmail();
                $data['time']       = time();
                $data['hash']       = hash_hmac('sha1', $data['email'].$data['account'].$url.$data['time'], $key);
                $credential        = array(
                    'url'   => $url,
                    'data'  =>  $data
                );
                $credentials[$live->getId()] = $credential;

            }

            /*
            
            $url                = $lives[0]->getUrl();
            $data['account']    = $this->container->get('lugh.parameters')->getByKey('Av.live.account', '392');
            $key                = $this->container->get('lugh.parameters')->getByKey('Av.live.psk', '');
            $data['email']      = $user->getEmail();
            $data['time']       = time();
            $data['hash']       = hash_hmac('sha1', $data['email'].$data['account'].$url.$data['time'], $key);
            $credentials        = array(
                    'url'   => $url,
                    'data'  =>  $data
                );
            
            if ($lives[0]->getEnabled() != true) 
            {
                throw new Exception('Live not enabled');
            }
            */
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize($credentials, 'json'));
    } // "get_resource_comments"     [GET] /av/credentials
    
    public function getStreamingAction()
    {
        $serializer     = $this->container->get('jms_serializer');
        $storage        = $this->get('lugh.server')->getStorage();
        $user           = $this->getUser();
        $accionista     = $user->getAccionista();
        
        try {
            $credentials = array();
            $site_url = $this->container->get('lugh.parameters')->getByKey('Av.live.address', '');
            $api_stream = $this->container->get('lugh.parameters')->getByKey('Av.live.api', '0');
            
            if($api_stream){
                if (strpos($site_url, 'vancast') !== false)
                {
                    $credential        = array(
                        'url'   => External\VanCastApi::GetUrl($user->getEmail(), $accionista->getName()),
                        'data'  =>  ''
                    );
                }
            }
            else
            {
                $credential        = array(
                    'url'   => $site_url,
                    'data'  =>  ''
                );
            }
            $credentials['live-0'] = $credential;
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize($credentials, 'json'));
    } // "get_resource_comments"     [GET] /av/streaming
    
    public function getLivesAction()
    {
        $serializer     = $this->container->get('jms_serializer');
        $storage        = $this->get('lugh.server')->getStorage();
        $user           = $this->getUser();
        $accionista     = $user->getAccionista();
        try {
            $lives      = $storage->getLivesByAccionista($accionista);   
        } catch (Exception $exc) {
            return new Response($serializer->serialize(array('error' => $exc->getMessage()), 'json'));
        }
        return new Response($serializer->serialize(array('success' => $lives), 'json'));       
        
    } // "get_resource_comments"     [GET] /av/lives
    
    
    public function putAction($id) // Update Resource
    { 
        $serializer = $this->container->get('jms_serializer');
        return new Response($serializer->serialize(array('error' => 'Not implemented'), 'json'));
    }// "put_resource"      [PUT] /avs/{id}
    
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

