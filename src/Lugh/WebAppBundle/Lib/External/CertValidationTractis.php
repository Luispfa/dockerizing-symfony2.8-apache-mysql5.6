<?php

namespace Lugh\WebAppBundle\Lib\External;


class CertValidationTractis
{

/* -------------------------- TRACTIS EXCEPTIONS (BYPASS) --------------------- */

	private $exceptions = array('FNMT'=>'verifyFNMT');

	/* TODO: Mejorar esto */
	private function verifyFNMT()
	{
		list($raw_name,$raw_dni) = explode('-', (isset($_SERVER["SSL_CLIENT_S_DN_CN"]) ? $_SERVER["SSL_CLIENT_S_DN_CN"] : $_SERVER["REDIRECT_SSL_CLIENT_S_DN_CN"]));

		$dni = trim(str_ireplace('nif','',$raw_dni));
		$name = trim(str_ireplace('nombre','',$raw_name));
		
		$issuer = 'FNMT';

		return array('status' => 'VALID',
			'name' => $name,
			'dni' => $dni,
			'issuer' => $issuer
			);
	}
        private function verifyCertificateFNMT()
	{
		list($raw_name,$raw_dni) = explode('-', (isset($_SERVER["SSL_CLIENT_S_DN_CN"]) ? $_SERVER["SSL_CLIENT_S_DN_CN"] : $_SERVER["REDIRECT_SSL_CLIENT_S_DN_CN"]));

		$dni = trim(str_ireplace('nif','',$raw_dni));
		$name = trim(str_ireplace('nombre','',$raw_name));
		
		$issuer = 'FNMT';
                $fnmtCert =CertificateHelperOld::PerformVerificationFromGlobals();

		return array('status' => ($fnmtCert['isTrusted']) ? 'VALID' : 'INVALID',
			'name' => $name,
			'dni' => $dni,
			'issuer' => $issuer,
                        'clientCert' => $this->client_cert
			);
	}
        
        private function verifyCertificateHeader()
	{
		list($id, $raw_name,$raw_dni) = explode('-', (isset($_SERVER["SSL_CLIENT_S_DN_CN"]) ? $_SERVER["SSL_CLIENT_S_DN_CN"] : $_SERVER["REDIRECT_SSL_CLIENT_S_DN_CN"]));

		$dni = trim(str_ireplace('nif','',$raw_dni));
		$name = trim(str_ireplace('nombre','',$raw_name));
		
		$issuer = 'HEADER';
                $fnmtCert =CertificateHelperOld::PerformVerificationFromGlobals();

		return array('status' => ($fnmtCert['isTrusted']) ? 'VALID' : 'INVALID',
			'name' => $name == '' ? $id : $name,
			'dni' => $dni == '' ? $id : $dni,
			'issuer' => $issuer,
                        'clientCert' => $this->client_cert
			);
	}
	
        private function verifyCertificateANCERT()
	{
		$raw_name = isset($_SERVER["SSL_CLIENT_S_DN_CN"]) ? $_SERVER["SSL_CLIENT_S_DN_CN"] : $_SERVER["REDIRECT_SSL_CLIENT_S_DN_CN"];
                $raw_dni = isset($_SERVER["SSL_CLIENT_S_DN"]) ? $_SERVER["SSL_CLIENT_S_DN"] : $_SERVER["REDIRECT_SSL_CLIENT_S_DN"];
                
		$dni = substr(trim(stristr(stristr(stristr($raw_dni,'serialNumber='),'='),'/',true)),1);
		$name = trim(str_ireplace('nombre','',$raw_name));
		
		$issuer = 'ANCERTV2';
                $fnmtCert =CertificateHelperOld::PerformVerificationFromGlobals();

		return array('status' => ($fnmtCert['isTrusted']) ? 'VALID' : 'DENIED',
			'name' => $name,
			'dni' => $dni,
			'issuer' => $issuer,
                        'clientCert' => $this->client_cert
			);
	}
        
