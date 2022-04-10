<?php

namespace Juvonet\OpenApiBundle\Controller;

use Juvonet\OpenApi\Serializer\Exception\IgnoreValueException;
use Symfony\Component\HttpFoundation\Response;

class DocumentationAction
{
    public function __construct(
        private \Juvonet\OpenApi\DocumentationGeneratorInterface $documentator,
        private \Juvonet\OpenApi\SerializerInterface $serializer,
    ) {
    }

    public function __invoke()
    {
        set_time_limit(300);

        $documentation = $this->documentator->generate();

        // exit('OK');

        $serialized = $this->serializer->serialize($documentation);

        // exit('OK 2');

        return new Response($serialized, Response::HTTP_OK, [
            'Content-Type' => 'application/json',
            'Cache-Control' => 'max-age=300'
        ]);
    }
}
