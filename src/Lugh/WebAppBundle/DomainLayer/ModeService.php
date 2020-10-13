<?php

namespace Lugh\WebAppBundle\DomainLayer;
use Lugh\WebAppBundle\DomainLayer\LughService;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Parameters
 *
 * @author a.navarro
 */
class ModeService extends LughService {
    
    
    protected $mode = 'prod';
    
    
    public function setTest()
    {
        $this->mode = 'test';
    }
    public function setProd()
    {
        $this->mode = 'prod';
    }
    public function getMode()
    {
        return $this->mode;
    }
}

?>
