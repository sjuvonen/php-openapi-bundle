<?php

namespace Juvonet\OpenApiBundle\PropertyDescriber\Type;

use Juvonet\OpenApi\Documentation\Property;
use Symfony\Component\PropertyInfo\Type;

class NativeTypeDescriber implements TypeDescriberInterface
{
    public function __construct(
        private \Juvonet\OpenApiBundle\TypeMapInterface $typeMap
    ) {
    }

    public function supports(Type $typeInfo): bool
    {
        return $this->typeMap->has($typeInfo);
    }

    public function describe(Property $property, Type $typeInfo): void
    {
        // if ($this->shouldIgnore($typeInfo, $property)) {
        //     return;
        // }

        [$type, $format] = $this->typeMap->get($typeInfo);

        $property->type = $type;
        $property->format = $format;
    }

    private function shouldIgnore(Type $typeInfo, Property $property): bool
    {
        if ($typeInfo->getBuiltinType() === Type::BUILTIN_TYPE_OBJECT) {
            return $property->type !== null;
        }

        return false;
    }
}
