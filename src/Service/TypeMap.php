<?php

namespace Juvonet\OpenApiBundle\Service;

use Juvonet\OpenApiBundle\TypeMapInterface;
use Symfony\Component\PropertyInfo\Type;

final class TypeMap implements TypeMapInterface
{
    private array $types = [
        Type::BUILTIN_TYPE_ARRAY => 'array',
        Type::BUILTIN_TYPE_BOOL => 'boolean',
        Type::BUILTIN_TYPE_FLOAT => 'number',
        Type::BUILTIN_TYPE_INT => 'integer',
        Type::BUILTIN_TYPE_ITERABLE => 'array',
        Type::BUILTIN_TYPE_OBJECT => 'object',
        Type::BUILTIN_TYPE_RESOURCE => 'string',
        Type::BUILTIN_TYPE_STRING => 'string',
    ];

    private array $formats = [
        Type::BUILTIN_TYPE_FLOAT => 'double',
        Type::BUILTIN_TYPE_RESOURCE => 'binary',
    ];

    private array $classes = [
        \DateInterval::class => ['string', 'date-interval'],
        \DateTimeInterface::class => ['string', 'date-time'],
        \SplFileInfo::class => ['string', 'binary'],
    ];

    private array $classCache = [];

    public function has(Type|string $type): bool
    {
        if ($type instanceof Type) {
            $type = $type->getClassName() ?? $type->getBuiltinType();
        }

        if (isset($this->types[$type])) {
            return true;
        }

        if (isset($this->classCache[$type])) {
            return true;
        }

        foreach ($this->classes as $mappedClass => $typeInfo) {
            if (is_a($type, $mappedClass, true)) {
                $this->classCache[$type] = $typeInfo;

                return true;
            }
        }

        return false;
    }

    public function get(Type|string $type): array
    {
        if ($type instanceof Type) {
            $type = $type->getClassName() ?? $type->getBuiltinType();
        }

        if (isset($this->types[$type])) {
            $type = $this->types[$type];
            $format = $this->formats[$type] ?? null;

            return [$type, $format];
        }

        if (class_exists($type) || interface_exists($type)) {
            if (isset($this->classCache[$type])) {
                return $this->classCache[$type];
            }

            foreach ($this->classes as $mappedClass => $typeInfo) {
                if (is_a($type, $mappedClass, true)) {
                    $this->classCache[$type] = $typeInfo;

                    return $typeInfo;
                }
            }

            $this->classCache[$type] = $this->get(Type::BUILTIN_TYPE_OBJECT);

            return $this->classCache[$type];
        }

        throw new \OutOfBoundsException("Unknown or unsupported native type name '{$type}' passed.");
    }
}
