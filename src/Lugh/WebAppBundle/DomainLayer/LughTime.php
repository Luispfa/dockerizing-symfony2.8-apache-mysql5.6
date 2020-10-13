<?php

namespace Lugh\WebAppBundle\DomainLayer;
use \Symfony\Component\HttpFoundation\Request as Request;
use Lugh\WebAppBundle\DomainLayer\LughService;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of LughTime
 *
 * @author a.navarro
 */
class LughTime extends LughService {

    public function inTime($record = null)
    {
        if ($record == null)
        {
            $record = $this->get('lugh.parameters')->getByKey('Platform.time.activate');
        }
        

        if ($record == null)
        {
            return false;
        }
        
        $param = json_decode($record, true);
        
        if (isset($param['from'])   &&  strtotime($param['from'])   >   time() ||
                isset($param['to'])     &&  strtotime($param['to'])     <   time()) 
        {
            return false;
        }
        elseif (!isset($param['to']) && isset($param['from']) &&  strtotime($param['from'])   >   time())
        {
            return false;
        }
        elseif (!isset($param['from']) && isset($param['to'])   &&  strtotime($param['to'])     <   time())
        {
            return false;
        }
        
        return true;
    }
    
    public function appInTime()
    {
        $request = Request::createFromGlobals();
        $host = $request->getHttpHost();
        
         if (
                !$this->get('lugh.route.template')->isAdminAddr($host) &&
                $host != '127.0.0.1' && 
                $host != 'localhost' 
            )
        {
            $em = $this->get('doctrine')->getManager('db_connection');
            $query = $em->createQuery('SELECT a FROM Lugh\DbConnectionBundle\Entity\Auth a WHERE a.host = :host and a.active=1');
            $query->setParameter('host', $host);
            $record = $query->getOneOrNullResult();
            
            if ($record != null)
            {
                return array(
                    'voto'      =>  $record->getVoto()      != null ? $record->getVoto()    : false,
                    'foro'      =>  $record->getForo()      != null ? $record->getForo()    : false,
                    'derecho'   =>  $record->getDerecho()   != null ? $record->getDerecho() : false,
                    'av'        =>  $record->getAv()        != null ? $record->getAv()      : false
                );
            }
            else {
                return array(
                    'voto'      =>  false,
                    'foro'      =>  false,
                    'derecho'   =>  false,
                    'av'        =>  false
                );
            }      
        }
        return array(
            'voto'      =>  true,
            'foro'      =>  true,
            'derecho'   =>  true,
            'av'        =>  true
        );
        
    }
}

?>
