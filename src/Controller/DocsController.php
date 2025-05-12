<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DocsController
{
    #[Route('/docs', name: 'api_docs', methods: ['GET'])]
    public function __invoke(): Response
    {
        return new Response(file_get_contents(__DIR__.'/../../public/docs/index.html'));
    }
}
