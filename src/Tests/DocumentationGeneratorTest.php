<?php

namespace Juvonet\OpenApiBundle\Tests;

use Juvonet\OpenApi\DocumentationGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DocumentationGeneratorTest extends KernelTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
    }

    public function testDocumentation()
    {
        /**
         * NelmioApiDocBundle is prone to crashing with tiny schema irregularities
         * so we need to verify that it is able to compile the docs.
         */

        $generator = self::getContainer()->get(DocumentationGeneratorInterface::class);
        $docs = $generator->generate();

        $this->assertTrue(isset($docs->openapi));
    }
}
