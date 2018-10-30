<?php

namespace App\Service;

use App\Transformer\ViolationDataTransformer;
use App\Validator\RequestParametersValidatorInterface;
use Doctrine\ORM\EntityManagerInterface;

class BaseManager
{
    /** @var EntityManagerInterface $entityManager */
    protected $entityManager;
    
    /** @var RequestParametersValidatorInterface $parametersValidator */
    protected $parametersValidator;
    
    /** @var ViolationDataTransformer $violationDataTransformer */
    protected $violationDataTransformer;
    
    public function __construct(
        EntityManagerInterface $entityManager, 
        RequestParametersValidatorInterface $parametersValidator,
        ViolationDataTransformer $violationDataTransformer
    ) {
        $this->entityManager       = $entityManager;
        $this->parametersValidator = $parametersValidator;
        $this->violationDataTransformer = $violationDataTransformer;
    }
    
    /**
     * @return RequestParametersValidatorInterface
     */
    public function getValidator(): RequestParametersValidatorInterface
    {
        return $this->parametersValidator;
    }
}