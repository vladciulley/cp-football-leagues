<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraints as Assert;

class TeamRequestParametersValidator extends BaseRequestParametersValidator
{
    /**
     * @return array
     */
    public function getConstraints(): array
    {
        return [
            new Assert\Collection([
                'fields' => [
                    'name' => [
                        new Assert\NotBlank(),
                        new Assert\Type(['type' => 'string'])
                    ],
                    'strip' => [
                        new Assert\NotBlank(),
                        new Assert\Type(['type' => 'string'])
                    ],
                    'league_id' => [
                        new Assert\NotBlank(),
                        new Assert\Type(['type' => 'integer'])
                    ]
                ], 
                'allowMissingFields' => false,
                'allowExtraFields' => false
            ]),
        ];
    }
}
