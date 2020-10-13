<?php

namespace Lugh\WebAppBundle\Audit;


class SessionRequestProcessor
{

    public function processRecord(array $record)
    {
        $record['extra']['host'] = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';

        return $record;
    }
    
}
