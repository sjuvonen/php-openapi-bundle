<?php

namespace Juvonet\OpenApiBundle\SchemaDescriber;

use Juvonet\OpenApi\Documentation\Schema;
use Juvonet\OpenApi\SchemaDescriberInterface;

/**
 * Filters inaccessible properties based on serialization groups.
 */
class InaccessiblePropertiesSchemaDescriber implements SchemaDescriberInterface
{
    public function __construct(
        private \Symfony\Component\PropertyInfo\PropertyListExtractorInterface $propertyListExtractor,
    ) {
    }

    public function supports(Schema $schema): bool
    {
        return count($schema->x['groups'] ?? []);
    }

    public function describe(Schema $schema): void
    {
        $accessContext = [
            'serializer_groups' => $schema->x['groups']
        ];

        $exposedProperties = $this->propertyListExtractor->getProperties($schema->context->class, $accessContext);

        foreach ($schema->properties as $key => $property) {
            if (!in_array($property->property, $exposedProperties)) {
                $schema->properties->remove($key);
            }
        }
    }
}
