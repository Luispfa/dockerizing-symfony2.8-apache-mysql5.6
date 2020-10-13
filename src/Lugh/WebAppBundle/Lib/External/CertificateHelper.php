<?php

namespace Lugh\WebAppBundle\Lib\External;

class CertificateHelper{

    private $certificate = null;
    private $commonName = null;

    static public function PerformVerificationFromGlobals()
    {
        $ch = new CertificateHelper();
        $ch->CaptureCertFromGlobals();

        return array(
            'commonName' => $ch->commonName,
            'certificate' => $ch->certificate,
            );
    }
    
    private function CaptureCertFromGlobals()
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
        else
        {
            $cert = 0;
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
        else
        {
            $commonName = '';
        }
        
        $this->certificate = $cert;
        $this->commonName = $commonName;
    }
}