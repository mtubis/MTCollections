<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests')
    ->name('*.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setUsingCache(true)
    ->setFinder($finder)
    ->setRules([
        '@PSR12' => true,
        '@PHP82Migration' => true,

        // Readability + consistency
        'array_syntax' => ['syntax' => 'short'],
        'declare_strict_types' => true,
        'strict_param' => true,
        'blank_line_after_opening_tag' => true,
        'no_unused_imports' => true,
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'single_quote' => true,
        'trailing_comma_in_multiline' => ['elements' => ['arrays', 'arguments', 'parameters']],
        'phpdoc_align' => ['align' => 'vertical'],
        'phpdoc_trim' => true,
        'phpdoc_no_empty_return' => true,
        'phpdoc_scalar' => true,

        // Discipline
        'yoda_style' => false,
    ]);