<?php

namespace Lugh\WebAppBundle\Lib\External;

/**
 * Class HeaderCertValidator
 * @author Remei Ridorsa at Header ASG
 */

class HeaderCertValidator {
    
    static public function validator($serial, $issuer, $bypass){
        
        $dataDir = HeaderCertValidator::getKernel()->getBundle('LughWebAppBundle')->getPath() . '/Lib/External/';
        $ocspResponse = '';
        
        //paths
        //$dirRoot = $dataDir . 'root/';
        $dirRoot = 'root/';
        //$dirIntermediate = $dataDir . 'intermediate/';
        $dirIntermediate = 'intermediate/';

        //root certificate
        $root = '';
        //intermediate certificate
        $intermediate = '';
        //OCSP url
        $OCSPUrl = '';


        if(strpos($issuer, 'FNMT') !== false){

            $root = 'FMNT_Root_Base64.cer';
            $intermediate = 'FMNT_Intermediate_Base64.cer';
            $OCSPUrl = 'http://ocspusu.cert.fnmt.es/ocspusu/OcspResponder';

        }

        elseif(strpos($issuer, 'DNI') !== false){

            $root = 'DNIE_Root.crt';
            $intermediate = 'DNIE_Intermediate_Base64.cer';
            $OCSPUrl = 'http://ocsp.dnie.es ';
        }


        if($root != '' && $intermediate != '' && $OCSPUrl != ''){

            //$execute = 'openssl ocsp -CAfile '.$dirRoot.$root.' -issuer '.$dirIntermediate.$intermediate.' -serial '.$serial.' -url '.$OCSPUrl .' 2>&1';
            $execute = 'openssl ocsp -CAfile '.$dirRoot.$root.' -issuer '.$dirIntermediate.$intermediate.' -serial '.$serial.' -url '.$OCSPUrl;
            //var_dump($execute);
            
            $output = shell_exec($execute);
            
            if($output != null){
                //var_dump($output);

                $output2 = preg_split('/[\r\n]/', $output);
                $output3 = preg_split('/: /', $output2[1]);
                $ocspResponse = $output3[1]; // will be "good", "revoked", or "unknown"
                //var_dump($ocspResponse);
            }
            else{
                $ocspResponse = $bypass ? 'bypass' : 'invalidIssuer';
            }

        }
        else{
            
            $ocspResponse = 'invalidIssuer';
        }

        return $ocspResponse;
        
        
    }
    
    
    static private function getKernel()
    {
        global $kernel;

        if ('AppCache' == get_class($kernel)) {
            $kernel = $kernel->getKernel();
        }
        return $kernel;
    }
    
}


