<?php

use PhpCsFixer\Finder;
use PhpCsFixer\Runner\Parallel\ParallelConfigFactory;

$finder = Finder::create()
	->exclude('dependencies')
	->exclude('panel/node_modules')
	->in(__DIR__);

return (new PhpCsFixer\Config())
	->setParallelConfig(ParallelConfigFactory::detect())
	->setRules([
		'@PSR12' => true,
		'align_multiline_comment' => ['comment_type' => 'all_multiline'],
		'array_indentation' => true,
		'array_syntax' => ['syntax' => 'short'],
		'assign_null_coalescing_to_coalesce_equal' => true,
		'cast_spaces' => ['space' => 'none'],
		'combine_consecutive_issets' => true,
		'combine_consecutive_unsets' => true,
		'combine_nested_dirname' => true,
		'concat_space' => ['spacing' => 'one'],
		'declare_equal_normalize' => ['space' => 'single'],
		'dir_constant' => true,
		'function_typehint_space' => true,
		'include' => true,
		'logical_operators' => true,
		'magic_constant_casing' => true,
		'magic_method_casing' => true,
		'method_chaining_indentation' => true,
		'modernize_types_casting' => true,
		'multiline_comment_opening_closing' => true,
		'native_function_casing' => true,
		'native_function_type_declaration_casing' => true,
		'new_with_braces' => true,
		'no_blank_lines_after_phpdoc' => true,
		'no_empty_comment' => true,
		'no_empty_phpdoc' => true,
		'no_empty_statement' => true,
		'no_leading_namespace_whitespace' => true,
		'no_mixed_echo_print' => ['use' => 'echo'],
		'no_short_bool_cast' => true,
		'no_superfluous_elseif' => true,
		'no_superfluous_phpdoc_tags' => ['allow_unused_params' => true],
		'no_unneeded_braces' => true,
		'no_unneeded_control_parentheses' => true,
		'no_unneeded_import_alias' => true,
		'no_unused_imports' => true,
		'no_useless_else' => true,
		'no_useless_nullsafe_operator' => true,
		'no_useless_return' => true,
		'no_whitespace_before_comma_in_array' => true,
		'nullable_type_declaration' => ['syntax' => 'union'],
		'nullable_type_declaration_for_default_null_value' => true,
		'object_operator_without_whitespace' => true,
		'operator_linebreak' => ['position' => 'end', 'only_booleans' => true],
		'ordered_imports' => ['sort_algorithm' => 'alpha'],
		'ordered_types' => ['sort_algorithm' => 'none', 'null_adjustment' => 'always_last'],
		'phpdoc_align' => ['align' => 'left'],
		'phpdoc_indent' => true,
		'phpdoc_param_order' => true,
		'phpdoc_scalar' => true,
		'phpdoc_trim' => true,
		'php_unit_fqcn_annotation' => true,
		'single_line_comment_style' => true,
		'single_quote' => true,
		'statement_indentation' => ['stick_comment_to_next_continuous_control_statement' => true],
		'ternary_to_null_coalescing' => true,
		'trim_array_spaces' => true,
		'whitespace_after_comma_in_array' => true
	])
	->setRiskyAllowed(true)
	->setIndent("\t")
	->setFinder($finder);
