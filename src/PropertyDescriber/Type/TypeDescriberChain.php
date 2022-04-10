<?php

namespace Juvonet\OpenApiBundle\PropertyDescriber\Type;

use Juvonet\OpenApi\Documentation\Property;
use Symfony\Component\PropertyInfo\Type;

final class TypeDescriberChain implements TypeDescriberInterface
{
    public function __construct(
        private iterable $describers
    ) {
    }

    public function supports(Type $type): bool
    {
        foreach ($this->describers as $describer) {
            if ($describer->supports($type)) {
                return true;
            }
        }

        return false;
    }

    public function describe(Property $property, Type $type): void
    {
        $supported = false;

        foreach ($this->describers as $describer) {
            if ($describer->supports($type)) {
                $describer->describe($property, $type);
                $supported = true;
            }
        }

        if (!$supported) {
            throw new \UnexpectedValueException('Passed type is not supported.');
        }
    }
}
