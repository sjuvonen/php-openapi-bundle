<?php

namespace Framework\OpenApiExtensionBundle\Util\Markdown;

class SchemaParser
{
    public function parseSchemaFile(string $filePath): object
    {
        return $this->parseSchema(file_get_contents($filePath));
    }

    public function parseSchema(string $markdown): object
    {
        $parsed = Parsedown::parseText($markdown);
        $blob = new \stdClass();

        foreach ($parsed as $i => $element) {
            if ($element['name'] === 'h2') {
                $prop = lcfirst($element['handler']['argument']);
                $method = 'parse' . $element['handler']['argument'];
                $blob->{$prop} = $this->{$method}(array_slice($parsed, $i + 1));
            }
        }

        return $blob;
    }

    private function parseProperties(array $elements): array
    {
        $nodes = [];
        $node = null;
        $skipNextTable = false;

        foreach ($elements as $i => $element) {
            switch ($element['name']) {
                case 'h3':
                    $prop = lcfirst($element['handler']['argument']);
                    $node = $nodes[] = new \stdClass();
                    $node->property = $prop;
                    break;

                case 'table':
                    if ($skipNextTable) {
                        $skipNextTable = false;
                        break;
                    }

                    $node->type = 'object';
                    $node->properties = [];
                    $node->properties = $this->parsePropertiesFromTable($element);
                    break;

                case 'h4':
                    if (isset($elements[$i + 1]['handler'])) {
                        $prop = lcfirst($element['handler']['argument']);
                        $node->{$prop} = $elements[$i + 1]['handler']['argument'];
                    }

                    if ($elements[$i + 1]['name'] === 'table') {
                        $skipNextTable = true;
                        $prop = lcfirst($element['handler']['argument']);

                        if ($prop === 'items') {
                            $node->items = (object)[
                                'type' => 'object',
                                'properties' => $this->parsePropertiesFromTable($elements[$i + 1])
                            ];
                        } else {
                            $node->{$prop} = $this->parsePropertiesFromTable($elements[$i + 1]);
                        }
                    }
                    break;
            }
        }

        return $nodes;
    }

    private function parsePropertiesFromTable(array $table): array
    {
        $nodes = [];

        $fields = array_map(
            fn (array $th) => strtolower($th['handler']['argument']),
            $table['elements'][0]['elements'][0]['elements']
        );

        foreach ($table['elements'][1]['elements'] as $tr) {
            $node = new \stdClass();

            foreach ($tr['elements'] as $i => $td) {
                $node->{$fields[$i]} = $td['handler']['argument'];
            }

            $nodes[] = $node;
        }

        return $nodes;
    }
}
