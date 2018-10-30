<?php

namespace App\Validator;

use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validation;


abstract class BaseRequestParametersValidator implements RequestParametersValidatorInterface
{
    /**
     * @param array $parameters
     *
     * @return ConstraintViolationListInterface
     */
    public function validate(array $parameters): ConstraintViolationListInterface
    {
        $validator = Validation::createValidator();
        $violations = $validator->validate($parameters, $this->getConstraints());
        
        return $violations;
    }

    /**
     * @return array
     */
    abstract protected function getConstraints(): array;
}
