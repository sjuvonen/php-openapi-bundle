<?php

namespace Juvonet\OpenApiBundle\PropertyDescriber;

use Juvonet\OpenApi\Documentation\Property;
use Juvonet\OpenApi\PropertyDescriberInterface;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\LessThan;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Range;

class SymfonyConstraintPropertyDescriber implements PropertyDescriberInterface
{
    public function __construct(
        private \Symfony\Component\Validator\Validator\ValidatorInterface $validator,
    ) {
    }

    public function supports(Property $property): bool
    {
        if (!$property->context->class) {
            return false;
        }

        return $this->validator->hasMetadataFor($property->context->class);
    }

    public function describe(Property $property): void
    {
        $metadata = $this->validator->getMetadataFor($property->context->class);
        $constraints = $metadata->members[$property->property][0]->constraints ?? [];

        foreach ($constraints as $constraint) {
            switch (true) {
                case $constraint instanceof Choice:
                    if ($choices = $this->getChoices($constraint, $property->context->class)) {
                        $property->enum = $choices;
                    }
                    break;

                case $constraint instanceof GreaterThan:
                    $property->minimum = $constraint->value ?? null;
                    break;

                case $constraint instanceof Length:
                    $property->minLength = $constraint->min ?? null;
                    $property->maxLength = $constraint->max ?? null;
                    break;

                case $constraint instanceof LessThan:
                    $property->maximum = $constraint->value ?? null;
                    break;

                case $constraint instanceof NotBlank:
                    $property->nullable = $constraint->allowNull;
                    break;

                case $constraint instanceof NotNull:
                    $property->nullable = false;
                    break;

                case $constraint instanceof Range:
                    $property->minimum = $constraint->min ?? null;
                    $property->maximum = $constraint->max ?? null;
                    break;
            }
        }
    }

    private function getChoices(Choice $constraint, string $className): ?array
    {
        if ($constraint->choices) {
            return array_values($constraint->choices);
        }

        if ($constraint->callback) {
            $callback = is_string($constraint->callback) ? [$className, $constraint->callback] : $constraint->callback;

            if (is_callable($callback)) {
                return array_values($callback());
            } else {
                /**
                 * Symfony allows some hacks in this parameter so it isn't always necessarily
                 * properly callable.
                 */
                return [];
            }
        }

        return null;
    }
}
