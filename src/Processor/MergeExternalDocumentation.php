<?php

namespace Juvonet\OpenApiBundle\Processor;

use Juvonet\OpenApi\Documentation\Meta\Items;
use Juvonet\OpenApi\Documentation\AbstractSchema;
use Juvonet\OpenApi\Documentation\OpenApi;
use Juvonet\OpenApi\Documentation\Operation;
use Juvonet\OpenApi\Documentation\PathItem;
use Juvonet\OpenApi\Documentation\Property;
use Juvonet\OpenApi\Documentation\Schema;
use Juvonet\OpenApi\Util\PathItems;
use Symfony\Component\Finder\Finder;

/**
 * Experimental describer for providing docs via external Markdown files.
 *
 * The processor will discover Markdown files in specified locations and tries
 * to populate operations and schemas with loaded information.
 */
class MergeExternalDocumentation
{
    private array $files;
    private array $index;

    public function __construct(
        private \Juvonet\OpenApiBundle\Util\Markdown\SchemaParser $schemaParser,
        private array $paths,
    ) {
    }

    public function __invoke(OpenApi $openApi): void
    {
        $this->buildFileCache();

        foreach (PathItems::extractOperations($openApi->paths) as $operation) {
            $this->tryPopulateOperation($operation);
        }

        foreach ($openApi->components->schemas as $schema) {
            $this->tryPopulateSchema($schema);
        }
    }

    private function tryPopulateOperation(Operation $operation): void
    {
        /**
         * Normalizes URL paths to simple form, e.g.
         *
         * GET /api/users/{user}
         * api-users-user.get.md
         */

        $fkey = strtr($operation->path, [
            '-' => '',
            '{' => '',
            '}' => '',
            '/' => '-',
        ]);

        $fkey = trim($fkey, '-');
        $fkey = strtolower("{$fkey}.{$operation->method}");

        if (isset($this->files[$fkey])) {
            foreach ($this->files[$fkey] as $filePath) {
                $data = $this->indexFile($filePath);
                $this->mergeOperationDocumentation($operation, $data);
            }
        }
    }

    private function tryPopulateSchema(Schema $schema): void
    {
        $fkey = "{$schema->schema}.schema";

        foreach ($this->files[$fkey] ?? [] as $filePath) {
            $blob = $this->schemaParser->parseSchemaFile($filePath);
            $this->mergeSchemaDocumentation($schema, $blob);
        }
    }

    private function buildFileCache(): void
    {
        $this->files = [];
        $this->index = [];

        foreach ($this->loadFiles() as $file) {
            $this->files[$file->getBasename('.md')][] = $file->getPathname();
        }
    }

    private function mergeOperationDocumentation(Operation $operation, object $blob): void
    {
        if (property_exists($blob, 'summary')) {
            $operation->summary = $blob->summary;
        }

        if (property_exists($blob, 'description')) {
            $operation->description = $blob->description;
        }
    }

    private function mergeSchemaDocumentation(AbstractSchema $schema, object $blob): void
    {
        $pmap = [];

        foreach ($blob as $field => $value) {
            if (in_array($field, ['items', 'properties'])) {
                continue;
            }

            $schema->{$field} = $value;
        }

        if (is_array($schema->properties)) {
            foreach ($schema->properties as $property) {
                $pmap[$property->property] = $property;
            }
        }

        foreach ($blob->properties ?? [] as $source) {
            if (!isset($pmap[$source->property])) {
                $property = new Property($source->property);

                $schema->properties->add($property);
                $pmap[$source->property] = $property;
            }

            $target = $pmap[$source->property];

            $this->mergeSchemaDocumentation($pmap[$source->property], $source);
        }

        if (isset($blob->items)) {
            $schema->items = new Items([]);
            $this->mergeSchemaDocumentation($schema->items, $blob->items);
        }
    }

    private function indexFile(string $filePath): object
    {
        /**
         * Parses headings and imports content below them as-is.
         *
         * Useful for embedding markdown-formatted content into API documentation.
         *
         * Markdown files have to conform to strict syntax.
         *
         * OpenAPI sections are declared by using 2nd level headings. Any content
         * may follow. Heading labels must match to OpenAPI operation field names,
         * e.g. "description" or "summary".
         *
         * Right now only the two aforementioned fields are actually supported.
         * Support will increase as we figure out further uses.
         */

        if (!isset($this->index[$filePath])) {
            $contents = trim(file_get_contents($filePath));
            $sections = preg_split('/^(## .+)$/m', $contents, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
            $blob = $this->index[$filePath] = new \stdClass();

            for ($i = 0; $i < count($sections); $i += 2) {
                if (!isset($sections[$i + 1])) {
                    throw new \UnexpectedValueException("Found malformed markdown data in file {$filePath}.");
                }

                $field = mb_strtolower(substr($sections[$i], 3));
                $content = trim($sections[$i + 1]);

                $blob->{$field} = $content;
            }
        }

        return $this->index[$filePath];
    }

    private function loadFiles(): iterable
    {
        if (!$this->paths) {
            return [];
        }

        return (new Finder())->name('*.md')->in($this->paths);
    }
}
