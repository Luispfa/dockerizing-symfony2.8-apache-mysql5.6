<?php

namespace Lugh\WebAppBundle\Lib\External;

/**
 * Class HeaderCertValidator
 * @author Alexander Criollo at Header ASG
 */

class CertificateValidator {

    public $name = '';
    public $surname = '';
    public $identity = '';
    public $issuer = '';
    private $status = '';
    private $bypass;
    public $certificate;

    static public function GetCertificateData($bypass) {

        set_time_limit(0);

        $validator = new CertificateValidator($bypass);
        $validator->Validate();

        $validate_array = array();

        sleep(3);
        $validate_array = array(
            'status' => $validator->status,
            'name' => $validator->name . ' ' . $validator->surname,
            'dni' => $validator->identity,
            'issuer' => $validator->issuer,
            'clientCert' => $validator->certificate
        );


        return $validate_array;

    }

    private function __construct($bypass)
    {
        $this->status = 'INVALID';
        $this->issuer = 'UNKNOWN';
        $this->bypass = $bypass;
    }

    private function Validate()
    {
        $dataCert = CertificateHelper::PerformVerificationFromGlobals();
        $datos = openssl_x509_parse($dataCert['certificate'], true);
        $this->certificate = $dataCert['certificate'];

        //var_dump($datos);

        $now = time();

        if ($datos['validFrom_time_t'] > $now || $datos['validTo_time_t'] < $now)
        {
            $this->status = 'INVALID';
        }
        else
        {
            // Obtenemos datos del certificado
            if(isset($datos['issuer']['CN'])){
                $this->issuer = $datos['issuer']['CN'];
            }

            if(isset($datos['subject']['CN']) && $datos['subject']['CN'] != ''){
                $this->name = $datos['subject']['SN'];
                $this->surname = $datos['subject']['GN'];

                    $serialNumber = explode("-",$datos['subject']['serialNumber']);
                    if(count($serialNumber) > 1){
                        $this->identity = $serialNumber[1];
                    }
                    else{
                        $this->identity = $serialNumber[0];
                    }

            }

            if ($this->bypass) {
                $this->status = 'VALID';
            }
            else {

                //var_dump($datos);
                // ISSUER
                $issuer = preg_replace('/\s+/', '_', trim($datos['issuer']['CN']) ) . '.cer';
                // URL OCSP
                $url = '';
                foreach(preg_split("/((\r?\n)|(\r\n?))/", $datos['extensions']['authorityInfoAccess']) as $line){
                    if (preg_match('%\b(OCSP)\b%i', $line) > 0) {
                    //if (strpos($line, 'OCSP') !== false) {
                        //$auxUrl = ;
                        $url = trim(explode('URI:', $line)[1]);
                    }
                }
                // ROOT
                $root = '';
                if (preg_match('%(FNMT)%i', $issuer) > 0) {
                    $root = 'AC_Raiz_FNMT-RCM.cer';
                } else if (preg_match('%(DNIE_001|DNIE_002|DNEI_003)%i', $issuer) > 0) {
                    $root = 'AC_RAIZ_DNIE.cer';
                } else if (preg_match('%(DNIE_004|DNI_005|DNI_006)%i', $issuer) > 0) {
                    $root = 'AC_RAIZ_DNIE_2.cer';
                }

                $ocspResponse = $this->ValidateOCSP($root, $issuer, $url);

                if($ocspResponse == 'invalidIssuer' || $ocspResponse == 'revoked'  || $ocspResponse == 'unknown'){
                    $this->status = 'INVALID';
                }
                else{
                    $this->status = 'VALID';
                }
            }
        }
    }


    private function ValidateOCSP($root, $intermediate, $url){
        
        $dataDir = CertificateValidator::getKernel()->getBundle('LughWebAppBundle')->getPath() . '/Lib/External/';

        //paths
        $dirRoot = $dataDir . 'root/';
        $dirIntermediate = $dataDir . 'intermediate/';

        $ocspResponse = 'invalidIssuer';

        if($root != '' && $intermediate != '' && $url != ''){

            $temp = tmpfile();
            fwrite($temp, $this->certificate);
            $path = stream_get_meta_data($temp)['uri'];

            $execute = 'openssl ocsp -CAfile '.$dirRoot.$root.' -issuer '.$dirIntermediate.$intermediate.' -cert '.$path.' -url '.$url .' 2>&1';
            $output = shell_exec($execute);

//            var_dump($execute);
//            var_dump($output);

            if($output != null && (preg_match('%(Error)%i', $output) == 0)) {
                $output2 = preg_split('/[\r\n]/', $output);
                $output3 = preg_split('/: /', $output2[1]);
                $ocspResponse = $output3[1]; // will be "good", "revoked", or "unknown"
            }
            fclose($temp);
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


