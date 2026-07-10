<?php

declare(strict_types=1);

$clover = $argv[1] ?? 'coverage.xml';
$minimum = (float) ($argv[2] ?? 85.0);

if (!file_exists($clover)) {
    fwrite(STDERR, "Coverage file not found: {$clover}\n");
    fwrite(STDERR, "Run `composer test:coverage` with a coverage driver enabled.\n");
    exit(1);
}

$xml = simplexml_load_file($clover);
$metrics = $xml->project->metrics;
$statements = (int) $metrics['statements'];

if ($statements === 0) {
    fwrite(STDERR, "No statements found in coverage report.\n");
    exit(1);
}

$covered = (int) $metrics['coveredstatements'];
$pct = round($covered / $statements * 100, 2);

echo "Line coverage: {$pct}% ({$covered}/{$statements} statements)\n";

if ($pct < $minimum) {
    fwrite(STDERR, "Coverage {$pct}% is below minimum {$minimum}%\n");
    exit(1);
}

echo "OK — coverage meets the {$minimum}% minimum.\n";
