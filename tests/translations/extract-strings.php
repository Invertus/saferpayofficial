<?php
/**
 * Extracts every translatable string of the module together with the exact
 * PrestaShop legacy translation key it resolves to.
 *
 * The key layout mirrors Translate::getModuleTranslation():
 *   <{modulename}prestashop>source_md5(string)
 * where "source" is the second argument of l() when one is given, the module
 * name when it is not, and the template basename for .tpl strings.
 *
 * Usage: php tests/translations/extract-strings.php [--csv=path] [--json=path]
 */

const MODULE_NAME = 'saferpayofficial';
const THEME_NAME = 'prestashop';

$root = dirname(__DIR__, 2);

$skipDirs = ['vendor', 'tests', 'cypress', 'node_modules', 'var', '.git', '.github', '.docker'];

$entries = [];
$dynamic = [];

foreach (walk($root, $skipDirs) as $file) {
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    if ($ext === 'php') {
        collectFromPhp($file, $root, $entries, $dynamic);
    } elseif ($ext === 'tpl') {
        collectFromTpl($file, $root, $entries);
    }
}

usort($entries, static function ($a, $b) {
    return [$a['source'], $a['string']] <=> [$b['source'], $b['string']];
});

$csvPath = optionValue($argv, '--csv');
$jsonPath = optionValue($argv, '--json');

if ($csvPath !== null) {
    $handle = fopen($csvPath, 'w');
    fputcsv($handle, ['key', 'source', 'en', 'de', 'fr', 'occurrences']);
    foreach (dedupe($entries) as $row) {
        fputcsv($handle, [$row['key'], $row['source'], $row['string'], '', '', implode(' | ', $row['occurrences'])]);
    }
    fclose($handle);
    fwrite(STDERR, sprintf("Wrote %s\n", $csvPath));
}

