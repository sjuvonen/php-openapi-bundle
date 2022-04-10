<?php

namespace Juvonet\OpenApiBundle\PropertyDescriber;

use Juvonet\OpenApi\Documentation\Property;
use Juvonet\OpenApi\PropertyDescriberInterface;

class DoctrineTypePropertyDescriber implements PropertyDescriberInterface
{
    public function __construct(
        private \Doctrine\Persistence\ManagerRegistry $doctrine,
        private \Juvonet\OpenApiBundle\PropertyDescriber\Type\TypeDescriberInterface $typeDescriber,
    ) {
    }

    public function supports(Property $property): bool
    {
        return $this->doctrine->getManagerForClass((string)$property->context->class) !== null;
    }

    public function describe(Property $property): void
    {
        $classMetadata = $this->doctrine
            ->getManagerForClass($property->context->class)
            ->getClassMetadata($property->context->class)
            ;

        $fieldType = $classMetadata->getTypeOfField($property->property);

        switch ($fieldType) {
            case 'date':
            case 'date_immutable':
                $property->type = 'string';
                $property->format = 'date';
                break;

            case 'datetime':
            case 'datetime_immutable':
                $property->type = 'string';
                $property->format = 'date-time';
                break;

            case 'time':
            case 'time_immutable':
                $property->type = 'string';
                $property->format = 'time';
                break;
        }
    }
}
