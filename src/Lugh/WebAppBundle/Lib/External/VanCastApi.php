<?php

namespace Lugh\WebAppBundle\Lib\External;

class VanCastApi {

    private $url;
    private $secretKey;
    private $publicKey;
    
    static public function GetUrl($email, $name)
    {
        $vancastApi = new VanCastApi();
        return $vancastApi->getAccessUrl($email, $name);
    }
    
    public function __construct() {
    
        $this->url = $this->getKernel()->getContainer()->get('lugh.parameters')->getByKey('Av.live.address', '');
        $this->secretKey = $this->getKernel()->getContainer()->get('lugh.parameters')->getByKey('Av.live.secretkey', '');
        $this->publicKey = $this->getKernel()->getContainer()->get('lugh.parameters')->getByKey('Av.live.appkey', '');
    }
    
    private function getKernel() {
        
        global $kernel;
        
        if ('AppCache' == get_class($kernel)) {
            $kernel = $kernel->getKernel();
        }
        
        return $kernel;
    }
    
    public function getAccessUrl($email, $name) {
    
        $end_user = '{"email":"' . $email .'","firstName":"' . $name . '","lastName":""}';
        
        $data = split('\?', split('event', $this->url)[1])[0];
        $canonical_url = '/event' . $data;
        
        $date = new \DateTime('now', new \DateTimeZone('UTC'));
        $date_expires = clone $date;
        $date_interval = new \DateInterval('PT500S');
        $date_expires->add($date_interval);
        
        $manifiest = "GET" . "\n" .
                     $canonical_url . "\n" .
                     "WS-Date=" . $date->getTimestamp() . "&WS-Expire=" . $date_expires->getTimestamp() . "\n" .
                     $end_user;
                     
        $signingKey = (hash_hmac('sha256', $date->getTimestamp(), $this->secretKey));
        
        $signature = (hash_hmac('sha256', $manifiest, $signingKey));
        
        
        $url = $this->url .
               '&WS-Date=' . $date->getTimestamp() . 
               '&WS-Expire=' . $date_expires->getTimestamp() . 
               '&WS-EndUser=' . base64_encode($end_user) . 
               '&WS-SSOKey=' . $this->publicKey . 
               '&WS-SSOSignature=' . $signature;
        
        return $url;
    }
}
