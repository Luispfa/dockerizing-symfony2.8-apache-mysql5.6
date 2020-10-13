<?php
namespace Lugh\WebAppBundle\DomainLayer\Director;
use Lugh\WebAppBundle\DomainLayer\Builder\Builder;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of LughDirector
 *
 * @author a.navarro
 */
abstract class LughDirector {
    
    protected $builder;
    
    public function setBuilder(Builder $builder)
    {
        $this->builder = $builder;
    }
    
    public function getClass()
    {
        return get_class($this->builder);
    }

}

?>
