<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests')
    ->name('*.php');

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        '@PHP82Migration' => true,
        'declare_strict_types' => true,
        'array_syntax' => ['syntax' => 'short'],
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'no_unused_imports' => true,
        'trailing_comma_in_multiline' => true,
        'phpdoc_order' => true,
        'phpdoc_trim' => true,
        'no_superfluous_phpdoc_tags' => ['remove_inheritdoc' => true],
        'modernize_types_casting' => true,
        'explicit_string_variable' => true,
        'method_chaining_indentation' => true,
        'no_extra_blank_lines' => true,
        'blank_line_after_namespace' => true,
    ])
    ->setFinder($finder)
    ->setRiskyAllowed(true);
