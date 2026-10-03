<?php

declare(strict_types=1);

$finder = (new PhpCsFixer\Finder())
    ->in([__DIR__ . '/app', __DIR__ . '/tests', __DIR__ . '/public'])
    ->exclude(['ThirdParty', 'Views'])
    ->name('*.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PER-CS2.0' => true,
        '@PER-CS2.0:risky' => true,
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'concat_space' => ['spacing' => 'one'],
        'declare_strict_types' => true,
        'global_namespace_import' => [
            'import_classes' => true,
            'import_constants' => false,
            'import_functions' => false,
        ],
        'native_function_invocation' => false,
        'phpdoc_align' => ['align' => 'left'],
        'phpdoc_separation' => false,
        'yoda_style' => false,
    ])
    ->setFinder($finder);
