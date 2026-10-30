<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude(['vendor', 'tests/ci-tools/vendor', 'node_modules', 'views/js/admin/settings-app/node_modules']);

// PrestaShop\CodingStandards\CsFixer\Config overrides getRules(), so its rules are copied
// into a plain config to adjust the ones that contradict PrestaShop or the PHP 7.1 floor:
// - modifier_keywords replaced visibility_required in php-cs-fixer 3.88, so the PrestaShop
//   override that keeps class constants without a visibility keyword no longer applies.
// - blank_line_after_opening_tag puts a blank line between <?php and the license header,
//   which PrestaShop core and the header-stamp tool write directly below the tag.
// - trailing_comma_in_multiline also adds commas after call arguments and parameters when
//   php-cs-fixer runs on PHP 8, which PHP 7.1 cannot parse.
$rules = array_merge((new PrestaShop\CodingStandards\CsFixer\Config())->getRules(), [
    'modifier_keywords' => ['elements' => ['property', 'method']],
    'blank_line_after_opening_tag' => false,
    'trailing_comma_in_multiline' => ['elements' => ['arrays']],
]);

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules($rules)
    ->setFinder($finder);
