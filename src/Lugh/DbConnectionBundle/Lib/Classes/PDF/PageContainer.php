<?php

namespace Lugh\DbConnectionBundle\Lib\Classes\PDF;

class PageContainer {
	public function getContainer(){
		global $kernel;
	    if ('AppCache' == get_class($kernel)) {
	         $kernel = $kernel->getKernel();
	    }
	    return $kernel->getContainer();
	}
}