<?php
/**
 * WIKINDX : Bibliographic Management system.
 *
 * @see https://wikindx.sourceforge.io/ The WIKINDX SourceForge project
 *
 * @author The WIKINDX Team
 * @license https://creativecommons.org/licenses/by-nc-sa/4.0/ CC-BY-NC-SA 4.0
 */

/**
 * Config file for the PHP-CS-Fixer formater in for developers. It promotes code readability.
 *
 * @see https://github.com/friendsofphp/PHP-CS-Fixer
 */
$finder = PhpCsFixer\Finder::create()
    ->exclude(implode("/", ["cache"]))
    ->exclude(implode("/", ["core", "messages"]))
    ->exclude(implode("/", ["data"]))
    ->exclude(implode("/", ["dbschema"]))
    ->exclude(implode("/", ["docs"]))
    ->exclude(implode("/", ["components", "languages"]))
    ->exclude(implode("/", ["components", "plugins", "visualize", "jpgraph"]))
    ->exclude(implode("/", ["components", "plugins", "dbadminer", "plugins"]))
    ->exclude(implode("/", ["components", "styles"]))
    ->exclude(implode("/", ["components", "templates"]))
    ->exclude(implode("/", ["components", "vendor"]))
//    ->notPath('src/Symfony/Component/Translation/Tests/fixtures/resources.php')
    ->in(__DIR__)
;

return PhpCsFixer\Config::create()
    ->setRules([
        '@PSR1' => TRUE,
        '@PSR2' => TRUE,
        'align_multiline_comment' => TRUE,
        'array_indentation' => TRUE,
        'binary_operator_spaces' => TRUE,
        'blank_line_before_statement' => TRUE,
        'escape_implicit_backslashes' => TRUE,
        //'explicit_string_variable' => true,
        'function_typehint_space' => TRUE,
        'method_chaining_indentation' => TRUE,
        'multiline_comment_opening_closing' => TRUE,
        'native_function_casing' => TRUE,
        'native_function_type_declaration_casing' => TRUE,
        //'no_alias_functions' => true,
        'no_alternative_syntax' => TRUE,
        'no_binary_string' => TRUE,
        'no_blank_lines_after_class_opening' => TRUE,
        'no_blank_lines_after_phpdoc' => TRUE,
        'no_empty_comment' => TRUE,
        'no_empty_phpdoc' => TRUE,
        'no_empty_statement' => TRUE,
        'no_mixed_echo_print' => TRUE,
        'no_multiline_whitespace_around_double_arrow' => TRUE,
        'no_short_bool_cast' => TRUE,
        'no_short_echo_tag' => TRUE,
        'no_singleline_whitespace_before_semicolons' => TRUE,
        'no_spaces_around_offset' => TRUE,
        'no_unneeded_curly_braces' => TRUE,
        'no_unset_cast' => TRUE,
        'no_whitespace_before_comma_in_array' => TRUE,
        //'no_whitespace_in_blank_line' => true,
        'object_operator_without_whitespace' => TRUE,
        'ordered_class_elements' => TRUE,
        'ordered_imports' => TRUE,
        'phpdoc_add_missing_param_annotation' => TRUE,
        'phpdoc_align' => [
            'align' => 'left',
            'tags' => ['param', 'property', 'property-read', 'property-write', 'return', 'throws', 'type', 'var', 'method'],
        ],
        'phpdoc_indent' => TRUE,
        'phpdoc_no_alias_tag' => TRUE,
        'phpdoc_order' => TRUE,
        'phpdoc_scalar' => TRUE,
        'phpdoc_separation' => TRUE,
        'phpdoc_single_line_var_spacing' => TRUE,
        'phpdoc_trim' => TRUE,
        'phpdoc_trim_consecutive_blank_line_separation' => TRUE,
        'phpdoc_types' => TRUE,
        'phpdoc_types_order' => TRUE,
        'phpdoc_var_annotation_correct_order' => TRUE,
        'return_type_declaration' => TRUE,
        'semicolon_after_instruction' => TRUE,
        'short_scalar_cast' => TRUE,
        //'simple_to_complex_string_variable' => true,
        'standardize_not_equals' => TRUE,
        'ternary_operator_spaces' => TRUE,
        'trailing_comma_in_multiline_array' => TRUE,
        'trim_array_spaces' => TRUE,
        'unary_operator_spaces' => TRUE,
        'braces' => [
            'allow_single_line_closure' => FALSE,
            'position_after_anonymous_constructs' => 'same',
            'position_after_control_structures' => 'same',
            'position_after_functions_and_oop_constructs' => 'next',
        ],
        'array_syntax' => ['syntax' => 'short'],
        'concat_space' => ['spacing' => 'one'],
        'method_argument_space' => ['on_multiline' => 'ensure_fully_multiline'],
        'constant_case' => ['case' => 'upper'],
    ])
    ->setFinder($finder)
;
