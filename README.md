# easyRdfParserPerf

A set of tests for checking possible EasyRdf parser backends performance.

As we want to compare aples to aples each backend implementation must return data in exactly the same format
and as we are considering possible EasyRdf backends, the common format is an `EasyRdf\Graph` instance.

## Running

Run `php -f test.php` in the repository's main directory.
It will run tests for all available implementations and data files.

This is also done automatically after every push to the repository using the GitHub Actions CI/CD.

You can see results of automatic runs [here](https://github.com/zozlak/easyRdfParserPerf/actions?query=workflow%3Arun).

Remarks:

* `test.php` takes a few parameters - see `php -f test.php -- --help`.
    * When running `test.php` with `php -f test.php` you must use `--` to separate `php` parameters from `test.php` parameters.
      All parameters prior to `--` are passed to the `php` and all after to `test.php`.

### Results

A single test is a combination of a particular paraser class and test data file.

Results are a JSON table of each test metrics, e.g.:

```json
[
    {
        "class": "EasyRdf\\ParserPerfTest\\EasyRdf",
        "dataFile": "puzzle4d_100k.ttl",
        "time": 26.795469045639038,
        "triplesCount": 109456,
        "memoryMb": 178.7421875,
        "dataFileSizeMb": 7.526865005493164
    }
]
```

## Reports

The repository includes an R code for parsing test results and generating a simple report out of them.

This is done by the code in `plots.R` which is also automatically run using the GitHub Actions CI/CD.

Report is published as an Actions run artifact.

## Extending

### Providing additional test data

Just add a data file to the right `data/{dataSize}` directory.

Remarks:

* Please indicate the data license in a comment inside a file.
* Please indicate the correct triples count and resources (graph nodes) count of your test data by adding two triples to it:
  ```
  <https://technical#subject> <https://technical#resourceCount> "{expected graph nodes count}" .
  <https://technical#subject> <https://technical#tripleCount> "{expected graph edges count}" .
  ```
    * See e.g. first two triples of [this test file](https://github.com/zozlak/easyRdfParserPerf/blob/master/data/small/puzzle4d_5k.ntriples).
    * If any of those triple isn't provided, the parsing correctness is just taken for granted.

### Providing additional backends

Write a class implementing the `\EasyRdf\ParserPerfTest\ParserPerfTestInterface`
(see [here](https://github.com/zozlak/easyRdfParserPerf/blob/master/src/EasyRdf/ParserPerfTest/ParserPerfTestInterface.php))
and assure it's autoloadable
(e.g. put it the `EasyRdf\ParserPerfTest` namespace and save it in `src\EasyRdf\ParserPerfTest\YourClassName.php`).

For an example please take a look at the [EasyRdf class](https://github.com/zozlak/easyRdfParserPerf/blob/master/src/EasyRdf/ParserPerfTest/EasyRdf.php).

## Implementation remarks

* The `test.php` calls particular tests as separate processes and measures the memory usage with `/usr/bin/time` as we don't have a reliable way of doing it in the PHP. The first issue is that `memory_get_peak_usage()` won't work if a less memory-hungry test/parser class is run after a more memory-hungry one. The second problem is that `memory_get_*usage()` doesn't report memory allocated outside of the PHP (e.g. by libxml2 when parsing the RDF XML).
