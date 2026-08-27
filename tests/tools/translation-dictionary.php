<?php
/**
 *NOTICE OF LICENSE
 *
 *This source file is subject to the Open Software License (OSL 3.0)
 *that is bundled with this package in the file LICENSE.txt.
 *It is also available through the world-wide-web at this URL:
 *http://opensource.org/licenses/osl-3.0.php
 *If you did not receive a copy of the license and are unable to
 *obtain it through the world-wide-web, please send an email
 *to license@prestashop.com so we can send you a copy immediately.
 *
 *DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 *versions in the future. If you wish to customize PrestaShop for your
 *needs please refer to http://www.prestashop.com for more information.
 *
 *@author INVERTUS UAB www.invertus.eu  <support@invertus.eu>
 *@copyright SIX Payment Services
 *@license   SIX Payment Services
 */

/**
 * SL-387 translation dictionary tool.
 *
 * Extracts every translatable wording of the module together with the exact
 * legacy dictionary key specifier PrestaShop resolves at runtime, and builds
 * translations/<iso>.php files from the reviewed wording CSV.
 *
 * The runtime key is built by Translate::getModuleTranslation():
 *   strtolower('<{saferpayofficial}prestashop>' . $specifier) . '_' . md5($escapedSource)
 * where $escapedSource = preg_replace("/\\\*'/", "\'", $source).
 *
 * PrestaShop's own extractor cannot be used because most l() calls pass the
 * specifier as a class constant (self::FILE_NAME, self::SHORT_CLASS_NAME) or
 * __CLASS__, which only the class knows.
 *
 * Usage:
 *   php tests/tools/translation-dictionary.php extract
 *   php tests/tools/translation-dictionary.php build de
 *   php tests/tools/translation-dictionary.php build fr
 *
 * Extract preserves already-filled de/fr columns when the CSV exists.
 * This tool is stripped from the merchant zip (create_zip.yml removes tests/).
 */

if (PHP_SAPI !== 'cli') {
    exit(1);
}

const MODULE_NAME = 'saferpayofficial';
const CSV_DEFAULT = __DIR__ . '/wording/SL-387-wording-de-fr.csv';
const LANGS = ['de', 'fr'];

/** Same OSL 3.0 notice every shipped PHP file of this module carries. */
const LICENSE_HEADER = <<<'HEADER'
/**
 *NOTICE OF LICENSE
 *
 *This source file is subject to the Open Software License (OSL 3.0)
 *that is bundled with this package in the file LICENSE.txt.
 *It is also available through the world-wide-web at this URL:
 *http://opensource.org/licenses/osl-3.0.php
 *If you did not receive a copy of the license and are unable to
 *obtain it through the world-wide-web, please send an email
 *to license@prestashop.com so we can send you a copy immediately.
 *
 *DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 *versions in the future. If you wish to customize PrestaShop for your
 *needs please refer to http://www.prestashop.com for more information.
 *
 *@author INVERTUS UAB www.invertus.eu  <support@invertus.eu>
 *@copyright SIX Payment Services
 *@license   SIX Payment Services
 */

HEADER;

$moduleDir = realpath(__DIR__ . '/../..');

// Strings passed to l() through a variable at runtime (order state names,
// Installer::createOrderStatus). They key under the bare module name.
$manualStrings = [
    'Payment completed by Saferpay',
    'Payment authorized by Saferpay',
    'Payment pending by Saferpay',
    'Payment rejected by Saferpay',
    'Awaiting Saferpay payment',
    'Order Refunded by Saferpay',
    'Order Partly Refunded by Saferpay',
    'Order Pending Refund by Saferpay',
    'Order Canceled by Saferpay',
    'Order authorization failed by Saferpay',
];

$command = $argv[1] ?? '';
$options = parseOptions(array_slice($argv, 2));
$csvPath = $options['csv'] ?? CSV_DEFAULT;

if ($command === 'extract') {
    extractCommand($moduleDir, $csvPath, $manualStrings);
    exit(0);
}

