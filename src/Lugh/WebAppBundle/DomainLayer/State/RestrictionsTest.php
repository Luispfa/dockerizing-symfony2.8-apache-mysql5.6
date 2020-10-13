<?php

namespace Lugh\WebAppBundle\DomainLayer\State;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Restrictions
 *
 * @author a.navarro
 */
class RestrictionsTest {
    
    static function getContainer()
    {
        global $kernel;
        if ('AppCache' == get_class($kernel)) {
             $kernel = $kernel->getKernel();
        }
        return $kernel->getContainer();
    }
    
    static function selfAdhesion($item, $adhesion)
    {
        return $adhesion->getAccionista() == $item->getAutor();
    }
    
    static function hasUserPermitedChangeState()
    {
        $user = self::getContainer()->get('security.context')->getToken()->getUser();
        foreach($user->getRoles() as $role){
            if($role == "ROLE_ADMIN" || $role == "ROLE_SUPER_ADMIN"){
                return true;
            }
        }
        return false;
    }
    
    static function inTime($item, $state)
    {
        /*if (true !== $timeState = self::getContainer()->get('lugh.parameters')->getByKey($item::nameClass . '.time.' . $state, true)) 
        {
            if (strtotime($timeState) < time()) 
            {
                return false;
            }
        }
        if (true != $timeglobal = self::getContainer()->get('lugh.parameters')->getByKey($item::nameClass . '.time.global', true)) 
        {
            if (strtotime($timeglobal) < time()) 
            {
                return false;
            }
        }*/
        return true;
    }

}

?>
