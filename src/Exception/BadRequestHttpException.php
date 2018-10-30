<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException as SymfonyBadRequestHttpException;

class BadRequestHttpException extends SymfonyBadRequestHttpException
{
    /** @var array $data */
    private $data = [];

    /**
     * @param array      $data
     * @param string     $message  The internal exception message
     * @param \Exception $previous The previous exception
     * @param int        $code     The internal exception code
     * @param array      $headers
     */
    public function __construct($data = [], string $message = null, \Exception $previous = null, int $code = 0, array $headers = array())
    {
        parent::__construct($message, $previous, $code, $headers);
           
        $this->setData($data);
    }

    public function setData($data = [])
    {
        $this->data = $data;
    }
    
    public function getData()
    {
        return $this->data;
    }
}
