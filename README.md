# easyRdfParserPerf

A set of tests for checking possible EasyRdf parser backends performance.

## Running

Run `php -f test.php` in the repository's main directory.

This is also done automatically after every push to the repository using the GitHub Actions CI/CD.

You can see results of automatic runs [here](https://github.com/zozlak/easyRdfParserPerf/actions?query=workflow%3Arun).

### Results

Results are provided as a json describing test results, e.g.

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

A single test is a single combination of a class and a test data file.

## Extending

### Providing additional test data

Just add a data file to the `data` directory.

Remarks:

* Please indicate the data license in a comment inside a file.

### Providing additional backends

Write a class implementing the `\EasyRdf\ParserPerfTest\ParserPerfTestInterface`
(see [here](https://github.com/zozlak/easyRdfParserPerf/blob/main/src/EasyRdf/ParserPerfTest/ParserPerfTestInterface.php))
and assure it's autoloadable (e.g. give it the `EasyRdf\ParserPerfTest` namespace and save it in `src\EasyRdf\ParserPerfTest\YourClassName.php`).

## Remarks

* The `test.php` calls particular tests as separate processes as it is the only reliable way of measuring memory usage.

