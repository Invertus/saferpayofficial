<?php
/**
 * Builds translations/<iso>.php from a reviewed wording file.
 *
 * The wording file is a CSV holding the English source string and one column
 * per language. Rows are matched to the module by the "key" column when the
 * file carries one, otherwise by the English string. Run
 * extract-strings.php first to produce the catalogue the matching runs against.
 *
 * Usage:
 *   php tests/translations/build-translation-files.php --wording=path/to/reviewed.csv
 *   php tests/translations/build-translation-files.php --wording=... --languages=de,fr --dry-run
 */

const MODULE_NAME = 'saferpayofficial';

$root = dirname(__DIR__, 2);

$wordingPath = optionValue($argv, '--wording');
if ($wordingPath === null || !is_readable($wordingPath)) {
    fwrite(STDERR, "Pass a readable wording file with --wording=<path>.\n");
    exit(1);
}

$languages = array_filter(explode(',', optionValue($argv, '--languages') ?? 'de,fr'));
$dryRun = in_array('--dry-run', $argv, true);

$catalogue = catalogue($root);
fwrite(STDERR, sprintf("Catalogue: %d strings, %d keys.\n", count(uniqueStrings($catalogue)), count($catalogue)));

$rows = readCsv($wordingPath);
if (!$rows) {
    fwrite(STDERR, "The wording file has no rows.\n");
    exit(1);
}

$header = array_shift($rows);
$columns = mapColumns($header, $languages);

if (!isset($columns['key']) && !isset($columns['en'])) {
    fwrite(STDERR, "The wording file needs a 'key' or an English source column. Found: " . implode(', ', $header) . "\n");
    exit(1);
}

foreach ($languages as $iso) {
    if (!isset($columns[$iso])) {
        fwrite(STDERR, sprintf("No column found for '%s'. Found: %s\n", $iso, implode(', ', $header)));
        exit(1);
    }
}

$byKey = [];
$byString = [];
foreach ($catalogue as $entry) {
    $byKey[$entry['key']] = $entry;
    $byString[matchable($entry['string'])][] = $entry;
}

$translations = array_fill_keys($languages, []);
$unmatched = [];
$blank = array_fill_keys($languages, []);

foreach ($rows as $index => $row) {
    $targets = resolveTargets($row, $columns, $byKey, $byString);
    if (!$targets) {
        $source = $row[$columns['en'] ?? 0] ?? '';
        if (trim($source) !== '') {
            $unmatched[] = ['line' => $index + 2, 'string' => $source];
        }
        continue;
    }

    foreach ($languages as $iso) {
        $value = trim($row[$columns[$iso]] ?? '');
        if ($value === '') {
            foreach ($targets as $target) {
                $blank[$iso][$target['key']] = $target['string'];
            }
            continue;
        }
        foreach ($targets as $target) {
            $translations[$iso][$target['key']] = $value;
        }
    }
}

$exitCode = 0;

foreach ($languages as $iso) {
    $missing = array_diff_key($byKey, $translations[$iso]);
    $path = $root . '/translations/' . $iso . '.php';

    fwrite(STDERR, sprintf(
        "\n%s: %d translated, %d missing, %d left blank in the wording file.\n",
        $iso,
        count($translations[$iso]),
        count($missing),
        count($blank[$iso])
    ));

    foreach (array_slice($missing, 0, 15, true) as $key => $entry) {
        fwrite(STDERR, sprintf("  missing  %-40s %s\n", $entry['source'], shorten($entry['string'])));
    }
    if (count($missing) > 15) {
        fwrite(STDERR, sprintf("  ... and %d more\n", count($missing) - 15));
    }

    if ($missing) {
        $exitCode = 1;
    }

    if ($dryRun) {
        continue;
    }

    file_put_contents($path, renderFile($translations[$iso]));
    fwrite(STDERR, sprintf("  wrote translations/%s.php\n", $iso));
}

if ($unmatched) {
    fwrite(STDERR, sprintf("\n%d wording rows match no string in the module:\n", count($unmatched)));
    foreach (array_slice($unmatched, 0, 20) as $row) {
        fwrite(STDERR, sprintf("  line %-5d %s\n", $row['line'], shorten($row['string'])));
    }
    if (count($unmatched) > 20) {
        fwrite(STDERR, sprintf("  ... and %d more\n", count($unmatched) - 20));
    }
    $exitCode = 1;
}

