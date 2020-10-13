<?php

namespace Lugh\DbConnectionBundle\Lib\Classes\PDF;

class Site {
    private $startDate = null;
    private $baseSite = null;
    private $endDate = null;
    private $apiKey = null;
    private $siteId = -1;
    
    private $_visitsCacheArray = null;
    
    public function __construct($baseSite, $apiKey, $siteId, \DateTime $startDate, \DateTime $endDate) {
        $this->startDate = $startDate;
        $this->baseSite  = $baseSite;
        $this->endDate   = $endDate;
        $this->apiKey    = $apiKey;
        $this->siteId    = $siteId;
    }

    //https://analytics.juntadeaccionistas.es/index.php?module=API&method=Live.getLastVisitsDetails&format=JSON&idSite=39&period=range&date=2013-03-14,2013-04-29&token_auth=4d4ea64aaca9921879b92224c4fb7a64&filter_limit=100
    public function GetVisitsJson() {
        return file_get_contents(
                $this->baseSite . 'index.php?module=API&method=Live.getLastVisitsDetails&format=JSON&idSite=' .
                $this->siteId .
                '&period=range&date=' .
                $this->startDate->format('Y-m-d') .
                ',' .
                $this->endDate->format('Y-m-d') .
                '&token_auth=' .
                $this->apiKey .
                '&filter_limit=1000000'
        );
    }

    public function GetVisitHourGraphImagePng() {
        return file_get_contents(
                $this->baseSite .
                'index.php?module=API&graphType=verticalBar&width=400&height=200&method=ImageGraph.get&idSite=' .
                $this->siteId .
                '&period=range' .
                '&date=' .
                $this->startDate->format('Y-m-d') .
                ',' .
                $this->endDate->format('Y-m-d') .
                '&apiModule=VisitTime&apiAction=getVisitInformationPerServerTime&format=JSON&token_auth=' .
                $this->apiKey
        );
    }
    
    //Devuelve el string de la url en vez del resultado de get_contents
    public function GetVisitHourGraphImagePngPath() {
        return  $this->baseSite .
                'index.php?module=API&graphType=verticalBar&width=400&height=200&method=ImageGraph.get&idSite=' .
                $this->siteId .
                '&period=range' .
                '&date=' .
                $this->startDate->format('Y-m-d') .
                ',' .
                $this->endDate->format('Y-m-d') .
                '&apiModule=VisitTime&apiAction=getVisitInformationPerServerTime&format=JSON&token_auth=' .
                $this->apiKey;
    }

    public function GetVisitDayGraphImagePng() {
        return file_get_contents(
                $this->baseSite .
                'index.php?module=API&graphType=evolution&width=800&height=200&method=ImageGraph.get&idSite=' .
                $this->siteId . '&period=day' . '&date=' .
                $this->startDate->format('Y-m-d') . ',' .
                $this->endDate->format('Y-m-d') .
                '&apiModule=VisitsSummary&apiAction=get&token_auth=' .
                $this->apiKey
        );
    }
    
    public function GetVisitDayGraphImagePngPath() {
        return  $this->baseSite .
                'index.php?module=API&graphType=evolution&width=800&height=200&method=ImageGraph.get&idSite=' .
                $this->siteId . '&period=day' . '&date=' .
                $this->startDate->format('Y-m-d') . ',' .
                $this->endDate->format('Y-m-d') .
                '&apiModule=VisitsSummary&apiAction=get&token_auth=' .
                $this->apiKey;
    }

    public function GetAndParseVisitsInfo() {
        if ($this->_visitsCacheArray === null) {
            $this->_visitsCacheArray = json_decode($this->GetVisitsJson(), true);
        }
        $r = &$this->_visitsCacheArray;

        $visitasStruct = array();
        $visitasStruct['unicas'] = 0;
        $visitasStruct['retornos'] = 0;
        $visitasStruct['paises'] = array();

        $referrersStruct = array();
        $referrersStruct['directo'] = 0;
        $referrersStruct['buscador'] = 0;
        $referrersStruct['paginaExterna'] = 0;
        $referrersStruct['urlsList'] = array();
        $referrersStruct['keywordsList'] = array();

        $paisesArray = array();
        $urlsArray = array();

        foreach ($r as $visit) {
            if ($visit['visitorType'] == 'new') {
                if ($visit['referrerType'] == 'direct') {
                    $referrersStruct['directo'] ++;
                } else {
                    if (isset($urlsArray[$visit['referrerUrl']])) {
                        $urlsArray[$visit['referrerUrl']] ++;
                    } else {
                        $urlsArray[$visit['referrerUrl']] = 1;
                    }

                    $referrersStruct['paginaExterna'] ++;
                }

                if ($visit['referrerKeyword'] != '') {
                    $referrersStruct['keywordsList'][] = $visit['referrerKeyword'];
                    $referrersStruct['buscador'] ++;
                }

                if (isset($paisesArray[$visit['country']])) {
                    $paisesArray[$visit['country']] ++;
                } else {
                    $paisesArray[$visit['country']] = 1;
                }

                $visitasStruct['unicas'] ++;
            } else {
                $visitasStruct['retornos'] ++;
            }
        }

        $visitasStruct['paises'] = $paisesArray;
        $referrersStruct['urlsList'] = $urlsArray;

        return array(
            'referrers' => $referrersStruct,
            'visits' => $visitasStruct
        );
    }
}
