# easyRdfParserPerf

A set of tests for checking possible EasyRdf parser backends performance.

## Running

Run `php -f test.php` in the repository's main directory.

This is also done automatically after every push to the repository using the GitHub Actions CI/CD.

You can see results of automatic runs [here](https://github.com/zozlak/easyRdfParserPerf/actions?query=workflow%3Arun).

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


## Extending

### Providing additional test data

Just add a data file to the `data` directory.

Remarks:

* Please indicate the data license in a comment inside a file.

### Providing additional backends

Write a class implementing the `\EasyRdf\ParserPerfTest\ParserPerfTestInterface`
(see [here](https://github.com/zozlak/easyRdfParserPerf/blob/main/src/EasyRdf/ParserPerfTest/ParserPerfTestInterface.php))
and assure it's autoloadable
(e.g. give it the `EasyRdf\ParserPerfTest` namespace and save it in `src\EasyRdf\ParserPerfTest\YourClassName.php`).

For an example please take a look at the [EasyRdf class](https://github.com/zozlak/easyRdfParserPerf/blob/master/src/EasyRdf/ParserPerfTest/EasyRdf.php).

## Implementation remarks

* The `test.php` calls particular tests as separate processes and measures the memory usage with `/usr/bin/time` as we don't have a reliable way of doing it in the PHP. The first issue is that `memory_get_peak_usage()` won't work if a less memory-hungry test/parser class is run after a more memory-hungry one. The second problem is that `memory_get_*usage()` doesn't report memory allocated outside of the PHP (e.g. by libxml2 when parsing the RDF XML).
