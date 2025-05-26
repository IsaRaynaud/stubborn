<?php

namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

class CentimeToEuroTransformer implements DataTransformerInterface
{
    public function transform($value): ?string
    {
        if ($value === null) {
            return null;
        }

        return number_format($value / 100, 2, '.', '');
    }

    public function reverseTransform($value): ?int
    {
        if ($value === null) {
            return null;
        }

        return (int) round($value * 100);
    }
}
