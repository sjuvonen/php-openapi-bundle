<?php

namespace Juvonet\OpenApiBundle;

use Symfony\Component\PropertyInfo\Type;

interface TypeMapInterface
{
    public function has(Type|string $type): bool;
    public function get(Type|string $type): array;
}