if ($command === 'build') {
    $iso = $argv[2] ?? '';
    if (!in_array($iso, LANGS, true)) {
        fwrite(STDERR, "Usage: build <iso> where iso is one of: " . implode(', ', LANGS) . "\n");
        exit(1);
    }
    $out = $options['out'] ?? $moduleDir . '/translations/' . $iso . '.php';
    buildCommand($csvPath, $iso, $out);
    exit(0);
}

fwrite(STDERR, "Usage: php tests/tools/translation-dictionary.php extract|build <iso> [--csv=path] [--out=path]\n");
exit(1);

function parseOptions(array $args)
{
    $options = [];
    foreach ($args as $arg) {
        if (preg_match('/^--(\w+)=(.+)$/', $arg, $m)) {
            $options[$m[1]] = $m[2];
        }
    }

    return $options;
}

// ---------------------------------------------------------------------------
// Extract
// ---------------------------------------------------------------------------

function extractCommand($moduleDir, $csvPath, array $manualStrings)
{
    $phpFiles = collectFiles($moduleDir, ['saferpayofficial.php', 'controllers', 'src', 'upgrade'], 'php');
    $classMap = buildClassMap($phpFiles);

    $entries = [];
    $skipped = [];

    foreach ($phpFiles as $file) {
        extractFromPhp($file, $moduleDir, $classMap, $entries, $skipped);
    }

    $tplFiles = collectFiles($moduleDir, ['views/templates'], 'tpl');
    foreach ($tplFiles as $file) {
        extractFromTpl($file, $moduleDir, $entries, $skipped);
    }

    foreach ($manualStrings as $string) {
        addEntry($entries, MODULE_NAME, $string, 'src/Install/Installer.php (runtime variable, order state name)');
    }

    $existing = readCsv($csvPath);

    ksort($entries);
    $dir = dirname($csvPath);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $handle = fopen($csvPath, 'w');
    fputcsv($handle, ['specifier', 'source_en', 'de', 'fr', 'placeholders', 'occurrences'], ',', '"', '');
    $preserved = 0;
    foreach ($entries as $entry) {
        $rowKey = rowKey($entry['specifier'], $entry['source']);
        $de = $existing[$rowKey]['de'] ?? '';
        $fr = $existing[$rowKey]['fr'] ?? '';
        if ($de !== '' || $fr !== '') {
            ++$preserved;
        }
        fputcsv($handle, [
            $entry['specifier'],
            $entry['source'],
            $de,
            $fr,
            implode(' ', placeholders($entry['source'])),
            implode('; ', array_unique($entry['occurrences'])),
        ], ',', '"', '');
    }
    fclose($handle);

    $specifiers = array_unique(array_map(function ($entry) {
        return $entry['specifier'];
    }, $entries));
    $sources = array_unique(array_map(function ($entry) {
        return $entry['source'];
    }, $entries));

    echo 'Rows: ' . count($entries)
        . ' | unique wordings: ' . count($sources)
        . ' | specifiers: ' . count($specifiers)
        . ' | translations preserved: ' . $preserved . "\n";
    echo 'CSV: ' . $csvPath . "\n";

    if ($skipped) {
        echo "\nNot extractable (variable argument), left in English on purpose:\n";
        foreach ($skipped as $line) {
            echo '  ' . $line . "\n";
        }
    }
}

function collectFiles($moduleDir, array $roots, $extension)
{
    $files = [];
    foreach ($roots as $root) {
        $path = $moduleDir . '/' . $root;
        if (is_file($path)) {
            $files[] = $path;
            continue;
        }
        if (!is_dir($path)) {
            continue;
        }
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $info) {
            if ($info->getExtension() === $extension) {
                $files[] = $info->getPathname();
            }
        }
    }
    sort($files);

    return $files;
}

/**
 * Map of lowercased FQCN => [fileName const value or null, parent FQCN or null]
 * used to resolve self::FILE_NAME through inheritance.
 */
