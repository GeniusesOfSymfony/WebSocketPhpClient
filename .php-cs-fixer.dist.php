<?php

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        '@PHP71Migration' => true,
        '@PHP71Migration:risky' => true,
        '@PHPUnit84Migration:risky' => true,
        'blank_line_after_opening_tag' => true,
        'declare_strict_types' => false,
        'fopen_flags' => false,
        'ordered_imports' => true,
        'protected_to_private' => true,
    ])
    ->setRiskyAllowed(true)
    ->setFinder(
        (new PhpCsFixer\Finder())
            ->in(__DIR__.'/src')
            ->in(__DIR__.'/tests')
    )
;
