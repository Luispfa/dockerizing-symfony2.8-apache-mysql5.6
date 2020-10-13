<?php

namespace Lugh\WebAppBundle\Annotations\Driver;

use Doctrine\Common\Annotations\Reader;//This thing read annotations
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;//Use essential kernel component
use Lugh\WebAppBundle\Annotations\Permissions;
use Symfony\Component\HttpFoundation\Response;// For example I will throw 403, if access denied
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;




class AnnotationDriver {
    
    private $reader;
    
    public function __construct($reader) 
    {
        $this->reader = $reader;//get annotations reader
    }
    
    /**
    * This event will fire during any controller call
    */
    public function onKernelController(FilterControllerEvent $event)
    {
 
        if (!is_array($controller = $event->getController())) { //return if no controller
            return;
        }
 
        $object = new \ReflectionObject($controller[0]);// get controller
        $method = $object->getMethod($controller[1]);// get method
        
        foreach ($this->reader->getClassAnnotations($object) as $configurationClass) {
            $this->accessDenied($configurationClass, $controller);
        }
        
        foreach ($this->reader->getMethodAnnotations($method) as $configuration) { //Start of annotations reading
            $this->accessDenied($configuration, $controller);
         }
    }
    
    private function accessDenied($configuration, $controller)
    {
        if(isset($configuration->perm))
        {//Found our annotation
            if (is_array($configuration->perm))
            {
                foreach ($configuration->perm as $permission) 
                {
                    if ($controller[0]->get('security.context')->isGranted($permission))
                    {
                        return true;
                    }
                }
                throw new AccessDeniedHttpException();

            }
            else
            {
                if (!$controller[0]->get('security.context')->isGranted($configuration->perm))
                {
                    throw new AccessDeniedHttpException();
                }
            }
         }
    }
}

?>
