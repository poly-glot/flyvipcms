<?php

declare(strict_types=1);

$minimum = (float) ($argv[1] ?? 55);
$clover = __DIR__ . '/../build/logs/clover.xml';

$project = simplexml_load_file($clover)->project->metrics ?? null;

if ($project === null) {
    fwrite(\STDERR, "No coverage report at {$clover}\n");
    exit(1);
}

$covered = (int) $project['coveredstatements'];
$total = (int) $project['statements'];
$percent = $total === 0 ? 0.0 : $covered / $total * 100;

printf("Statement coverage %.2f%% (minimum %.2f%%)\n", $percent, $minimum);
exit($percent >= $minimum ? 0 : 1);