if ($jsonPath !== null) {
    file_put_contents($jsonPath, json_encode(array_values(dedupe($entries)), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    fwrite(STDERR, sprintf("Wrote %s\n", $jsonPath));
}

$unique = dedupe($entries);
$bySource = [];
foreach ($unique as $row) {
    $bySource[$row['source']] = ($bySource[$row['source']] ?? 0) + 1;
}
ksort($bySource);

fwrite(STDERR, sprintf("\n%d translatable strings across %d sources\n", count($unique), count($bySource)));
foreach ($bySource as $source => $count) {
    fwrite(STDERR, sprintf("  %-42s %3d\n", $source, $count));
}
if ($dynamic) {
    fwrite(STDERR, sprintf("\n%d dynamic l() calls cannot be extracted (translate the value at its origin):\n", count($dynamic)));
    foreach ($dynamic as $d) {
        fwrite(STDERR, sprintf("  %s:%d\n", $d['file'], $d['line']));
    }
}

/** Merges duplicate key occurrences into one row. */
function dedupe(array $entries)
{
    $out = [];
    foreach ($entries as $e) {
        if (!isset($out[$e['key']])) {
            $out[$e['key']] = [
                'key' => $e['key'],
                'source' => $e['source'],
                'string' => $e['string'],
                'occurrences' => [],
            ];
        }
        $out[$e['key']]['occurrences'][] = $e['file'] . ':' . $e['line'];
    }

    return $out;
}

function walk($dir, array $skipDirs)
{
    $it = new RecursiveIteratorIterator(
        new RecursiveCallbackFilterIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            static function ($current) use ($skipDirs) {
                return !($current->isDir() && in_array($current->getFilename(), $skipDirs, true));
            }
        )
    );
    foreach ($it as $f) {
        if ($f->isFile()) {
            yield $f->getPathname();
        }
    }
}

function collectFromPhp($file, $root, array &$entries, array &$dynamic)
{
    $code = file_get_contents($file);
    if (strpos($code, '->l(') === false) {
        return;
    }

    $constants = [];
    if (preg_match_all('/const\s+([A-Z_][A-Z0-9_]*)\s*=\s*([\'"])(.*?)\2\s*;/', $code, $m, PREG_SET_ORDER)) {
        foreach ($m as $match) {
            $constants[$match[1]] = $match[3];
        }
    }

    $tokens = token_get_all($code);
    $count = count($tokens);

    for ($i = 0; $i < $count; $i++) {
        if (!is_array($tokens[$i]) || $tokens[$i][0] !== T_OBJECT_OPERATOR) {
            continue;
        }
        $j = nextMeaningful($tokens, $i + 1);
        if ($j === null || !is_array($tokens[$j]) || $tokens[$j][0] !== T_STRING || $tokens[$j][1] !== 'l') {
            continue;
        }
        $k = nextMeaningful($tokens, $j + 1);
        if ($k === null || $tokens[$k] !== '(') {
            continue;
        }

        $line = $tokens[$j][2];
        $args = readArguments($tokens, $k, $count);
        if ($args === null || !$args) {
            continue;
        }

        $string = literalValue($args[0]);
        if ($string === null) {
            $dynamic[] = ['file' => relative($file, $root), 'line' => $line];
            continue;
        }

        $source = MODULE_NAME;
        if (isset($args[1])) {
            $second = literalValue($args[1]);
            if ($second !== null) {
                $source = $second;
            } else {
                $constName = constantReference($args[1]);
                if ($constName !== null && isset($constants[$constName])) {
                    $source = $constants[$constName];
                } else {
                    $dynamic[] = ['file' => relative($file, $root), 'line' => $line];
                    continue;
                }
            }
        }

        $entries[] = [
            'key' => buildKey($source, $string),
            'source' => strtolower($source),
            'string' => $string,
            'file' => relative($file, $root),
            'line' => $line,
        ];
    }
}

function collectFromTpl($file, $root, array &$entries)
{
    $content = file_get_contents($file);
    if (!preg_match_all('/\{l\s+s=([\'"])(.*?)\1(?<attrs>[^}]*)\}/s', $content, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) {
        return;
    }

    $source = pathinfo($file, PATHINFO_FILENAME);

    foreach ($matches as $match) {
        $attrs = $match['attrs'][0];
        // Strings without a mod= attribute are resolved against PrestaShop core, not this module.
        if (!preg_match('/\bmod=([\'"])(.*?)\1/', $attrs, $modMatch) || $modMatch[2] !== MODULE_NAME) {
            continue;
        }
        $string = stripcslashes($match[2][0]);
        $line = substr_count(substr($content, 0, $match[0][1]), "\n") + 1;

        $entries[] = [
            'key' => buildKey($source, $string),
            'source' => strtolower($source),
            'string' => $string,
            'file' => relative($file, $root),
            'line' => $line,
        ];
    }
}

/** Reproduces the key PrestaShop looks up at runtime. */
function buildKey($source, $string)
{
    return '<{' . strtolower(MODULE_NAME) . '}' . strtolower(THEME_NAME) . '>' . strtolower($source) . '_' . md5(normalise($string));
}

/** PrestaShop hashes the string with every apostrophe escaped exactly once. */
function normalise($string)
{
    return preg_replace("/\\\\*'/", "\\'", $string);
}

function nextMeaningful(array $tokens, $i)
{
    $count = count($tokens);
    for (; $i < $count; $i++) {
        if (is_array($tokens[$i]) && in_array($tokens[$i][0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
            continue;
        }

        return $i;
    }

    return null;
}

/** Returns the argument list as arrays of tokens, or null when unbalanced. */
function readArguments(array $tokens, $openParen, $count)
{
    $depth = 0;
    $args = [];
    $current = [];

    for ($i = $openParen; $i < $count; $i++) {
        $token = $tokens[$i];
        if ($token === '(' || $token === '[') {
            $depth++;
            if ($depth === 1) {
                continue;
            }
        } elseif ($token === ')' || $token === ']') {
            $depth--;
            if ($depth === 0) {
                if ($current) {
                    $args[] = $current;
                }

                return $args;
            }
        } elseif ($token === ',' && $depth === 1) {
            $args[] = $current;
            $current = [];
            continue;
        }

        if (is_array($token) && in_array($token[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
            continue;
        }
        $current[] = $token;
    }

    return null;
}

/** Unquotes a single literal string argument, or null when it is not one. */
function literalValue(array $arg)
{
    if (count($arg) !== 1 || !is_array($arg[0]) || $arg[0][0] !== T_CONSTANT_ENCAPSED_STRING) {
        return null;
    }

    $raw = $arg[0][1];
    $quote = $raw[0];
    $inner = substr($raw, 1, -1);

    if ($quote === "'") {
        return str_replace(['\\\\', "\\'"], ['\\', "'"], $inner);
    }

    return stripcslashes($inner);
}

/** Returns FILE_NAME for arguments shaped like self::FILE_NAME. */
function constantReference(array $arg)
{
    $last = end($arg);
    if (is_array($last) && $last[0] === T_STRING) {
        return $last[1];
    }

    return null;
}

function relative($file, $root)
{
    return ltrim(str_replace($root, '', $file), DIRECTORY_SEPARATOR);
}

function optionValue(array $argv, $name)
{
    foreach ($argv as $arg) {
        if (strpos($arg, $name . '=') === 0) {
            return substr($arg, strlen($name) + 1);
        }
    }

    return null;
}
