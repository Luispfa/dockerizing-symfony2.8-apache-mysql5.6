<?php

namespace Lugh\DbConnectionBundle\Lib;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Lugh\WebAppBundle\Command\CreateDatabaseDoctrineCommand;
use Lugh\WebAppBundle\Command\UpdateSchemaDoctrineCommand;
use Lugh\WebAppBundle\Command\LoadDataFixturesDoctrineCommand;
use Lugh\WebAppBundle\Command\CreateUserCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\ConsoleOutput;

class AddPlatform {
    
    
    public static function getContainer()
    {
        global $kernel;
        if ('AppCache' == get_class($kernel)) {
             $kernel = $kernel->getKernel();
        }
        return $kernel->getContainer();
    }
    
    
    public static function addPlataformCommand($platform) {
        self::createDatabase($platform['dbname']);
        self::schemaUpdate($platform['dbname']);
        self::loadFixtures($platform['dbname']);
        self::createSuperAdmin($platform['dbname']);
        return true;
    }
    
    
    private static function createDatabase($platform)
    {        
        global $kernel;
        if ('AppCache' == get_class($kernel)) {
             $kernel = $kernel->getKernel();
        }
        $application = new Application($kernel);
        $application->setAutoExit(false);
        $command = 'doctrine:database:customname:create  ' . $platform . ' --connection=db_connection --env=prod';
        $input = new StringInput($command);
        $output = new ConsoleOutput(); 
        try {
            $ret = $application->run($input, $output);
        } catch (Exception $exc) {
            throw new Exception($exc->getMessage());
        }
        return $ret;
    }
    
    private static function schemaUpdate($platform)
    {
        global $kernel;
        if ('AppCache' == get_class($kernel)) {
             $kernel = $kernel->getKernel();
        }
        $application = new Application($kernel);
        $application->setAutoExit(false);
        $command = 'doctrine:schema:customname:update ' . $platform . ' --force --env=prod';
        $input = new StringInput($command);
        $output = new ConsoleOutput();
        if ($ret = $application->run($input, $output) != 0) {
            throw new Exception("Command doctrine:schema:customname:update no run correctly");
        }
        return $ret;
    }
    
    private static function loadFixtures($platform)
    {
        global $kernel;
        if ('AppCache' == get_class($kernel)) {
             $kernel = $kernel->getKernel();
        }
        $application = new Application($kernel);
        $application->setAutoExit(false);
        $command = 'doctrine:fixtures:customname:load ' . $platform . ' --append --env=prod';
        $input = new StringInput($command);
        $output = new ConsoleOutput();
        if ($ret = $application->run($input, $output) != 0) {
            throw new Exception("Command doctrine:fixtures:customname:load no run correctly");
        }
        return $ret;
    }
    
    private static function createSuperAdmin($platform)
    {
        exec('php ../app/console fos:user:customname:create ' . 
                'dev_admin ' .
                'lugh_admin+' . $platform. '@juntadeaccionistas.es ' .
                'OverLord+ad01 ' .
                $platform . 
                ' --super-admin ' .
                '--env=prod',
                $output, $ret);
        
        if ($ret != 0) {
            throw new Exception("Command fos:user:customname:create no run correctly");
        }
        return $ret;
    }
    
}
