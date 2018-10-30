<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraints as Assert;

class SecurityRequestParametersValidator extends BaseRequestParametersValidator
{
    /**
     * @return array
     */
    public function getConstraints(): array 
    {
        return [
            new Assert\Collection([
                'fields' => [
                    'user' => [
                        new Assert\NotBlank(),
                        new Assert\Type(['type' => 'string'])
                    ],
                    'password' => [
                        new Assert\NotBlank(),
                        new Assert\Type(['type' => 'string'])
                    ]
                ], 
                'allowMissingFields' => false,
                'allowExtraFields' => false
            ]),
        ];
    }
}
