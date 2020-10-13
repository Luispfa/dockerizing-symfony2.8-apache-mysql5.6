<?php

namespace Lugh\WebAppBundle\Lib\External;

class StoreManager {

    static public function getStorages()
    {
        return json_decode(self::getContainer()->get('lugh.parameters')->getByKey('Directory.fileupload.storages'),true);
    }
    
    
    
    static public function StoreFile($fileObject,$userId)
    {
        $internalName = $userId . '.'.uniqid(php_uname('n').'.', true) . '.' .  time().'.bin';
        $storages = self::getStorages();
        $destinationLocation = $storages['w'] . '/adjuntos/' . $userId[0] . '/' . $userId[1] . '/' . $userId[2] . '/' . $userId . '/';
        if(!is_dir($destinationLocation))
        {
            mkdir($destinationLocation, 0777, true);
        }
        
        $fileObject->move($destinationLocation, $internalName);
        return $internalName;
    }
    static public function StoreMail($filename, $content, $itemId)
    {
        //@TODO Exception no datos en BBDD
        $storages = self::getStorages();
        $destinationLocation = $storages['w'] . '/mail/' . $itemId[0] . '/' . $itemId[1] . '/' . $itemId[2] . '/' . $itemId . '/';
        $destinationfile = $destinationLocation . $itemId . '.' . $filename;
        if(!is_dir($destinationLocation))
        {
            mkdir($destinationLocation, 0777, true);
        }
        
        file_put_contents($destinationfile, $content);
        return $destinationfile;
    }
    
    static public function StoreSms($filename, $content, $itemId, $result)
    {
        //@TODO Exception no datos en BBDD
        $storages = self::getStorages();
        $destinationLocation = $storages['w'] . '/sms/' . $itemId[0] . '/' . $itemId[1] . '/' . $itemId[2] . '/' . $itemId . '/';
        $destinationfile = $destinationLocation . $itemId . '.' . $filename;
        if(!is_dir($destinationLocation))
        {
            mkdir($destinationLocation, 0777, true);
        }
        
        file_put_contents($destinationfile.'.json', $content);
        file_put_contents($destinationfile.'.xml', $result);
        return $destinationfile;
    }

    static public function RetrieveFile($userId, $internalName)
    {
        $storages = self::getStorages();
        foreach($storages['r'] as $storage)
        {
            $destinationFile = $storage . '/adjuntos/' . $userId[0] . '/' . $userId[1] . '/' . $userId[2] . '/' .$userId . '/' . $internalName;
            if (file_exists($destinationFile))
            {
                return file_get_contents($destinationFile);
            }
        }
        
        return false;
    }
    
    static public function RetrieveFileNames($itemId, $internalName)
    {
        $storages = self::getStorages();
        foreach($storages['r'] as $storage)
        {
            $destinationFile = $storage . '/adjuntos/' . $itemId[0] . '/' . $itemId[1] . '/' . $itemId[2] . '/' . $itemId . '/'. $internalName;
            if (file_exists($destinationFile))
            {
                return $destinationFile;
            }
        }
        
        return false;
    }   
    
    static public function StoreGeneric($internalName, $content)
    {
        //@TODO Exception no datos en BBDD
        
        $md5 = md5($internalName);
        
        $storages = self::getStorages();
        $destinationLocation = $storages['w'] . '/generic/' . $md5[0] . '/' . $md5[1] . '/' . $md5[2] . '/';
        $destinationfile = $destinationLocation . $internalName;
        if(!is_dir($destinationLocation))
        {
            mkdir($destinationLocation, 0777, true);
        }
        
        file_put_contents($destinationfile, $content);
        return $destinationfile;
    }
    static public function StoreGenericFile($internalName,$fileObject)
    {
        $md5 = md5($internalName);
        $storages = self::getStorages();
        $destinationLocation = self::getPath() . '/' . $storages['w'] . '/generic/' . $md5[0] . '/' . $md5[1] . '/' . $md5[2] . '/';
        $destinationfile = $destinationLocation . $internalName;
        if(!is_dir($destinationLocation))
        {
            mkdir($destinationLocation, 0777, true);
        }
        $fileObject->move($destinationLocation, $internalName);
        return $destinationfile;
    }
    static public function StoreMovimientosFile($filename, $content)
    {
        $storages = self::getStorages();
        $path = self::getPath() . '/' . $storages['w'] . '/movimientos/';
        if (!file_exists($path)) {
            mkdir($path);
        }
        $destinationfile = file_put_contents($path . $filename, $content);
        return $destinationfile;
    }
    static public function RetriveMovimientosFile($filename)
    {
        $storages = self::getStorages();
        foreach($storages['r'] as $storage)
        {
            $destinationFile = self::getPath() . '/' . $storages['w'] . '/movimientos/' . $filename;
            if (file_exists($destinationFile))
            {
                return file_get_contents($destinationFile);
            }
        }

        return $destinationfile;
    }
    static public function RetrieveGenericFile($internalName)
    {
        $md5 = md5($internalName);
        
        $storages = self::getStorages();
        foreach($storages['r'] as $storage)
        {
            $destinationFile = self::getPath() . '/' . $storage . '/generic/' . $md5[0] . '/' . $md5[1] . '/' . $md5[2] . '/'. $internalName;
            if (file_exists($destinationFile))
            {
                return $destinationFile;
            }
        }
        
        return false;
    } 
    
    static public function RetrieveGeneric($internalName)
    {
        $md5 = md5($internalName);
        
        $storages = self::getStorages();
        foreach($storages['r'] as $storage)
        {
            $destinationFile = self::getPath() . '/' . $storage . '/generic/' . $md5[0] . '/' . $md5[1] . '/' . $md5[2] . '/'. $internalName;
            if (file_exists($destinationFile))
            {
                return file_get_contents($destinationFile);
            }
        }
        
        return false;
    } 
    
    
    static function getContainer()
    {
        global $kernel;
        if ('AppCache' == get_class($kernel)) {
             $kernel = $kernel->getKernel();
        }
        return $kernel->getContainer();
    }
    
    static function getPath()
    {
        $kernel = self::getContainer()->get('kernel');
        $bundle = $kernel->getBundle('LughWebAppBundle');
        $rootPath = $bundle->getPath() . '/Files';
        return $rootPath;
    }
}