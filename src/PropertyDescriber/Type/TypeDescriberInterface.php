<?php

namespace Juvonet\OpenApiBundle\PropertyDescriber\Type;

use Juvonet\OpenApi\Documentation\Property;
use Symfony\Component\PropertyInfo\Type;

interface TypeDescriberInterface
{
    public function supports(Type $type): bool;
    public function describe(Property $property, Type $type): void;
}