	private function verifyBypass()
	{
                $dniCert =CertificateHelperOld::PerformVerificationFromGlobals();
                
                if (isset($dniCert['isTrusted']) && $dniCert['isTrusted'] == true)
                {
                    $sub = $dniCert['subject'];
                    list($dni) = sscanf(substr($sub,strpos($sub, 'serialNumber')),'serialNumber=%[^/]/');
                    list($ape1) = sscanf(substr($sub,strpos($sub, 'SN=')),'SN=%[^/]/');
                    list($nom) = sscanf(substr($sub,strpos($sub, 'GN=')),'GN=%[^/]/');
                    $cn = substr($sub,strpos($sub, 'CN=')+3);
                    $ape2 = substr($cn,0,strpos($cn, ','));
                    $name = $nom . ' ' . $ape2;
                    $issuer = 'DNIe';
                    $status = ($dniCert['isTrusted']) ? 'VALID' : 'INVALID';
                    
                    if($this->debug_mode == true)
                    {
                            $DCHelper = "\r\n IsTrusted => " . $dniCert['isTrusted'] . "\r\n".
                                        "\r\n CommonName => " . $dniCert['commonName'] . "\r\n".
                                        "\r\n Certificate => " . $dniCert['certificate'] . "\r\n".
                                        "\r\n Subject => " . $dniCert['subject'] . "\r\n";
                            
                            $toSave .= "DNIeCERT: \r\n". $DCHelper ."\r\n";
                            $toSave .= "Response: \r\n".($dniCert['isTrusted']) ? 'VALID' : 'INVALID'."\r\n";
                            $toSave .= "\r\n". date('Y-m-d H:i:s')."\r\n###########\r\n";

                            file_put_contents('../tractis_debug.txt',$toSave,FILE_APPEND);
                    }
                }
                else
                {
                    list($dni) = sscanf(substr($str,strpos($_SERVER['REDIRECT_SSL_CLIENT_S_DN'], 'serialNumber')),'serialNumber=%[^/]/');
                    list($ape1) = sscanf(substr($str,strpos($_SERVER['REDIRECT_SSL_CLIENT_S_DN'], 'SN')),'SN=%[^/]/');
                    list($nom) = sscanf(substr($str,strpos($_SERVER['REDIRECT_SSL_CLIENT_S_DN'], 'GN')),'GN=%[^/]/');
                    list($ape2) = sscanf(substr($str,strpos($_SERVER['REDIRECT_SSL_CLIENT_S_DN'], 'CN')),'CN=%s');
                    list($name,$rest) = explode('(',$_SERVER['REDIRECT_SSL_CLIENT_S_DN_CN']);
                    $issuer = 'UNKNOW';
                    $status = ($this->client_cert != null && $this->client_cert != '') ? 'VALID' : 'INVALID';
                }

		return array(
                        'status' => $status,
			'name' => $name,
			'dni' => $dni,
			'issuer' => $issuer,
                        'clientCert' => $this->client_cert
			);
	}	
/* -------------------------- TRACTIS EXCEPTIONS (BYPASS) --------------------- */

	private $client_cert;
	private $tractis_user;
	private $tractis_password;
	private $tractis_api_key;
	private $debug_mode;
        private $ssl_client;

