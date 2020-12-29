#!/usr/bin/php
<?php
/*
 * BSD 3-Clause License
 * 
 * Copyright (c) 2020, Mateusz Żółtak
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 * 
 * 1. Redistributions of source code must retain the above copyright notice, this
 *    list of conditions and the following disclaimer.
 * 
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 *    this list of conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution.
 * 
 * 3. Neither the name of the copyright holder nor the names of its
 *    contributors may be used to endorse or promote products derived from
 *    this software without specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

if ($argc !== 4 && $argc !== 1) {
    exit("$argv[0] [class dataFile outputFile]
        
Performs EasyRdf backend tests

Can be called in two ways:

1. Without parameters. In such a case all classes implementing 
   the `ParserPerfTestInterface` in the `src/EasyRdf/ParserPerfTest`
   directory are tested on all matching data from the `data` directory
   and the JSON output is written on the standard output.
2. With parameters, e.g. 
   `php -f test.php '\\EasyRdf\\ParserPerfTest\\EasyRdf' data/puzzle4d_100k.ttl out.json`. 
   In such a case a given class is tested on a given data file with
   JSON output being written to a given file.
");
}

require_once __DIR__ . '/vendor/autoload.php';

$test = new EasyRdf\ParserPerfTest\Test();
if ($argc === 4) {
    try {
        $results = $test->testClass($argv[1], $argv[2]);
        file_put_contents($argv[3], json_encode($results));
    } catch (BadMethodCallException $e) {
        if (file_exists($argv[3])) {
            unlink($argv[3]);
        }
    }
} else {
    $results = [];
    foreach ($test->getClasses(__DIR__ . '/src/EasyRdf/ParserPerfTest', '\\EasyRdf\\ParserPerfTest') as $class) {
        $obj    = new $class();
        $classE = escapeshellarg($class);
        foreach ($test->getDataFiles(__DIR__ . '/data', $obj) as $dataFile) {
            $php         = escapeshellarg(__FILE__);
            $dataFileE   = escapeshellarg($dataFile);
            $outputFile  = __DIR__ . '/tmp.json';
            $outputFileE = escapeshellarg($outputFile);
            if (file_exists($outputFile)) {
                unlink($outputFile);
            }
            $cmd    = "/usr/bin/time -v php -f $php $classE $dataFileE $outputFileE 2>&1 | grep 'Maximum resident set size'";
            $output = trim(shell_exec($cmd));
            if (file_exists($outputFile)) {
                $result           = json_decode(file_get_contents($outputFile));
                unlink($outputFile);
                $result->memoryMb = preg_replace('/^.*Maximum resident set size [(]kbytes[)]: ([0-9]+).*$/', '\\1', $output) / 1024;
                $results[]        = $result;
            } else {
                echo "$cmd failed\n";
            }
        }
    }
    echo json_encode($results, JSON_PRETTY_PRINT);
}