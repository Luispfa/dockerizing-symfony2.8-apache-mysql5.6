<?php

namespace Lugh\WebAppBundle\Lib\External;

class WebCastApi{

    private $appKey;
    private $secretKey;
    
    
    
    static public function GetUrl($event_id, $session_id)
    {
        $webcastApi = new WebCastApi();
        return $webcastApi->getAccessUrl($event_id, $session_id);
        
    }
    
    public function __construct() {
        $this->appKey       = $this->getKernel()->getContainer()->get('lugh.parameters')->getByKey('Av.live.appkey', '');
        $this->secretKey    = $this->getKernel()->getContainer()->get('lugh.parameters')->getByKey('Av.live.secretkey', '');
    }
    
    private function getKernel()
    {
        global $kernel;

        if ('AppCache' == get_class($kernel)) {
            $kernel = $kernel->getKernel();
        }
        return $kernel;
    }
    
    private function getBody($url)
    {
        $appKey         = $this->appKey;
        $secretKey      = $this->secretKey;
        $now            = time();
        $signature      = hash_hmac('sha1', $appKey.$now, $secretKey);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        $headers = array(
          'date: '.$now,
          'appkey: '.$appKey,
          'signature: '.$signature,
          'signatureversion: v1');
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($output, 0, $header_size);
        $body = substr($output, $header_size);
        curl_close($ch);
        
        return $body;
    }
    
    public function getAccessUrl($event_id, $session_id) {
        
        $body = $this->getBody("http://api.webcasting-studio.net/events/$event_id/access/list?sessionId=$session_id");
        $access_json = json_decode($body, true);
        $acces = $access_json[0];
        
        if ($acces['name'] == 'Single Sign-on (SSO).' && isset($acces['url'][0]['url']))
        {
            return $acces['url'][0]['url'];
        }
        
        return null;
        
    }
    
}