	private function __construct($client_cert, $tractis_user, $tractis_password, $tractis_api_key)
	{
		$this->client_cert = $client_cert;
		$this->tractis_user = $tractis_user;
		$this->tractis_password = $tractis_password;
		$this->tractis_api_key = $tractis_api_key;
		$this->debug_mode = file_exists('../tractis.debug_enabled');

		if($this->debug_mode == true)
		{
			$toSave = '';
			$toSave .= date('Y-m-d H:i:s')."\r\n@@@@@@@@@\r\n";
			$toSave .= '$_SERVER:'."\r\n".serialize($_SERVER)."\r\n";
			file_put_contents('../tractis_debug.txt',$toSave,FILE_APPEND);
		}

	}

        
        //this is the function that is currently used
	static public function GetClientCertificateData($bypass)
	{
            set_time_limit(0);
            
            $datos = openssl_x509_parse(isset($_SERVER["SSL_CLIENT_CERT"]) ? $_SERVER["SSL_CLIENT_CERT"] : $_SERVER["REDIRECT_SSL_CLIENT_CERT"] ,0);
            $nombre = '';
            $dni = '';
            $issuer = '';
            $status = 'VALID';
            $ocspResponse = '';
            
            $validFrom = $datos['validFrom_time_t'];
            $validTo = $datos['validTo_time_t'];
            $now = time();
            
            if($validFrom > $now || $validTo < $now){
                
                $status = 'INVALID';
            }
            
            else{
            
                //gather serial number, issuer, subject name and dni
                $serial = "0x".  CertificateHelperOld::bcdechex($datos['serialNumber']);
                if(isset($datos['issuer']['commonName'])){
                    $issuer = $datos['issuer']['commonName'];
                }

                if(isset($datos['subject']['commonName']) && $datos['subject']['commonName'] != ''){
                    $commonName = explode("-",$datos['subject']['commonName']);
                    $nombre = trim(explode("(",$commonName[0])[0]);
                    if(count($commonName) > 1){
                        $dni = trim($commonName[1]);
                    }
                    else{
                        $serialNumber = explode("-",$datos['subject']['serialNumber']);
                        if(count($serialNumber) > 1){
                            $dni = $serialNumber[1];
                        }
                        else{
                            $dni = $serialNumber[0];
                        }
                    }

                    $ocspResponse = HeaderCertValidator::validator($serial, $issuer, $bypass);

                    if($ocspResponse == 'invalidIssuer' || $ocspResponse == 'revoked'  || $ocspResponse == 'unknown'){

                        $status = 'INVALID';
                    }
                    elseif($ocspResponse == 'bypass'){
                        
                        $status = 'VALID';
                    }

                }
                else{
                    $status = 'INVALID';
            }
            
            }
            
              
            $validate_array = array();
            
            sleep(3);
            $validate_array = array('status' => $status,
                'name' => $nombre,
                'dni' => $dni,
                'issuer' => $issuer,
                'clientCert' => isset($_SERVER["SSL_CLIENT_CERT"]) ? $_SERVER["SSL_CLIENT_CERT"] : $_SERVER["REDIRECT_SSL_CLIENT_CERT"]
                );
		

            return $validate_array;
	}

	private function verifyGeneric()
	{
            set_time_limit(0);
            $SSL_CLIENT_S_DN_O = null;
            $SSL_CLIENT_I_DN_CN = null;

            if (isset($_SERVER["SSL_CLIENT_S_DN_O"]))
            {
                $SSL_CLIENT_S_DN_O = $_SERVER["SSL_CLIENT_S_DN_O"];
            }

            else if (isset($_SERVER["REDIRECT_SSL_CLIENT_S_DN_O"]))
            {
                $SSL_CLIENT_S_DN_O = $_SERVER["REDIRECT_SSL_CLIENT_S_DN_O"];
            }
            else if (isset($_SERVER["SSL_CLIENT_I_DN_CN"]))
            {
                $SSL_CLIENT_I_DN_CN = $_SERVER["SSL_CLIENT_I_DN_CN"];
            }

            else if (isset($_SERVER["REDIRECT_SSL_CLIENT_I_DN_CN"]))
            {
                $SSL_CLIENT_I_DN_CN = $_SERVER["REDIRECT_SSL_CLIENT_I_DN_CN"];
            }
            $this->ssl_client = $SSL_CLIENT_S_DN_O;


            if ($SSL_CLIENT_S_DN_O == 'FNMT')
            {
                    return $this->verifyCertificateFNMT();
            }
            if ($SSL_CLIENT_I_DN_CN == 'AC FNMT Usuarios')
            {
                    return $this->verifyCertificateFNMT();
            }
            elseif(stristr($SSL_CLIENT_I_DN_CN, 'ANCERT Certificados FERN V2') != false)
            {
                     return $this->verifyCertificateANCERT();
            }
            elseif ($SSL_CLIENT_S_DN_O == 'Header S.L.')
            {
                    return $this->verifyCertificateHeader();
            }
            elseif(file_exists('../tractis.bypass_enabled'))
            //if(file_exists('tractis.bypass_enabled'))
            {
                     return $this->verifyBypass();
            }
            else
            {
                    return $this->verifyTractis();
            }
		
	}

