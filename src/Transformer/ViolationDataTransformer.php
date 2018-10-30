<?php

namespace App\Transformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ViolationDataTransformer implements DataTransformerInterface
{
    /**
     * @param ConstraintViolationListInterface $value The value in the original representation
     *
     * @return mixed The value in the transformed representation
     *
     * @throws TransformationFailedException when the transformation fails
     */
    public function transform($value): ?array 
    {
        if ($value) {

            $messages = [];

            /** @var ConstraintViolation $violation */
            foreach ($value as $violation) {
                $messages[$violation->getPropertyPath()] = $violation->getMessage();
            }

            return $messages;
        }

        return null;
    }

    /**
     * @param mixed $value The value in the transformed representation
     *
     * @return mixed The value in the original representation
     *
     * @throws TransformationFailedException when the transformation fails
     */
    public function reverseTransform($value)
    {
        // No need for reverse transform
        throw new TransformationFailedException('ViolationDataTransformer::reverseTransform() is not implemented.');
    }
}