<?php

namespace Lugh\WebAppBundle\Lib\External;

class CertificateHelperOld{

    public $certificate = null;
    public $commonName = null;
    public $subject = null;
    
    static public function PerformVerificationFromGlobals()
    {
        $ch = new CertificateHelperOld();
        
        $ch->CaptureCertFromGlobals();
        
        return array(
            'isTrusted' => $ch->isTrusted(),
            'commonName' => $ch->commonName,
            'certificate' => $ch->certificate,
            'subject'   => $ch->getSubject()
            );
    }
    
    public function CaptureCertFromParm($cert)
    {
        $this->certificate = $cert;
    }
    
    public function CaptureCertFromGlobals()
    {
        $cert = null;
        $commonName = null;
        
        if (isset($_SERVER["SSL_CLIENT_CERT"]))
        {
            $cert = $_SERVER["SSL_CLIENT_CERT"];
        }
        else if (isset($_SERVER["REDIRECT_SSL_CLIENT_CERT"]))
        {
            $cert = $_SERVER["REDIRECT_SSL_CLIENT_CERT"];
        }

        if (isset($_SERVER["SSL_CLIENT_S_DN_OU"]))
        {
            $commonName = $_SERVER["SSL_CLIENT_S_DN_OU"];
        }
        else if (isset($_SERVER["REDIRECT_SSL_CLIENT_S_DN_OU"]))
        {
            $commonName = $_SERVER["REDIRECT_SSL_CLIENT_S_DN_OU"];
        }
        else if (isset($_SERVER["SSL_CLIENT_S_DN_O"]))
        {
            $commonName = $_SERVER["SSL_CLIENT_S_DN_O"];
        }
        else if (isset($_SERVER["REDIRECT_SSL_CLIENT_S_DN_O"]))
        {
            $commonName = $_SERVER["REDIRECT_SSL_CLIENT_S_DN_O"];
        }
        else if (isset($_SERVER["SSL_CLIENT_I_DN_CN"]))
        {
            $commonName = $_SERVER["SSL_CLIENT_I_DN_CN"];
        }
        elseif (isset($_SERVER["REDIRECT_SSL_CLIENT_I_DN_CN"]))
        {
            $commonName = $_SERVER["REDIRECT_SSL_CLIENT_I_DN_CN"];
        }
        
        $this->certificate = $cert;
        $this->commonName = $commonName;
    }
    
    public function isTrusted()
    {
        $isTrusted = false;
        $result = "";
        
        $dataDir = $this->getKernel()->getBundle('LughWebAppBundle')->getPath() . '/data';
        
        $cmd = sprintf('openssl verify -CAfile %s/ca-bundle.crt ',$dataDir);

        $descriptorspec = array(
           0 => array("pipe", "r"),  // stdin
           1 => array("pipe", "w"),  // stdout
           2 => array("pipe", "w"),
        );
        
        
        $process = proc_open($cmd, $descriptorspec, $pipes);

        if (is_resource($process)) {
            // 0 => writeable handle connected to child stdin
            // 1 => readable handle connected to child stdout
            // 2 => readable handle connected to child stderr

            fwrite($pipes[0], $this->certificate);
            fclose($pipes[0]);

            $result = stream_get_contents($pipes[1]);
            fclose($pipes[1]);

            $error = stream_get_contents($pipes[2]);
            fclose($pipes[2]);
            
            $return_value = proc_close($process); // Avoid deadlock
            
            if(strpos($result,"stdin: OK") === 0 && $return_value === 0)
            {
                $isTrusted = true;
            }
        }
        
        return $isTrusted;
    }
    
    private function getSubject()
    {
        $result = "";
        
        $cmd = 'openssl x509 -noout -subject ';

        $descriptorspec = array(
           0 => array("pipe", "r"),  // stdin
           1 => array("pipe", "w"),  // stdout
           2 => array("pipe", "w"),
        );
        
        
        $process = proc_open($cmd, $descriptorspec, $pipes);

        if (is_resource($process)) {
            // 0 => writeable handle connected to child stdin
            // 1 => readable handle connected to child stdout
            // 2 => readable handle connected to child stderr

            fwrite($pipes[0], $this->certificate);
            fclose($pipes[0]);

            $result = stream_get_contents($pipes[1]);
            fclose($pipes[1]);

            $error = stream_get_contents($pipes[2]);
            fclose($pipes[2]);
            
            $return_value = proc_close($process); // Avoid deadlock
            
        }
        
        return $result;
    }
    
    private function getKernel()
    {
        global $kernel;

        if ('AppCache' == get_class($kernel)) {
            $kernel = $kernel->getKernel();
        }
        return $kernel;
    }
    
    static public function bcdechex($dec) {
            $last = bcmod($dec, 16);
            $remain = bcdiv(bcsub($dec, $last), 16);
            if($remain == 0) {
                return dechex($last);
            } else {
                return self::bcdechex($remain).dechex($last);
            }
    }
    
}