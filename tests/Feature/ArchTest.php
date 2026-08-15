<?php
declare(strict_types=1);

use Crustum\Meta\Schema\SchemaFactory;
use Crustum\Meta\Schema\SchemaObject;
use Crustum\Meta\Schema\SchemaValidator;
use Crustum\Meta\Tags\TagBuilder;

it('uses strict types and avoids debug helpers in shipped source', function (): void {
    $root = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'src';
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
    );

    foreach ($iterator as $fileInfo) {
        if ($fileInfo->getExtension() !== 'php') {
            continue;
        }

        $contents = (string)file_get_contents($fileInfo->getPathname());

        expect($contents)->toContain('declare(strict_types=1);')
            ->and($contents)->not->toMatch('/\b(die|dd|dump|var_dump)\s*\(/');
    }
});

it('tag builders extend the tag builder base', function (): void {
    foreach (glob(dirname(__DIR__, 2) . '/src/Tags/*.php') ?: [] as $file) {
        $class = 'Crustum\\Meta\\Tags\\' . basename($file, '.php');

        if (!class_exists($class)) {
            continue;
        }

        expect(is_a($class, TagBuilder::class, true))->toBeTrue();
    }
});

it('schema objects extend the schema object base', function (): void {
    foreach (glob(dirname(__DIR__, 2) . '/src/Schema/*.php') ?: [] as $file) {
        $class = 'Crustum\\Meta\\Schema\\' . basename($file, '.php');
        if (!class_exists($class)) {
            continue;
        }

        if (in_array($class, [SchemaFactory::class, SchemaValidator::class], true)) {
            continue;
        }

        expect(is_a($class, SchemaObject::class, true))->toBeTrue();
    }
});

it('enums are backed string enums', function (): void {
    foreach (glob(dirname(__DIR__, 2) . '/src/Enums/*.php') ?: [] as $file) {
        $class = 'Crustum\\Meta\\Enums\\' . basename($file, '.php');

        if (!enum_exists($class)) {
            continue;
        }

        expect((new ReflectionEnum($class))->isBacked())->toBeTrue();
    }
});