function buildClassMap(array $phpFiles)
{
    $map = [];
    foreach ($phpFiles as $file) {
        $content = file_get_contents($file);
        $namespace = '';
        if (preg_match('/^namespace\s+([^;]+);/m', $content, $m)) {
            $namespace = trim($m[1]);
        }
        if (!preg_match('/^(?:final\s+|abstract\s+)?class\s+(\w+)(?:\s+extends\s+([\w\\\\]+))?/m', $content, $m)) {
            continue;
        }
        $class = $m[1];
        $fqcn = ltrim(($namespace ? $namespace . '\\' : '') . $class, '\\');

        $parent = null;
        if (!empty($m[2])) {
            $parent = resolveClassName($m[2], $namespace, $content);
        }

        $constants = [];
        if (preg_match_all("/const\s+(\w+)\s*=\s*'([^']*)'/", $content, $m, PREG_SET_ORDER)) {
            foreach ($m as $const) {
                $constants[$const[1]] = $const[2];
            }
        }

        $map[strtolower($fqcn)] = ['constants' => $constants, 'parent' => $parent, 'fqcn' => $fqcn];
    }

    return $map;
}

function resolveClassName($name, $namespace, $content)
{
    if (strpos($name, '\\') === 0) {
        return ltrim($name, '\\');
    }
    $first = explode('\\', $name)[0];
    if (preg_match_all('/^use\s+([^;]+);/m', $content, $m)) {
        foreach ($m[1] as $use) {
            $use = trim($use);
            $alias = null;
            if (preg_match('/^(.+)\s+as\s+(\w+)$/i', $use, $aliasMatch)) {
                $use = trim($aliasMatch[1]);
                $alias = $aliasMatch[2];
            }
            $short = $alias !== null ? $alias : substr($use, strrpos($use, '\\') + 1 ?: 0);
            if (strcasecmp($short, $first) === 0) {
                $rest = substr($name, strlen($first));

                return ltrim($use . $rest, '\\');
            }
        }
    }

    return ltrim(($namespace ? $namespace . '\\' : '') . $name, '\\');
}

function resolveClassConst(array $classMap, $fqcn, $constName)
{
    $current = strtolower((string) $fqcn);
    $guard = 0;
    while (isset($classMap[$current]) && $guard++ < 10) {
        if (isset($classMap[$current]['constants'][$constName])) {
            return $classMap[$current]['constants'][$constName];
        }
        $parent = $classMap[$current]['parent'];
        if ($parent === null) {
            return null;
        }
        $current = strtolower($parent);
    }

    return null;
}

function extractFromPhp($file, $moduleDir, array $classMap, array &$entries, array &$skipped)
{
    $content = file_get_contents($file);
    $relative = substr($file, strlen($moduleDir) + 1);

    $namespace = '';
    if (preg_match('/^namespace\s+([^;]+);/m', $content, $m)) {
        $namespace = trim($m[1]);
    }
    $fqcn = null;
    if (preg_match('/^(?:final\s+|abstract\s+)?class\s+(\w+)/m', $content, $m)) {
        $fqcn = ltrim(($namespace ? $namespace . '\\' : '') . $m[1], '\\');
    }

    $tokens = token_get_all($content);
    $count = count($tokens);

    for ($i = 0; $i < $count; ++$i) {
        if (!is_array($tokens[$i]) || $tokens[$i][0] !== T_OBJECT_OPERATOR) {
            continue;
        }
        $next = nextMeaningful($tokens, $i + 1);
        if ($next === null || !is_array($tokens[$next]) || $tokens[$next][0] !== T_STRING || $tokens[$next][1] !== 'l') {
            continue;
        }
        $paren = nextMeaningful($tokens, $next + 1);
        if ($paren === null || $tokens[$paren] !== '(') {
            continue;
        }
        $line = $tokens[$next][2];
        $args = readCallArgs($tokens, $paren);

        $sourceTokens = trimTokens($args[0] ?? []);
        if (count($sourceTokens) !== 1 || !is_array($sourceTokens[0]) || $sourceTokens[0][0] !== T_CONSTANT_ENCAPSED_STRING) {
            $skipped[] = $relative . ':' . $line . ' -> l(' . renderTokens($sourceTokens) . ')';
            continue;
        }
        $source = eval('return ' . $sourceTokens[0][1] . ';');

        $specifier = resolveSpecifier(trimTokens($args[1] ?? []), $classMap, $fqcn, $relative, $line, $skipped);
        if ($specifier === false) {
            continue;
        }

        addEntry($entries, $specifier, $source, $relative . ':' . $line);
    }
}