	private function verifyTractis()
	{
                /*set_time_limit(0);
		$url = 'https://www.tractis.com/certificate_verification?api_key=' . $this->tractis_api_key;

		$ch = curl_init();
		curl_setopt ($ch, CURLOPT_URL,$url);
		curl_setopt ($ch, CURLOPT_POST, true);
		curl_setopt ($ch, CURLOPT_POSTFIELDS, $this->client_cert);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);

		curl_setopt ($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt ($ch, CURLOPT_USERPWD, $this->tractis_user.':'.$this->tractis_password);

		// Only for development, remove next line on production !!!
		curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, false);
                
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,0);
                curl_setopt($ch, CURLOPT_NOSIGNAL, 1);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);

		$result = curl_exec($ch);
                
                $curl_errno = curl_errno($ch);
                $curl_error = curl_error($ch);

		curl_close($ch);
                

		if($this->debug_mode == true)
		{
			$toSave .= "URL: \r\n".$url.' -- U:P :'.$this->tractis_user.':'.$this->tractis_password."\r\n";
			$toSave .= "Response: \r\n".$result."\r\n";
			$toSave .= date('Y-m-d H:i:s')."\r\n###########\r\n";

			file_put_contents('../tractis_debug.txt',$toSave,FILE_APPEND);
		}
                
                if ($curl_errno > 0) {
                        $this->sendCurlML($curl_errno,$curl_error);
                        
                        return array(
                            'status' => 'INVALID'
                            );
                }*/

		return $this->parseResponse($result);
	}

	private function parseResponse($response)
	{
		$xml = simplexml_load_string($response);
                
		$retVal = array();

		$retVal['status'] = substr($xml->validationStatus,0);
		if($retVal['status'] == 'VALID')
		{
			$name = $xml->xpath("//attribute[@id='tractis:attribute:name']");
			$retVal['name'] = substr($name[0],0);

			$dni = $xml->xpath("//attribute[@id='tractis:attribute:dni']");
			$retVal['dni'] = substr($dni[0],0);

			$issuer = $xml->xpath("//attribute[@id='tractis:attribute:issuer']");
			$retVal['issuer'] = substr($issuer[0],0);
                        $retVal['clientCert'] = $this->client_cert;
		}
		else
		{
			$retVal['status'] = 'INVALID';
                        //$this->sendML($xml);
		}

		return $retVal;
	}
        
        private function sendML($xml)
        {
            $logger = new MassiveLog();
            $status = 'ERROR';
            $result = '';
            if (property_exists($xml,'validationStatus'))
            {
                $status = 'WARN';
                $result = substr($xml->validationStatus,0);
            }
            else
            {
                $status = 'CRITICAL';
                $result = 'INVALID';
            }
            
            $logger->MLLog(
                    $status, 
                    'Tractis check result : ' . $result, 
                    'TRACTIS', 
                    'Proveedor de identidad Tractis', 
                    'DNIE',
                    array(
                        'details'       => $xml, 
                        'source'        => $_SERVER['HTTP_HOST'],
                        'client'        => $this->ssl_client,
                        'certificate'   => $this->client_cert
                        )
                    );
        }
        
        private function sendCurlML($curl_errno,$curl_error)
        {
            $logger = new MassiveLog();
            
            $logger->MLLog(
                    $status, 
                    'Tractis check result : ' . 'No Response', 
                    'TRACTIS', 
                    'Proveedor de identidad Tractis', 
                    'DNIE',
                    array(
                        'details'       => "cURL Error ($curl_errno): $curl_error", 
                        'source'        => $_SERVER['HTTP_HOST'],
                        'client'        => $this->ssl_client,
                        'certificate'   => $this->client_cert
                        )
                    );
        }
        
        static private function getKernel()
        {
            global $kernel;
            if ('AppCache' == get_class($kernel)) {
                $kernel = $kernel->getKernel();
            }
            return $kernel;
        }
        
        public function bcdechex($dec) {
            $last = bcmod($dec, 16);
            $remain = bcdiv(bcsub($dec, $last), 16);
            if($remain == 0) {
                return dechex($last);
            } else {
                return bcdechex($remain).dechex($last);
            }
        }

}
/*
ini_set('display_errors', '1');
error_reporting(E_ALL);
*/
//print_r(CertValidationTractis::GetClientCertificateData());
//print_r($_SERVER);


?>
