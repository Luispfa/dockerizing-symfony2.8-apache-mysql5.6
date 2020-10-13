<?php
namespace Lugh\WebAppBundle\Audit;

use Monolog\Logger;
use Monolog\Handler\SocketHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\AbstractProcessingHandler;

class LughHandler extends AbstractProcessingHandler
{
    protected $container;
    
    protected function write(array $record) {
        $dir_file = $this->container->getParameter('kernel.logs_dir') . DIRECTORY_SEPARATOR . $this->container->get('request')->server->get('HTTP_HOST', 'localhost') . '_' . date('Ymd') . '.log';
        if ($this->isValidMessage($record['message']))
        {
            $resource = $this->getResource($dir_file);
            fwrite($resource , (string) $this->formatRecord($record));
            $this->closeResource($resource);
        }
    }
    
    public function setContainer($container)
    {
        $this->container = $container;
    }
    
    private function getResource($path)
    {
        return fopen($path, 'a');
    }
    private function closeResource($handle)
    {
        return fclose($handle);
    }
    private function isValidMessage($message)
    {
        $valid_messages = array(
            '"_format": "json"',
            'indexAction',
            'viewsAction',
            'Authentication request',
            'has been authenticated successfully',
            'fos_user_security_check',
            'fos_user_security_logout',
            'User',
            'Username'
            
        );
        foreach ($valid_messages as $value) {
            if (strpos($message, $value) !== false) {
                return true;
            }
        }
        return false;
    }
    
    private function formatRecord($record)
    {
        $remote_addr = $this->container->get('request')->server->get('REMOTE_ADDR', 'unknow');
        $date = $record['datetime']->format('Y-m-d H:i:s');;
        $host = $record['extra']['host'];
        $message = $record['message'];
        return '[' . $date . '] ' . '[' . $host . '] ' . '[' . $remote_addr . '] : ' . $message . chr(10);
        
    }
    
    private function getUser()
    {
        //die(var_dump($this->container->get('security.context')->isGranted('ROLE_USER_FULL')));
        if (!$this->container->has('security.context')) {
            throw new \LogicException('The SecurityBundle is not registered in your application.');
        }

        if (null === $token = $this->container->get('security.context')->getToken()) {
            return;
        }

        if (!is_object($user = $token->getUser())) {
            return;
        }

        return $user;
    }

}

