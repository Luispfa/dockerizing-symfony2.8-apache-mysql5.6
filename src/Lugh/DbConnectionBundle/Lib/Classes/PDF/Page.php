<?php

namespace Lugh\DbConnectionBundle\Lib\Classes\PDF;

interface Page{
    public function getBody($pdf);
}