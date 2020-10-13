<?php

namespace Lugh\DbConnectionBundle\Service;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use FOS\UserBundle\Util\CanonicalizerInterface;
use Doctrine\Bundle\DoctrineBundle\Registry as RegistryInterface;
use FOS\UserBundle\Entity\UserManager as BaseUserManager;


class UserManager extends BaseUserManager {

    /**
    * Constructor.
    *
    * @param EncoderFactoryInterface $encoderFactory
    * @param CanonicalizerInterface  $usernameCanonicalizer
    * @param CanonicalizerInterface  $emailCanonicalizer
    * @param RegistryInterface       $doctrine
    * @param string                  $connName
    * @param string                  $class
    * @param string                  $custom_class
    */
   public function __construct(EncoderFactoryInterface $encoderFactory, CanonicalizerInterface $usernameCanonicalizer,
                               CanonicalizerInterface $emailCanonicalizer, RegistryInterface $doctrine, $connName, $class, $custom_class)
   {
       global $kernel;
       if ('AppCache' == get_class($kernel)) {
            $kernel = $kernel->getKernel();
       }
       $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
       
       $om = $doctrine->getManager();       
       if ($kernel->getContainer()->get('lugh.route.template')->isAdminAddr($request->getHttpHost()))
       {
           $om = $doctrine->getManager($connName);
           $class = $custom_class;
       }
       
       parent::__construct($encoderFactory, $usernameCanonicalizer, $emailCanonicalizer, $om, $class);
   }

   /**
    * Just for test
    * @return EntityManager
    */
   public function getOM()
   {
       return $this->objectManager;
   }
}
