<?php

namespace App\Validator;

use Symfony\Component\Validator\ConstraintViolationListInterface;

interface RequestParametersValidatorInterface
{
    public function validate(array $parameters): ConstraintViolationListInterface;
}