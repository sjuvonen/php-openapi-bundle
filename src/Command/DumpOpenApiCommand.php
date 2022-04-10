<?php

namespace Juvonet\OpenApiBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DumpOpenApiCommand extends Command
{
    protected static $defaultName = 'openapi:dump';
    protected static $defaultDescription = 'Dumps OpenAPI documentation into a file.';

    public function __construct(
        private \Juvonet\OpenApi\DocumentationGeneratorInterface $documentationGenerator,
        private \Juvonet\OpenApi\SerializerInterface $serializer,
        ?string $name = null,
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->addOption('paths', null, InputOption::VALUE_NONE, 'Dump paths only.')
            ->addOption('schemas', null, InputOption::VALUE_NONE, 'Dump components only.')
            ->addOption('schema', null, InputOption::VALUE_REQUIRED, 'Dump named schema only.')
            ->addOption('operation', null, InputOption::VALUE_REQUIRED, 'Dump named operation only.')
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $timeStart = microtime(true);

        $documentation = $this->documentationGenerator->generate();

        switch (true) {
            case $input->getOption('operation'):
                $identifier = $input->getOption('operation');

                foreach ($documentation->paths as $pathItem) {
                    foreach ($pathItem as $operation) {
                        if (isset($operation->operationId) && $operation->operationId === $identifier) {
                            $documentation = $operation;

                            break 3;
                        }
                    }
                }

                throw new \OutOfBoundsException('Operation not found.');

            case $input->getOption('paths'):
                $documentation = $documentation->paths;
                break;

            case $input->getOption('schemas'):
                $documentation = $documentation->components->schemas;
                break;

            case $input->getOption('schema'):
                $identifier = $input->getOption('schema');

                foreach ($documentation->components->schemas as $schema) {
                    if ($schema->schema === $identifier) {
                        $documentation = $schema;

                        break 2;
                    }
                }

                throw new \OutOfBoundsException('Schema not found.');
        }

        // print json_encode($documentation, JSON_PRETTY_PRINT);
        print $this->serializer->serialize($documentation);

        $mem = memory_get_peak_usage(true);

        print "\n\nProcessing time: " . round((microtime(true) - $timeStart) * 1000, 3) . ' ms';
        print "\nMemory usage: " . round($mem / 1024 / 1024, 3) . ' MB';
        print "\n";

        return self::SUCCESS;
    }
}
