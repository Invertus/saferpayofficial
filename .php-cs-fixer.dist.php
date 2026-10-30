<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude(['vendor', 'var', 'tests/ci-tools/vendor', 'node_modules', 'views/js/admin/settings-app/node_modules']);

// These are the rules the Addons validator applies, with one exception. PrestaShop's Config
// overrides getRules(), so the rules are copied into a plain config to change it:
// trailing_comma_in_multiline adds commas after call arguments and parameters when
// php-cs-fixer runs on PHP 8, as the validator does, and PHP 7.1 cannot parse them.
$rules = array_merge((new PrestaShop\CodingStandards\CsFixer\Config())->getRules(), [
    'trailing_comma_in_multiline' => ['elements' => ['arrays']],
]);

return (new PhpCsFixer\Config())
    ->setUsingCache(false)
    ->setRiskyAllowed(true)
    ->setRules($rules)
    ->setFinder($finder);
