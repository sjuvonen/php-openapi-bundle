<?php

namespace Juvonet\OpenApiBundle\PropertyDescriber\Type;

use Juvonet\OpenApi\Documentation\Extra\Ref;
use Juvonet\OpenApi\Documentation\Property;
use Symfony\Component\PropertyInfo\Type;

class ObjectTypeDescriber implements TypeDescriberInterface
{
    public function supports(Type $type): bool
    {
        return !$type->isCollection() && $type->getClassName();
    }

    public function describe(Property $property, Type $type): void
    {
        if (!$property->type) {
            $property->ref = new Ref($type->getClassName());
        }
    }
}
