<?php

namespace App;

use OpenApi\Attributes as OA;

#[OA\Info(
    title:        'Stubborn',
    version:      '1.0.0',
    description:  "Documentation générée automatiquement pour l'application Stubborn.",
    contact:      new OA\Contact(
        name:  'Support Stubborn',
        email: 'contact@lewebpluschouette.fr',
    ),
    license:      new OA\License(
        name: 'MIT',
        url:  'https://opensource.org/licenses/MIT'
    )
)]

final class OpenApiInfo {}