exit($exitCode);

/** Resolves one wording row to the catalogue entries it should fill. */
function resolveTargets(array $row, array $columns, array $byKey, array $byString)
{
    if (isset($columns['key'])) {
        $key = trim($row[$columns['key']] ?? '');
        if ($key !== '' && isset($byKey[$key])) {
            return [$byKey[$key]];
        }
    }

    if (isset($columns['en'])) {
        $source = matchable($row[$columns['en']] ?? '');
        if ($source !== '' && isset($byString[$source])) {
            return $byString[$source];
        }
    }

    return [];
}

/** A string may be worded once but used from several sources; compare loosely. */
function matchable($string)
{
    return preg_replace('/\s+/u', ' ', trim(html_entity_decode($string, ENT_QUOTES, 'UTF-8')));
}

function catalogue($root)
{
    $json = tempnam(sys_get_temp_dir(), 'catalogue');
    $extract = escapeshellarg(__DIR__ . '/extract-strings.php');
    exec(sprintf('php %s --json=%s 2>/dev/null', $extract, escapeshellarg($json)), $out, $status);
    if ($status !== 0) {
        fwrite(STDERR, "extract-strings.php failed.\n");
        exit(1);
    }
    $entries = json_decode(file_get_contents($json), true);
    unlink($json);

    return $entries;
}

function uniqueStrings(array $catalogue)
{
    $strings = [];
    foreach ($catalogue as $entry) {
        $strings[matchable($entry['string'])] = true;
    }

    return $strings;
}

function renderFile(array $translations)
{
    ksort($translations);

    $lines = [
        '<?php',
        '',
        'global $_MODULE;',
        '$_MODULE = [];',
    ];

    foreach ($translations as $key => $value) {
        $lines[] = sprintf('$_MODULE[%s] = %s;', quote($key), quote($value));
    }

    return implode("\n", $lines) . "\n";
}

function quote($value)
{
    return "'" . str_replace(['\\', "'"], ['\\\\', "\\'"], $value) . "'";
}

function readCsv($path)
{
    $rows = [];
    $handle = fopen($path, 'r');
    $delimiter = detectDelimiter($path);
    while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
        if ($row === [null]) {
            continue;
        }
        $rows[] = $row;
    }
    fclose($handle);

    return $rows;
}

function detectDelimiter($path)
{
    $line = fgets(fopen($path, 'r'));
    $counts = [];
    foreach ([',', ';', "\t"] as $candidate) {
        $counts[$candidate] = substr_count($line, $candidate);
    }
    arsort($counts);

    return key($counts);
}

/** Finds which column holds the key, the English source and each language. */
function mapColumns(array $header, array $languages)
{
    $aliases = [
        'key' => ['key', 'translation key', 'md5', 'hash'],
        'en' => ['en', 'en-us', 'english', 'source', 'source string', 'original', 'string', 'text'],
        'de' => ['de', 'de-de', 'de-ch', 'german', 'deutsch', 'germany'],
        'fr' => ['fr', 'fr-fr', 'fr-ch', 'french', 'francais', 'français'],
        'lt' => ['lt', 'lt-lt', 'lithuanian', 'lietuviu'],
        'it' => ['it', 'it-it', 'italian', 'italiano'],
        'nl' => ['nl', 'nl-nl', 'dutch', 'nederlands'],
        'es' => ['es', 'es-es', 'spanish', 'espanol', 'español'],
    ];

    $normalised = array_map(static function ($h) {
        return strtolower(trim(preg_replace('/\s+/u', ' ', (string) $h)));
    }, $header);

    $columns = [];
    foreach (array_merge(['key', 'en'], $languages) as $want) {
        foreach ($aliases[$want] ?? [$want] as $alias) {
            $index = array_search($alias, $normalised, true);
            if ($index !== false) {
                $columns[$want] = $index;
                break;
            }
        }
    }

    return $columns;
}

function shorten($string, $length = 70)
{
    $string = preg_replace('/\s+/u', ' ', trim($string));

    return mb_strlen($string) > $length ? mb_substr($string, 0, $length - 1) . '…' : $string;
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
