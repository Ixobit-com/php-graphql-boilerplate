<?php

declare(strict_types=1);

$finder = (new PhpCsFixer\Finder())
    ->in('./src');

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony'               => true,
        'binary_operator_spaces' => [
            'operators' => [
                '='  => 'align',
                '=>' => 'align',
            ],
        ],
        'declare_strict_types'   => true,
    ])
    ->setFinder($finder)
;
