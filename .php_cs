<?php

$finder = PhpCsFixer\Finder::create()
    ->exclude('dependencies')
    ->in(__DIR__);

return PhpCsFixer\Config::create()
    ->setRules([
        '@PSR1' => true,
        '@PSR2' => true,
        'align_multiline_comment' => ['comment_type' => 'phpdocs_like'],
        'array_indentation' => true,
        'array_syntax' => ['syntax' => 'short'],
        'cast_spaces' => ['space' => 'none'],
        // 'class_keyword_remove' => true, // replaces static::class with 'static' (won't work)
        'combine_consecutive_issets' => true,
        'combine_consecutive_unsets' => true,
        'combine_nested_dirname' => true,
        'concat_space' => ['spacing' => 'one'],
        'declare_equal_normalize' => ['space' => 'single'],
        'dir_constant' => true,
        'function_typehint_space' => true,
        'include' => true,
        'logical_operators' => true,
        'lowercase_cast' => true,
        'lowercase_static_reference' => true,
        'magic_constant_casing' => true,
        'magic_method_casing' => true,
        'method_chaining_indentation' => true,
        'modernize_types_casting' => true,
        'multiline_comment_opening_closing' => true,
        'native_function_casing' => true,
        'native_function_type_declaration_casing' => true,
        'new_with_braces' => true,
        'no_blank_lines_after_class_opening' => true,
        'no_blank_lines_after_phpdoc' => true,
        'no_empty_comment' => true,
        'no_empty_phpdoc' => true,
        'no_empty_statement' => true,
        'no_leading_namespace_whitespace' => true,
        'no_mixed_echo_print' => ['use' => 'echo'],
        'no_unneeded_control_parentheses' => true,
        'no_unused_imports' => true,
        'no_useless_return' => true,
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        // 'phpdoc_add_missing_param_annotation' => ['only_untyped' => false], // adds params in the wrong order
        // 'phpdoc_align' => ['align' => 'vertical'], // added in a second step
        'phpdoc_indent' => true,
        // 'phpdoc_scalar' => true, // added in a second step
        'phpdoc_trim' => true,
        'short_scalar_cast' => true,
        'single_line_comment_style' => true,
        'single_quote' => true,
        'ternary_to_null_coalescing' => true,
        'whitespace_after_comma_in_array' => true
    ])
    ->setRiskyAllowed(true)
    ->setFinder($finder);
