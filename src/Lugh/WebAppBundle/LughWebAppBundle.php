<?php

namespace Lugh\WebAppBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Lugh\WebAppBundle\DependencyInjection\CompilerPass\ConnectionCompilerPass;

class LughWebAppBundle extends Bundle
{
    /*public function __construct() {
        global $kernel;

        if ('AppCache' == get_class($kernel)) {
            $kernel = $kernel->getKernel();
        }
        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
        $session = $request->getSession();
        
        if ($session->get('dbname', null) == null)
        {
            die('bbb');
            $session->set('dbname', 'nodb');
            $em = $kernel->getContainer()->get('doctrine')->getManager('db_connection');
            $query = $em->createQuery('SELECT a FROM Lugh\DbConnectionBundle\Entity\Auth a WHERE a.host = :host');
            $query->setParameter('host', $request->getHttpHost());

            $record = $query->getOneOrNullResult();

            if ($record == null)
            {
                $session->set('dbname', $record->getDbname());
            }
        }
        
    }*/
    public function getParent()
    {
        return 'FOSUserBundle';
    }
    
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new ConnectionCompilerPass());
    }
}
