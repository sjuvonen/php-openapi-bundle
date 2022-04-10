<?php

namespace Juvonet\OpenApiBundle\PropertyDescriber;

use Juvonet\OpenApi\Documentation\Property;
use Juvonet\OpenApi\PropertyDescriberInterface;
use Symfony\Component\PropertyInfo\Type;

class SymfonyTypePropertyDescriber implements PropertyDescriberInterface
{
    public function __construct(
        private \Juvonet\OpenApiBundle\PropertyDescriber\Type\TypeDescriberInterface $typeDescriber,
        private \Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface $propertyTypeExtractor,
    ) {
    }

    public function supports(Property $property): bool
    {
        return $property->context->class !== null;
    }

    public function describe(Property $property): void
    {
        $types = $this->propertyTypeExtractor->getTypes($property->context->class, $property->property) ?? [];

        foreach ($types as $type) {
            if ($this->typeDescriber->supports($type)) {
                $this->typeDescriber->describe($property, $type);
            }
        }
    }
}
