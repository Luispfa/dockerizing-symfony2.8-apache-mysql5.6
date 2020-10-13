<?php

namespace Lugh\DbConnectionBundle\Lib;

class PlatformsManager {
    
    static function getContainer($connection = 'db_connection')
    {
        global $kernel;
        if ('AppCache' == get_class($kernel)) {
            $kernel = $kernel->getKernel();
        }
        return $kernel->getContainer();
    }
    
    static function getManager($connection = 'db_connection')
    {
        return self::getContainer()->get('doctrine')->getManager($connection);
    }
    
    static public function switchDb($dbname)
    {
        $name = 'lugh_' . $dbname;

        $em = self::getManager('default');
        $params = $em->getConnection()->getParams();
        self::getContainer()->get('doctrine.dbal.default_connection')->forceSwitch($name, $params['user'], $params['password']);
    }

    static public function setVotoActive($platform_id, $active)
    {
        $em = self::getManager();
        try {
            $auth = $em->getRepository('Lugh\DbConnectionBundle\Entity\Auth' )->find($platform_id);
            $auth->setVoto($active);
            $em->persist($auth);
            $em->flush();
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
            return false;
        }
        
        return true;
    }
    
    static public function setForoActive($platform_id, $active)
    {
        $em = self::getManager();
        try {
            $auth = $em->getRepository('Lugh\DbConnectionBundle\Entity\Auth' )->find($platform_id);
            $auth->setForo($active);
            $em->persist($auth);
            $em->flush();
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
            return false;
        }
        
        return true;
    }
    
    static public function setDerechoActive($platform_id, $active)
    {
        $em = self::getManager();
        try {
            $auth = $em->getRepository('Lugh\DbConnectionBundle\Entity\Auth' )->find($platform_id);
            $auth->setDerecho($active);
            $em->persist($auth);
            $em->flush();
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
            return false;
        }
        
        return true;
    }
    
    static public function setAVActive($platform_id, $active)
    {
        $em = self::getManager();
        try {
            $auth = $em->getRepository('Lugh\DbConnectionBundle\Entity\Auth' )->find($platform_id);
            $auth->setAv($active);
            $em->persist($auth);
            $em->flush();
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
            return false;
        }
        
        return true;
    }
    
    static public function setActiveActive($platform_id, $active)
    {
        $em = self::getManager();
        try {
            $auth = $em->getRepository('Lugh\DbConnectionBundle\Entity\Auth' )->find($platform_id);
            $auth->setActive($active);
            $em->persist($auth);
            $em->flush();
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
            return false;
        }
        
        return true;
    }
    
    static public function setProductionActive($platform_id, $active)
    {
        $em = self::getManager();
        try {
            $auth = $em->getRepository('Lugh\DbConnectionBundle\Entity\Auth' )->find($platform_id);
            $auth->setOnProductionDates($active);
            $em->persist($auth);
            $em->flush();
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
            return false;
        }
        
        return true;
    }
    
    static public function setHostParam($platform_id, $param)
    {
        $em = self::getManager();
        try {
            $auth = $em->getRepository('Lugh\DbConnectionBundle\Entity\Auth' )->find($platform_id);
            $auth->setHost($param);
            $em->persist($auth);
            $em->flush();
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
            return false;
        }
        
        return true;
    }
    
    static public function setDbNameParam($platform_id, $param)
    {
        $em = self::getManager();
        try {
            $auth = $em->getRepository('Lugh\DbConnectionBundle\Entity\Auth' )->find($platform_id);
            self::changeDatabaseName($param, $auth->getDbname());
            $auth->setDbname($param);
            $em->persist($auth);
            $em->flush();
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
            return false;
        }
        
        return true;
    }
    
    static public function setStyleParam($platform_id, $param)
    {
        $em = self::getManager();
        try {
            $auth = $em->getRepository('Lugh\DbConnectionBundle\Entity\Auth' )->find($platform_id);
            $template = $em->getRepository('Lugh\DbConnectionBundle\Entity\Template' )->find($param);
            $auth->setTemplate($template);
            $em->persist($auth);
            $em->flush();
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
            return false;
        }
        
        return true;
    }
    
    static public function addPlatform($params)
    {
        $em = self::getManager();
        try {
            $auth = new \Lugh\DbConnectionBundle\Entity\Auth();
            $auth->setHost(self::normalizeHost($params['appendedInputButton_host']));
            $auth->setDbname(self::normalizeDbName($params['appendedInputButton_dbname']));
            $template = $em->getRepository('Lugh\DbConnectionBundle\Entity\Template' )->find($params['select_style']);
            $auth->setTemplate($template);
            $auth->setVoto($params['myonoffswitch_voto']);
            $auth->setForo($params['myonoffswitch_foro']);
            $auth->setDerecho($params['myonoffswitch_derecho']);
            $auth->setAv($params['myonoffswitch_av']);
            $auth->setActive($params['myonoffswitch_active']);
            $auth->setOnProductionDates(0);
            $em->persist($auth);
            $em->flush();
            
            $platform = array(
                'dbname'    =>  self::normalizeDbName($params['appendedInputButton_dbname'])
            );
            $result = AddPlatform::addPlataformCommand($platform);
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
            return false;
        }
        return $result;
    }
    
    
    static private function changeDatabaseName($name, $nameOld)
    {
        $em = self::getManager();
        $name = 'lugh_' . $name;
        $nameOld = 'lugh_' . $nameOld;
        
        $sql = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '{$nameOld}'";
        $doctrineobject = $em->getConnection()->executeQuery($sql)->fetchAll(\PDO::FETCH_ASSOC);
        
        if (count($doctrineobject) > 0 && $doctrineobject[0]['SCHEMA_NAME'] == $nameOld)
        {
            $sql = "SHOW TABLE STATUS FROM `{$nameOld}`";
            $doctrineobjecttable = $em->getConnection()->executeQuery($sql)->fetchAll(\PDO::FETCH_ASSOC);

            $sql = "CREATE DATABASE {$name}";
            $doctrineobject = $em->getConnection()->executeQuery($sql);

            $sql = "RENAME TABLE ";
            foreach ($doctrineobjecttable as $table) {
                $sql .="`{$nameOld}`.`{$table['Name']}` TO `{$name}`.`{$table['Name']}`, ";
            }
            $sql = substr($sql, 0, -2);
            $doctrineobject = $em->getConnection()->executeQuery($sql);

            $sql = "DROP DATABASE `{$nameOld}`";
            $doctrineobject = $em->getConnection()->executeQuery($sql);
        }
    }
    
    static private function normalizeDbName($dbname)
    {
        $dbname = str_replace('.','_',$dbname);
        $dbname = str_replace('-','_',$dbname);
        $dbname = str_replace('https://','',$dbname);
        $dbname = str_replace('http://','',$dbname);
        $dbname = str_replace('/','',$dbname);
        $dbname = str_replace('\\','',$dbname);
        
        return trim($dbname);
    }
    
    static private function normalizeHost($hostname)
    {
        $hostname = str_replace('https://','',$hostname);
        $hostname = str_replace('http://','',$hostname);
        $hostname = str_replace('/','',$hostname);
        $hostname = str_replace('\\','',$hostname);
        
        return trim($hostname);
    }
    
    
}