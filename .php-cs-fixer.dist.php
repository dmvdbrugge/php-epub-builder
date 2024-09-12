<?php

declare(strict_types=1);

use Mollie\PhpCodingStandards\PhpCsFixer\Rules;
use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->exclude('devtools')
    ->in(__DIR__);

$ruleOverrides = [
    'no_extra_blank_lines' => [
        'tokens' => [
            'break', 'case', 'continue', 'curly_brace_block', 'default', 'extra', 'parenthesis_brace_block',
            'return', 'square_brace_block', 'throw',
        ],
    ],
];

return (new Config('EpubBuilder'))
    ->setFinder($finder)
    ->setRiskyAllowed(true)
    ->setRules(Rules::getForPhp81($ruleOverrides));
