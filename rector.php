<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\ValueObject\PhpVersion;

return RectorConfig::configure()
    ->withPaths([__DIR__ . '/app', __DIR__ . '/tests'])
    ->withSkip([
        __DIR__ . '/app/Views',
        __DIR__ . '/app/ThirdParty',
        __DIR__ . '/app/Config',
        __DIR__ . '/app/Database/Seeds',
    ])
    ->withPhpVersion(PhpVersion::PHP_84)
    ->withPhpSets(php84: true)
    ->withTypeCoverageLevel(50)
    ->withDeadCodeLevel(50)
    ->withCodeQualityLevel(50)
    ->withImportNames(removeUnusedImports: true);
