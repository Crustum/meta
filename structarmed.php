<?php
declare(strict_types=1);

use Boundwize\StructArmed\Architecture;

return Architecture::define()
    ->layerPattern('Foundation', [
        '/^Crustum\\\\Meta\\\\(Enums|Exceptions)(\\\\.*)?$/',
    ])
    ->layerPattern('Tags', '/^Crustum\\\\Meta\\\\Tags(\\\\.*)?$/')
    ->layerPattern('Rendering', '/^Crustum\\\\Meta\\\\Rendering(\\\\.*)?$/')
    ->layerPattern('Schema', '/^Crustum\\\\Meta\\\\Schema(\\\\.*)?$/')
    ->layerPattern('Trait', '/^Crustum\\\\Meta\\\\Trait\\\\.*$/')
    ->layerPattern('Routing', '/^Crustum\\\\Meta\\\\Routing\\\\.*$/')
    ->layerPattern('View', '/^Crustum\\\\Meta\\\\View\\\\.*$/')
    ->layerPattern('Facades', [
        '/^Crustum\\\\Meta\\\\(HeadManager|HeadBuilder|HeadData|CurrentHead|ErrorPages|ContainerRegistry|TagRegistry|SchemaType)$/',
    ])
    ->layerPattern('Plugin', '/^Crustum\\\\Meta\\\\MetaPlugin$/')
    ->ruleset([
        'Foundation' => [],
        'Tags' => ['Foundation'],
        'Rendering' => ['Tags', 'Foundation'],
        'Schema' => ['Foundation'],
        'Trait' => ['Tags', 'Foundation'],
        'Routing' => ['Facades'],
        'View' => ['Schema', 'Tags', 'Rendering', 'Facades', 'Trait', 'Foundation'],
        'Facades' => ['Tags', 'Schema', 'Rendering', 'Trait', 'Routing', 'Foundation'],
        'Plugin' => ['+Facades', 'View'],
    ]);
