<?php

namespace Juvonet\OpenApiBundle\PropertyDescriber;

use Juvonet\OpenApi\Documentation\Property;
use Juvonet\OpenApi\PropertyDescriberInterface;

class SymfonyDescriptionPropertyDescriber implements PropertyDescriberInterface
{
    public function __construct(
        private \Symfony\Component\PropertyInfo\PropertyDescriptionExtractorInterface $propertyDescriptionExtractor,
    ) {
    }

    public function supports(Property $property): bool
    {
        if (!$property->context->class) {
            return false;
        }

        if ($property->title && $property->description) {
            return false;
        }

        return true;
    }

    public function describe(Property $property): void
    {
        if (!$property->title) {
            $title = $this->propertyDescriptionExtractor->getShortDescription(
                $property->context->class,
                $property->property
            );

            if ($title) {
                $property->title = $title;
            }
        }

        if (!$property->description) {
            $description = $this->propertyDescriptionExtractor->getLongDescription(
                $property->context->class,
                $property->property
            );

            if ($description) {
                $property->description = $description;
            }
        }
    }
}
