<?php

namespace Juvonet\OpenApiBundle\PropertyDescriber;

use Juvonet\OpenApi\Documentation\Property;
use Juvonet\OpenApi\PropertyDescriberInterface;

class SymfonyAccessPropertyDescriber implements PropertyDescriberInterface
{
    public function __construct(
        private \Symfony\Component\PropertyInfo\PropertyAccessExtractorInterface $propertyAccessExtractor,
    ) {
    }

    public function supports(Property $property): bool
    {
        return $property->context->class !== null;
    }

    public function describe(Property $property): void
    {
        $readable = $this->propertyAccessExtractor->isReadable($property->context->class, $property->property);
        $writable = $this->propertyAccessExtractor->isWritable($property->context->class, $property->property);

        if ($readable !== false && $writable === false) {
            $property->readOnly = true;
        }

        if ($writable !== false && $readable === false) {
            $property->writeOnly = true;
        }
    }
}
