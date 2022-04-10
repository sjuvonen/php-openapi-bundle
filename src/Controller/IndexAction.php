<?php

namespace Juvonet\OpenApiBundle\Controller;

use Symfony\Component\HttpFoundation\Response;

class IndexAction
{
    public function __construct(
        private \Juvonet\OpenApi\DocumentationGeneratorInterface $documentator,
        private \Symfony\Component\Routing\Generator\UrlGeneratorInterface $urlGenerator,
        private \Twig\Environment $renderer
    ) {
    }

    public function __invoke()
    {
        $content = $this->renderer->render('@JuvonetOpenApi/documentation.html.twig', [
            'docs_url' => $this->urlGenerator->generate('juvonet_openapi.docs')
        ]);

        return new Response($content, Response::HTTP_OK, [
            'Content-Type' => 'text/html'
        ]);
    }
}
