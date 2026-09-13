# Module translations

The module ships no filled translation files, so every text falls back to its
English source whatever language the shop runs in (SL-387). These two scripts
turn a reviewed wording file into the translation files PrestaShop loads.

They live under `tests/` on purpose: `make prepare-zip` strips that directory,
so the tooling never reaches a merchant's shop.

## The key layout

PrestaShop resolves a legacy module string to

```
<{saferpayofficial}prestashop><source>_<md5 of the string>
```

`<source>` is the second argument of `l()` when one is passed, the module name
when it is not, and the template basename for `.tpl` strings. The md5 is taken
after every apostrophe is escaped exactly once, matching
`Translate::getModuleTranslation()`. `extract-strings.php` reproduces this, so
the keys it emits are the ones looked up at runtime — they are not guesses.

## Producing the string list

```sh
php tests/translations/extract-strings.php --csv=wording.csv
```

`wording.csv` then holds one row per translatable string with its key, its
source, the English text, empty `de`/`fr` columns to fill in, and the file and
line each string comes from. That file is what goes out for translation.

The script also reports `l()` calls whose argument is a variable. Those cannot
be extracted and have to be translated where the value is produced.

## Building the translation files

```sh
php tests/translations/build-translation-files.php --wording=reviewed.csv
php tests/translations/build-translation-files.php --wording=reviewed.csv --languages=de,fr --dry-run
```

The wording file needs either a `key` column or an English source column, plus
one column per language. Headers are matched loosely, so `German`, `de` and
`de-CH` all resolve to `de`. Rows are matched on the key when the file carries
one and on the English string otherwise, which means a file that has been
reworded on the English side will report those rows as unmatched rather than
silently write the wrong text.

Before writing anything the script reports, per language, how many strings it
translated, which ones are still missing, which cells were left blank, and
which wording rows match no string in the module. Use `--dry-run` to see that
report without touching `translations/`. A run that is not fully covered exits
non-zero, so it can gate a release.

Strings used from more than one source get one entry per source, which is why
242 keys come out of 222 distinct strings.

## Adding a language

Add the ISO code to `--languages` and give the wording file a matching column.
Nothing else in the module needs to change.
