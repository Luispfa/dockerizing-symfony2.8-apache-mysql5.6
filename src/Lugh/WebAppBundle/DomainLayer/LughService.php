<?php
namespace Lugh\WebAppBundle\DomainLayer;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface as container;

abstract class LughService 
{

    protected $container;
    
    public function __construct(container $container = null) {
        $this->container = $container;
    }

    protected function get($id)
    {
        return $this->container->get($id);
    }
    
    protected function getCurrentRequest()
    {
        return $this->container->get('request_stack')->getCurrentRequest();
    }
    
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
    
}