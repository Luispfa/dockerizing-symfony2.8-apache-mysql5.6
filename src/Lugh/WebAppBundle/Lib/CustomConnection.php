<?php

namespace Lugh\WebAppBundle\Lib;
use Symfony\Component\HttpFoundation\Session\Session;
use \Doctrine\DBAL\Driver as Driver;
use \Doctrine\DBAL\Configuration as Configuration;
use \Doctrine\Common\EventManager as EventManager;
use \Symfony\Component\HttpFoundation\Request as Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\DBAL\Events;
use Doctrine\DBAL\Event\ConnectionEventArgs;



class CustomConnection extends \Doctrine\DBAL\Connection {

    const SESSION_ACTIVE_DYNAMIC_CONN = 'active_dynamic_conn';

    /**
     * @var Session
     */
    private $session;

    /**
     * @var bool
     */
    private $_isConnected = false;
    
    
    /**
     * Initializes a new instance of the Connection class.
     *
     * @param array                              $params       The connection parameters.
     * @param \Doctrine\DBAL\Driver              $driver       The driver to use.
     * @param \Doctrine\DBAL\Configuration|null  $config       The configuration, optional.
     * @param \Doctrine\Common\EventManager|null $eventManager The event manager, optional.
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    
    public function __construct(array $params, Driver $driver, Configuration $config = null, EventManager $eventManager = null)
    {
        global $kernel;
        if ('AppCache' == get_class($kernel)) {
            $kernel = $kernel->getKernel();
        }
        $request = Request::createFromGlobals();
        //@TODO eliminar if
        if (
                //$kernel->getContainer()->get('kernel')->getEnvironment() != 'dev' && 
                $request->getHttpHost() != '127.0.0.1' && 
                $request->getHttpHost() != 'localhost' &&
                $request->getHttpHost() != ':'
                )
        {
            $em = $kernel->getContainer()->get('doctrine')->getManager('db_connection');
            $query = $em->createQuery('SELECT a FROM Lugh\DbConnectionBundle\Entity\Auth a WHERE a.host = :host and a.active=1');
            $query->setParameter('host', $request->getHttpHost());

            $record = $query->getOneOrNullResult();
            $params['dbname'] = 'dbnone';
            if ($record != null)
            {
                $params['dbname'] = 'lugh_' . $record->getDbname();
            }
            else if (!$kernel->getContainer()->get('lugh.route.template')->isAdminAddr($request->getHttpHost())){
                header('HTTP/1.0 404 not found');
                die();
            }
            
        }
        
        parent::__construct($params, $driver, $config, $eventManager);
    }
    
    /**
    * @param Session $sess
    */
   public function setSession(Session $sess)
   {
       $this->session = $sess;
   }

   public function forceSwitch($dbName, $dbUser, $dbPassword)
   {
       if ($this->session->has(self::SESSION_ACTIVE_DYNAMIC_CONN)) {
           $current = $this->session->get(self::SESSION_ACTIVE_DYNAMIC_CONN);
           if ($current[0] === $dbName) {
               return;
           }
       }
       
       $this->session->set(self::SESSION_ACTIVE_DYNAMIC_CONN, [
           $dbName,
           $dbUser,
           $dbPassword
       ]);

       if ($this->isConnected()) {
           $this->close();
       }
   }
   
   /**
    * {@inheritDoc}
    */
   public function connect()
   {
        if ($this->_isConnected) return false;
       
        $params = $this->getParams();
        if (is_object($this->session) && $this->session->has(self::SESSION_ACTIVE_DYNAMIC_CONN))
        {
            $realParams = $this->session->get(self::SESSION_ACTIVE_DYNAMIC_CONN);
            $params['dbname'] = $realParams[0];
            $params['user'] = $realParams[1];
            $params['password'] = $realParams[2];
        }
       
        $driverOptions = isset($params['driverOptions']) ?
                $params['driverOptions'] : array();
        $user = isset($params['user']) ? $params['user'] : null;
        $password = isset($params['password']) ?
                $params['password'] : null;



        $this->_conn = $this->_driver->connect($params, $user, $password, $driverOptions);
        $this->_isConnected = true;

        if ($this->_eventManager->hasListeners(Events::postConnect)) {
            $eventArgs = new Event\ConnectionEventArgs($this);
            $this->_eventManager->dispatchEvent(Events::postConnect, $eventArgs);
        }

        return true;
       
   }
   
   /**
    * {@inheritDoc}
    */
   public function isConnected()
   {
       return $this->_isConnected;
   }
   
   /**
    * {@inheritDoc}
    */
   public function close()
   {
       if ($this->isConnected()) {
           parent::close();
           $this->_isConnected = false;
       }
   }
    
}