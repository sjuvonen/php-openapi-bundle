<?php

namespace Juvonet\OpenApiBundle\OperationDescriber;

use Juvonet\OpenApi\Documentation\Operation;
use Juvonet\OpenApi\OperationDescriberInterface;

class SymfonyDescriptionOperationDescriber implements OperationDescriberInterface
{
    public function __construct(
        private \Symfony\Component\PropertyInfo\PropertyDescriptionExtractorInterface $propertyDescriptionExtractor,
    ) {
    }

    public function supports(Operation $operation): bool
    {
        if (!$operation->context->class) {
            return false;
        }

        if ($operation->context->method === '__invoke') {
            return false;
        }

        if ($operation->summary && $operation->description) {
            return false;
        }

        return true;
    }

    public function describe(Operation $operation): void
    {
        if (!$operation->summary) {
            $summary = $this->propertyDescriptionExtractor->getShortDescription(
                $operation->context->class,
                $operation->context->method
            );

            if ($summary) {
                $operation->summary = $summary;
            }
        }

        if (!$operation->description) {
            $description = $this->propertyDescriptionExtractor->getLongDescription(
                $operation->context->class,
                $operation->context->method
            );

            if ($description) {
                $operation->description = $description;
            }
        }
    }
}
