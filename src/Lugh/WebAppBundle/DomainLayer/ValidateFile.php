<?php

namespace Lugh\WebAppBundle\DomainLayer;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ValidateFile
{

    private $container, $serializer, $file;
    private $mimes = [
        '*' => 'application/vnd.ms-office',
        'doc' => 'application/msword', //Microsoft Word	
        'xls' => 'application/vnd.ms-excel', //Microsoft Excel	
        'ppt' => 'application/vnd.ms-powerpoint', //Microsoft PowerPoint	
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', //Microsoft Word (OpenXML)
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', //Microsoft Excel (OpenXML)	
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation', //Microsoft PowerPoint (OpenXML)	
        'pdf' => 'application/pdf', //PDF
        'txt' => 'text/plain', //Text, (generally ASCII or ISO 8859-n)	
        'ǵif' => 'image/gif',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png'// ALL Images
    ];

    const TXT_EXTENSION = 'txt';
    const MAX_SIZE_BYTES = 1048576;// 1MB //41943040;//40mb 

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->serializer = $this->container->get('jms_serializer');
    }

    public function isValid(UploadedFile $file)
    {
        $this->file = $file;

        $error_msg[] = $this->isMimeType();
        $error_msg[] = $this->isTxtExtension();
        $error_msg[] = $this->isMaxSize();
//        $msg = implode(PHP_EOL, array_filter($error_msg));

        return !empty(array_filter($error_msg)) ? $this->responseError(array_filter($error_msg)) : $this->responseSuccess();
    }

    private function isMimeType()
    {
        $error = null;
        if (!in_array($this->file->getMimeType(), $this->mimes)) {
            //$error = 'files with "' . $this->file->getClientOriginalExtension() . '" extension not allowed';
            $error = "id00518_home:app:registro:error-file-extension";
         }

        return $error;
    }

    private function isTxtExtension()
    {
        $error = null;
        if ($this->file->getMimeType() == $this->mimes[self::TXT_EXTENSION]) {
            if (strtolower($this->file->getClientOriginalExtension()) != self::TXT_EXTENSION) {
                //$error = 'only text file with extension "' . self::TXT_EXTENSION . '" is allowed';
                $error = "id00519_home:app:registro:error-file-extension-txt";
            }
        }

        return $error;
    }

    private function isMaxSize()
    {
        $error = null;
        if ($this->file->getSize() > self::MAX_SIZE_BYTES) {
            //$error = 'file size cannot exceed ' . number_format(self::MAX_SIZE_BYTES / 1048576, 0) . 'MB';
            $error = "id00520_home:app:registro:error-file-size";
        }

        return $error;
    }

    private function responseSuccess()
    {
        $response = new Response();
        $response->setStatusCode(200);

        return $response;
    }

    private function responseError($msg)
    {
        $response = new Response($this->serializer->serialize(array('error' => $msg), 'json'));
        $response->setStatusCode(400);

        return $response;
    }

}
