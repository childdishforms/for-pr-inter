<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/doc')]
class ApiDocController
{
    /**
     * Возвращает HTML-страницу с интерфейсом Swagger UI.
     */
    #[Route('', name: 'api_doc_controller', methods: ['GET'])]
    public function swaggerUI(): Response
    {
        return new Response(<<<'HTML'
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <title>Contacts API</title>
                <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5/swagger-ui.css">
            </head>
            <body>
                <div id="swagger-ui"></div>
                <script src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-bundle.js"></script>
                <script>
                    SwaggerUIBundle({
                        url: '/api/doc.json',
                        dom_id: '#swagger-ui',
                        tryItOutEnabled: true,
                        defaultModelsExpandDepth: -1
                    });
                </script>
            </body>
            </html>
            HTML);
    }
}
