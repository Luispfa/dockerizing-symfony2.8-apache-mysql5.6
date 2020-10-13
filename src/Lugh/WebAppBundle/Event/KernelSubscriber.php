<?php

namespace Lugh\WebAppBundle\Event;

use \Symfony\Component\EventDispatcher\EventSubscriberInterface;
use \Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;

class KernelSubscriber implements EventSubscriberInterface
{
    static public function getSubscribedEvents()
    {
        return array(
            'kernel.response' => array('onKernelResponsePre')
        );
    }
    
    private function getKernel()
    {
        global $kernel;
        if ('AppCache' == get_class($kernel)) {
             $kernel = $kernel->getKernel();
        }
        return $kernel;
    }
    public function onKernelResponsePre(FilterResponseEvent $event)
    {
        $host = $event->getRequest()->getHttpHost();
        $container = $this->getKernel()->getContainer();
        $session = $event->getRequest()->getSession();
        if (
                (strpos($event->getRequest()->get('_controller'), '::loginAction') != false ||
                strpos($event->getRequest()->get('_controller'), '::indexAction') != false) &&
                !$container->get('lugh.route.template')->isAdminAddr($host) &&
                $host != '127.0.0.1' && 
                $host != 'localhost' 
            )
        {
            $em = $container->get('doctrine')->getManager('db_connection');
            $query = $em->createQuery('SELECT a FROM Lugh\DbConnectionBundle\Entity\Auth a WHERE a.host = :host and a.active=1');
            $query->setParameter('host', $host);
            $record = $query->getOneOrNullResult();
            if ($record == null)
            {
                $response = new Response();
                $event->setResponse($response->setStatusCode(500));
            }
            else
            {
                $this->setHost($session, $record, $host);
                $this->setApps($session, $record);
            }
        }
        
        if ((strpos($event->getRequest()->get('_controller'), '::loginAction') != false ||
                strpos($event->getRequest()->get('_controller'), '::indexAction') != false) &&
                !$container->get('lugh.route.template')->isAdminAddr($host))
        {
            $this->setParams($session);
        }
    }
    
    private function setHost($session, $record, $host)
    {
        $session->set('lugh_' . $host, trim($record->getTemplate()->getPath()));
    }

    private function setApps($session, $record)
    {
        $apps = array(
            'voto'      =>  $record->getVoto(),
            'foro'      =>  $record->getForo(),
            'derecho'   =>  $record->getDerecho(),
            'av'        =>  $record->getAv()
        );
        $apps['platforms'] = $apps;
        $session->set('apprequest', $apps);
        
    }
    private function setParams($session)
    {
        $container = $this->getKernel()->getContainer();
        $record = $container->get('lugh.parameters')->getParams();
        
        $params = $this->modParams($record);
        $session->set('paramrequest', $params);
        
        $paramsapp = $this->modParamsApps($session, $record);
        $session->set('paramsapp', $paramsapp);
    }
    
    private function modParams($params = array())
    {
        $parameters = array();
        foreach ($params as $param) {
            if ($param['key_param'] == 'Platform.time.activate')
            {
                $parameters[] = $this->setParam($param, $this->getKernel()->getContainer()->get('lugh.Time')->inTime());
            }
            else if (strpos($param['key_param'],'.time.activate') !== false)
            {
                
            }
            else
            {
                $parameters[] = $param;
            }
        }
        return $parameters;
    }
    
    private function modParamsApps($session, $params = array())
    {
        $parameters = $session->get('apprequest', array());
        foreach ($params as $param) {
            if ($param['key_param'] == 'Platform.time.activate')
            {
                
            }
            else if (strpos($param['key_param'],'.time.activate') !== false)
            {
                $key = lcfirst(substr($param['key_param'], 0,strpos($param['key_param'],'.time.activate')));
                $parameters[$key] = $this->getKernel()->getContainer()->get('lugh.Time')->inTime($param['value_param']);
            }

        }
        return $parameters;
    }
    
    private function setParam($param, $value)
    {
        return array(
            'id'            =>  $param['id'],
            'key_param'     =>  $param['key_param'],
            'value_param'   =>  $value,
            'observaciones' =>  $param['observaciones']
        );
    }

}
