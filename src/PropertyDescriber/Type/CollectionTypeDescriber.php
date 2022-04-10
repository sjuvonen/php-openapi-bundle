<?php

namespace Juvonet\OpenApiBundle\PropertyDescriber\Type;

use Doctrine\Common\Collections\Collection;
use Juvonet\OpenApi\Documentation\Meta\Items;
use Juvonet\OpenApi\Documentation\Property;
use Symfony\Component\PropertyInfo\Type;

/**
 * Describers collection properties and the nested items.
 */
class CollectionTypeDescriber implements TypeDescriberInterface
{
    public function __construct(
        private \Juvonet\OpenApiBundle\PropertyDescriber\Type\TypeDescriberInterface $typeDescriber,
    ) {
    }

    public function supports(Type $type): bool
    {
        return $type->isCollection();
    }

    public function describe(Property $property, Type $type): void
    {
        $property->type = 'array';
        $property->items = new Property();

        foreach ($type->getCollectionValueTypes() as $itemType) {
            if ($this->typeDescriber->supports($itemType)) {
                $this->typeDescriber->describe($property->items, $itemType);
            }
        }
    }
}
