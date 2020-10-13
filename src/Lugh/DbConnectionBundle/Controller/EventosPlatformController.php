<?php 

	namespace Lugh\DbConnectionBundle\Controller;

	use Symfony\Bundle\FrameworkBundle\Controller\Controller;
	use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
	use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
	use Symfony\Component\HttpFoundation\Response;
	use Lugh\DbConnectionBundle\Lib\PlatformsManager;
	use Symfony\Component\Config\Definition\Exception\Exception;
	use Doctrine\DBAL\DBALException;

    use Lugh\WebAppBundle\Entity\Live;

	/**
	 * @Route("/eventosplatform")
	 */
	class EventosPlatformController extends Controller
	{

		/**
	     * @Route("/" ,name="_eventosplatform_index")
	     * @Template()
	     */
	    public function indexAction()
	    {
	        return array();
	    }

    	/**
    	 * @Route("/eventos" ,name="_eventosplatform_eventos")
    	 * @Template()
    	 */
    	public function contentEventosAction(){
    		$request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        	$platform_id = $request->get('platform_id');
        	return array('platform_id' => $platform_id);
    	}

    	/**
    	 * @Route("/eventos_proveedor" ,name="_eventos_proveedor")
    	 * @Template()
    	 */
    	public function eventosProveedorContentAction(){
    		$request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
    		$platform_id = $request->get('platform_id');

        	return array('platform_id' => $platform_id, 'provider_E' => $this->getProviderEventList($platform_id));
    	}
    	
    	/**
    	 * @Route("/eventos_plataforma" ,name="_eventos_plataforma")
    	 * @Template()
    	 */
    	public function eventosPlataformaContentAction(){
    		$request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        	$platform_id = $request->get('platform_id');

            try{
                $em = $this->getDoctrine()->getManager('db_connection');
                $platform =  $em->getRepository('Lugh\DbConnectionBundle\Entity\Auth')->find($platform_id);         
                PlatformsManager::switchDb( $platform->getDbname() );
                $em = $this->getDoctrine()->getManager();
                
                $eventos = $em->getRepository('Lugh\WebAppBundle\Entity\Live')->findAll();

                return array('platform_id' => $platform_id, 'platform_E' => $eventos);
            }catch(\Exception $exc){
                throw new Exception('get eventos Plataforma failed: '.json_encode(array('error'=> $exc->getMessage())));
            }     
    	}
        /**
         * @Route("/eventos_proveedor_import" ,name="_eventos_proveedor_import")
         */
        public function importProviderEvent(){
            try{
                $em = $this->getDoctrine()->getManager('db_connection');
                $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
                $params = json_decode($request->get('params'),true);

                $platform_id    = $request->get('platform_id');
                $event_id       = $params['event_id'];
                $session_id     = $params['session_id'];

                $platform =  $em->getRepository('Lugh\DbConnectionBundle\Entity\Auth')->find($platform_id);         
                PlatformsManager::switchDb( $platform->getDbname() );
                $em = $this->getDoctrine()->getManager();

                $live_item = $em->getRepository('Lugh\WebAppBundle\Entity\Live')->findOneBySessionId($session_id);

                if(!$live_item){

                    $evento = $this->getProviderEvent($event_id,$platform_id);
                    $sesion = $this->getProviderSession( $session_id,$event_id, $platform_id);
                    $access = $this->getProviderSessionAccess($session_id,$event_id,$platform_id);

                    $live_item = new Live();
                    $live_item->setEventId( $event_id );
                    $live_item->setSessionId( $session_id );
                    $live_item->setSessionName( $sesion['name'] );
                    $live_item->setSessionStartDatetime( new \DateTime($sesion['starginDate'],new \DateTimeZone('UTC')) );
                    $live_item->setSessionFinishDatetime( new \DateTime($sesion['finishingDate'],new \DateTimeZone('UTC')) );
                    $live_item->setAppVersion( $sesion['appVersion'] );
                    $live_item->setSessionLiveStatus(  $sesion['liveStatus'] );
                    $live_item->setSessionOdStatus(  $sesion['odStatus'][0]['status'] );
                    $live_item->setUrl(  $access[0]['url'][0]['url'] );
                    $live_item->setEnabled( false );

                    $em->persist($live_item);
                    $em->flush();

                    return new Response('{"success": 1}');
                }
                return new Response('{"error": 0}');
            }catch(\Exception $exc){
                return new Response(json_encode(array('error'=> $exc->getMessage())));
            }
        }
        /**
         * @Route("/eventos_proveedor_toggle" ,name="_eventos_proveedor_toggle")
         */
        public function tooglePlatformEvent(){
            try{
                $em = $this->getDoctrine()->getManager('db_connection');
                $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
                $params = json_decode($request->get('params'),true);

                $platform_id    = $request->get('platform_id');
                $event_id       = $params['event_id'];
                $session_id     = $params['session_id'];

                $platform =  $em->getRepository('Lugh\DbConnectionBundle\Entity\Auth')->find($platform_id);         
                PlatformsManager::switchDb( $platform->getDbname() );
                $em = $this->getDoctrine()->getManager();
                
                $live_item = $em->getRepository('Lugh\WebAppBundle\Entity\Live')->findOneBySessionId($session_id);
                if(!$live_item){
                    return new Response('{"error": 0}');
                }
                $live_item->setEnabled(!($live_item->getEnabled()));

                $em->persist($live_item);
                $em->flush();

                return new Response('{"success": 1}');

            }catch(\Exception $exc){
                throw new Exception('Toggle event status failed: '.json_encode(array('error'=> $exc->getMessage())));
                return;
            }
        }
        /**
         * @Route("/eventos_proveedor_remove" ,name="_eventos_proveedor_remove")
         */
        public function removePlatformEvent(){
            try{
                $em = $this->getDoctrine()->getManager('db_connection');
                $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
                $params = json_decode($request->get('params'),true);

                $platform_id    = $request->get('platform_id');
                $event_id       = $params['event_id'];
                $session_id     = $params['session_id'];

                $platform =  $em->getRepository('Lugh\DbConnectionBundle\Entity\Auth')->find($platform_id);         
                PlatformsManager::switchDb( $platform->getDbname() );
                $em = $this->getDoctrine()->getManager();
                
                $live_item = $em->getRepository('Lugh\WebAppBundle\Entity\Live')->findOneBySessionId($session_id);
                if(!$live_item){
                    return new Response('{"error": 0}');
                }

                $em->remove($live_item);
                $em->flush();

                return new Response('{"success": 1}');
            }catch(\Exception $exc){
                throw new Exception('Remove event failed: '.json_encode(array('error'=> $exc->getMessage())));
                return;
            }
        }

    	/*-----------------------------------------------------------------------------*/
    	private function getHeadersProvider($platform_id)
    	{
    		try{
                $em = $this->getDoctrine()->getManager('db_connection');
        		$platform =  $em->getRepository('Lugh\DbConnectionBundle\Entity\Auth')->find($platform_id);    		
        		PlatformsManager::switchDb( $platform->getDbname() );
                $em = $this->getDoctrine()->getManager();
                $parameters = $em->getRepository('Lugh\WebAppBundle\Entity\Parametros');

                $now       = time();
                $appkey    = $parameters->findOneBy(array('key_param' => 'Av.live.appkey'))->getValueParam();
                $secretkey = $parameters->findOneBy(array('key_param' => 'Av.live.secretkey'))->getValueParam();
                $signature = hash_hmac('sha1', $appkey.$now, $secretkey);


                $headers = array(
                  'date: '.$now,
                  'appkey: '.$appkey,
                  'signature: '.$signature,
                  'signatureversion: v1'
                  );

                return $headers;

            }catch(\Exception $exc){
                throw new Exception('get provider header failed: '.json_encode(array('error'=> $exc->getMessage())));
                return;
            }
    	}

    	private function getProviderEventList($platform_id){
    		try{

                $url = 'http://api.webcasting-studio.net/events/list';

                $headers = $this->getHeadersProvider($platform_id);

                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_HEADER, 0);//Si se pone a 1 muestra las cabeceras de la respuesta
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $output = curl_exec($ch);
                curl_close($ch);

                $eventos = json_decode($output,true);
                if(strlen($output) == 1111){
                    echo('<h2 style="margin:10px;color:red;">Connection Error!</h2>');
                }
                
                if($eventos){
                    foreach($eventos as $key => $evento){
                        $eventos[$key]['sessions'] = $this->getProviderEventSessionList($evento['id'], $platform_id);
                    }

                    return $eventos; 
                }
                return array();
                
    	
    		}catch(\Exception $exc){
    			throw new Exception('get platform events failed: '.json_encode(array('error'=> $exc->getMessage())));
                return;
    		}
    	}

        private function getProviderEventSessionList( $event_id, $platform_id){
            try{
                $url = 'http://api.webcasting-studio.net/events/'.$event_id.'/sessions/list';

                $headers = $this->getHeadersProvider($platform_id);
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $output = curl_exec($ch);
                curl_close($ch);

                $sesiones = json_decode($output,true);
                if($sesiones){
                    foreach($sesiones as $key => $session){
                        $sesiones[$key]['access'] = $this->getProviderSessionAccess($session['id'],$event_id, $platform_id);
                    }
                    return $sesiones;
                }
                return array();
                

            }catch(\Exception $exc){
                throw new Exception('get event sessions failed: '.json_encode(array('error'=> $exc->getMessage())));
                return;
            }
        }

        private function getProviderEvent($event_id,$platform_id){
            try{
                $url = 'http://api.webcasting-studio.net/events/'.$event_id;

                $headers = $this->getHeadersProvider($platform_id);
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $output = curl_exec($ch);
                curl_close($ch);

                $event = json_decode($output,true);
                
                return $event;

            }catch(\Exception $exc){
                throw new Exception('get event failed: '.json_encode(array('error'=> $exc->getMessage())));
                return;
            }
        }

        private function getProviderSession($session_id,$event_id,$platform_id){
            try{
                $url = 'http://api.webcasting-studio.net/events/'.$event_id.'/sessions/'.$session_id;

                $headers = $this->getHeadersProvider($platform_id);
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $output = curl_exec($ch);
                curl_close($ch);

                $session = json_decode($output,true);
               
                return $session;

            }catch(\Exception $exc){
                throw new Exception('get session failed: '.json_encode(array('error'=> $exc->getMessage())));
                return;
            }
        }

        private function getProviderSessionAccess($session_id,$event_id,$platform_id){
            try{
                $url = 'http://api.webcasting-studio.net/events/'.$event_id.'/access/list?sessionId='.$session_id.'&ssl=false&domain=header.webcasting-studio.net';

                $headers = $this->getHeadersProvider($platform_id);
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $output = curl_exec($ch);
                curl_close($ch);

                $acceso = json_decode($output,true);

                return $acceso;

            }catch(\Exception $exc){
                throw new Exception('get session access failed: '.json_encode(array('error'=> $exc->getMessage())));
                return;
            }
        }

        

        

    	
    	



	}