<?php

namespace App\Form;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class DateTimeTransformer implements DataTransformerInterface
{
    public function transform($date)
    {
        if ($date === null) {
            return '';
        }

        return $date->format('Y-m-d H:i:s');
    }

    public function reverseTransform($dateString)
    {
        try {
            $date = new \DateTime($dateString);
        } catch (\Exception $e) {
            throw new TransformationFailedException('Invalid date format');
        }

        return $date;
    }
}