function resolveSpecifier(array $tokens, array $classMap, $fqcn, $relative, $line, array &$skipped)
{
    if (!$tokens) {
        return MODULE_NAME;
    }
    if (count($tokens) === 1 && is_array($tokens[0])) {
        if ($tokens[0][0] === T_CONSTANT_ENCAPSED_STRING) {
            return eval('return ' . $tokens[0][1] . ';');
        }
        if ($tokens[0][0] === T_CLASS_C) {
            return $fqcn;
        }
    }
    if (count($tokens) === 3
        && is_array($tokens[0]) && $tokens[0][1] === 'self'
        && is_array($tokens[2]) && $tokens[2][0] === T_STRING
    ) {
        $value = resolveClassConst($classMap, $fqcn, $tokens[2][1]);
        if ($value !== null) {
            return $value;
        }
    }

    $skipped[] = $relative . ':' . $line . ' -> unresolved specifier: ' . renderTokens($tokens);

    return false;
}

function nextMeaningful(array $tokens, $start)
{
    $count = count($tokens);
    for ($i = $start; $i < $count; ++$i) {
        if (is_array($tokens[$i]) && in_array($tokens[$i][0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
            continue;
        }

        return $i;
    }

    return null;
}

/** Split the balanced argument list starting at the opening paren into top-level args. */
function readCallArgs(array $tokens, $openParen)
{
    $args = [];
    $current = [];
    $depth = 0;
    $count = count($tokens);
    for ($i = $openParen; $i < $count; ++$i) {
        $token = $tokens[$i];
        $char = is_array($token) ? null : $token;
        if ($char === '(' || $char === '[' || $char === '{') {
            ++$depth;
            if ($depth === 1 && $char === '(') {
                continue;
            }
        }
        if ($char === ')' || $char === ']' || $char === '}') {
            --$depth;
            if ($depth === 0 && $char === ')') {
                if (trimTokens($current)) {
                    $args[] = $current;
                }

                return $args;
            }
        }
        if ($char === ',' && $depth === 1) {
            $args[] = $current;
            $current = [];
            continue;
        }
        if ($depth >= 1) {
            $current[] = $token;
        }
    }

    return $args;
}

function trimTokens(array $tokens)
{
    return array_values(array_filter($tokens, function ($token) {
        return !(is_array($token) && in_array($token[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true));
    }));
}

function renderTokens(array $tokens)
{
    return implode('', array_map(function ($token) {
        return is_array($token) ? $token[1] : $token;
    }, $tokens));
}

function extractFromTpl($file, $moduleDir, array &$entries, array &$skipped)
{
    $content = file_get_contents($file);
    $relative = substr($file, strlen($moduleDir) + 1);
    $specifier = basename($file, '.tpl');

    if (!preg_match_all('/\{l\s+([^{}]*)\}/', $content, $matches, PREG_OFFSET_CAPTURE)) {
        return;
    }

    foreach ($matches[1] as $match) {
        $attrs = $match[0];
        $line = substr_count(substr($content, 0, $match[1]), "\n") + 1;

        if (!preg_match("/\bmod=['\"]" . MODULE_NAME . "['\"]/", $attrs)) {
            $skipped[] = $relative . ':' . $line . ' -> {l} without mod=' . MODULE_NAME;
            continue;
        }

        $source = null;
        if (preg_match("/\bs='((?:\\\\.|[^'\\\\])*)'/", $attrs, $m)) {
            $source = str_replace(["\\'", '\\\\'], ["'", '\\'], $m[1]);
        } elseif (preg_match('/\bs="((?:\\\\.|[^"\\\\])*)"/', $attrs, $m)) {
            $source = str_replace(['\\"', '\\\\'], ['"', '\\'], $m[1]);
        }
        if ($source === null) {
            $skipped[] = $relative . ':' . $line . ' -> {l} with non-literal s attribute';
            continue;
        }

        addEntry($entries, $specifier, $source, $relative . ':' . $line);
    }
}

function addEntry(array &$entries, $specifier, $source, $occurrence)
{
    $key = rowKey($specifier, $source);
    if (!isset($entries[$key])) {
        $entries[$key] = ['specifier' => $specifier, 'source' => $source, 'occurrences' => []];
    }
    $entries[$key]['occurrences'][] = $occurrence;
}

function rowKey($specifier, $source)
{
    return strtolower($specifier) . '|' . $source;
}

function placeholders($string)
{
    preg_match_all('/%(?:\d+\$)?[sd]/', $string, $matches);

    return $matches[0];
}

function readCsv($csvPath)
{
    if (!is_file($csvPath)) {
        return [];
    }
    $rows = [];
    $handle = fopen($csvPath, 'r');
    $header = fgetcsv($handle, null, ',', '"', '');
    while (($row = fgetcsv($handle, null, ',', '"', '')) !== false) {
        $assoc = array_combine($header, array_pad($row, count($header), ''));
        $rows[rowKey($assoc['specifier'], $assoc['source_en'])] = $assoc;
    }
    fclose($handle);

    return $rows;
}

// ---------------------------------------------------------------------------
// Build
// ---------------------------------------------------------------------------

function buildCommand($csvPath, $iso, $out)
{
    $rows = readCsv($csvPath);
    if (!$rows) {
        fwrite(STDERR, "No rows in $csvPath - run extract first.\n");
        exit(1);
    }

    $lines = [];
    $missing = 0;
    $errors = [];
    foreach ($rows as $row) {
        $translation = trim($row[$iso]);
        if ($translation === '') {
            ++$missing;
            continue;
        }
        $source = $row['source_en'];
        if (placeholders($source) !== placeholders($translation)) {
            $errors[] = 'Placeholder mismatch: "' . $source . '" -> "' . $translation . '"';
            continue;
        }

        $lines[dictionaryKey($row['specifier'], $source)] = $translation;
    }

    if ($errors) {
        fwrite(STDERR, implode("\n", $errors) . "\n");
        exit(1);
    }

    ksort($lines);
    $content = "<?php\n" . LICENSE_HEADER . "\nglobal \$_MODULE;\n\$_MODULE = [];\n";
    foreach ($lines as $key => $translation) {
        $content .= "\$_MODULE['" . $key . "'] = '" . escapeValue($translation) . "';\n";
    }

    file_put_contents($out, $content);
    $lint = shell_exec('php -l ' . escapeshellarg($out) . ' 2>&1');
    echo 'Wrote ' . count($lines) . ' translations to ' . $out . ($missing ? ' (' . $missing . ' rows untranslated)' : '') . "\n";
    echo trim($lint) . "\n";
}

/** Mirrors Translate::getModuleTranslation() key derivation. */
function dictionaryKey($specifier, $source)
{
    $escaped = preg_replace("/\\\\*'/", "\\'", $source);

    return strtolower('<{' . MODULE_NAME . '}prestashop>' . $specifier) . '_' . md5($escaped);
}

/**
 * Values are read back through stripslashes(), so double every backslash and
 * escape the apostrophe once for the single-quoted PHP literal.
 */
function escapeValue($translation)
{
    $stored = str_replace('\\', '\\\\', $translation);

    return str_replace(['\\', "'"], ['\\\\', "\\'"], $stored);
}
