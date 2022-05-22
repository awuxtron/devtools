<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$config = [
    'working_dir' => $workingDir = getcwd(),
    'paths' => array_map(fn ($p) => "{$workingDir}/{$p}", [
        'app',
        'src',
        'tests',
        'php',
    ]),
    'rules' => [
        '@PhpCsFixer' => true,
        'new_with_braces' => ['named_class' => false, 'anonymous_class' => false],
        'concat_space' => ['spacing' => 'one'],
        'yoda_style' => ['equal' => false, 'identical' => false, 'less_and_greater' => false],
        'no_superfluous_phpdoc_tags' => false,
        'single_line_comment_style' => false,
        'global_namespace_import' => ['import_classes' => true, 'import_constants' => true, 'import_functions' => true],
        'control_structure_continuation_position' => true,
        'phpdoc_line_span' => true,
        'date_time_create_from_format_call' => true,
        'declare_parentheses' => true,
        'nullable_type_declaration_for_default_null_value' => true,
    ],
];

if (file_exists($custom = "{$config['working_dir']}/.php_cs")) {
    $config = array_replace_recursive($config, $custom = require $custom);

    if (!empty($custom['paths'])) {
        $config['paths'] = $custom['paths'];
    }
}

if (!isset($finder)) {
    $finder = Finder::create()
        ->in(array_filter($config['paths'], 'file_exists'))
        ->name('*.php')
        ->ignoreUnreadableDirs()
        ->ignoreDotFiles(true)
        ->ignoreVCS(true)
    ;
}

return (new Config)->setFinder($finder)->setRules($config['rules']);
