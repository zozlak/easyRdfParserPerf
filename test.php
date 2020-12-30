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

$param = [
    'class'  => null,
    'data'   => null,
    'runs'   => 1,
    'output' => 'php://stdout',
    'help'   => false
];
for ($i = 1; $i < count($argv); $i++) {
    $v = $argv[$i];
    $p = substr($v, 2);
    if (substr($v, 0, 2) !== '--' || !key_exists($p, $param)) {
        $param['help'] = true;
        break;
    }
    if (is_bool($param[$p])) {
        $param[$p] = !$param[$p];
    } else {
        $i++;
        $param[$p] = $argv[$i];
    }
}
$param       = (object) $param;
$param->runs = (int) $param->runs;

if ($param->help) {
    exit("$argv[0] [--class class] [--data dataFile] [--runs N] [--output filePath] [--help]
        
Performs EasyRdf backend tests.
Outputs test results as a JSON array.

- If --class is not specified, all classes implementing the 
  `ParserPerfTestInterface` in the `src/EasyRdf/ParserPerfTest` directory 
  are tested
- If --dataFile is not specified, all datafiles from the `data` directory 
  are used
- If --runs is not specified, every test (being a combination of a class and
  a data file) is run once
- If --output is not specified, the JSON output is printed on the stdout.
");
}

require_once __DIR__ . '/vendor/autoload.php';

$test = new EasyRdf\ParserPerfTest\Test();

if ($param->class !== null && $param->data !== null && $param->runs === 1) {
    $result = $test->testClass($param->class, $param->data);
    file_put_contents($param->output, json_encode([$result], JSON_PRETTY_PRINT));
} else {
    $param->output = fopen($param->output, 'w');
    fwrite($param->output, "[\n");
    if ($param->class === null) {
        $param->class = $test->getClasses(__DIR__ . '/src/EasyRdf/ParserPerfTest', '\\EasyRdf\\ParserPerfTest');
    } else {
        $param->class = [$param->class];
    }
    $N = 0;
    foreach ($param->class as $class) {
        $obj    = new $class();
        $classE = escapeshellarg($class);
        if ($param->data === null) {
            $dataFiles = $test->getDataFiles(__DIR__ . '/data', $obj);
        } else {
            $dataFiles = [$param->data];
        }
        foreach ($dataFiles as $dataFile) {
            for ($i = 0; $i < $param->runs; $i++) {
                $php         = escapeshellarg(__FILE__);
                $dataFileE   = escapeshellarg($dataFile);
                $outputFile  = __DIR__ . '/tmp.json';
                $outputFileE = escapeshellarg($outputFile);
                if (file_exists($outputFile)) {
                    unlink($outputFile);
                }
                $cmd    = "/usr/bin/time -v php -f $php -- --class $classE --data $dataFileE --runs 1 --output $outputFileE 2>&1";
                $output = trim(shell_exec($cmd));
                $memory = str_replace("\n", ' ', $output);
                $memory = preg_replace('/^.*Maximum resident set size [(]kbytes[)]: ([0-9]+).*$/m', '\\1', $memory);
                $memory = $memory / 1024;
                if (file_exists($outputFile)) {
                    $result = json_decode(file_get_contents($outputFile))[0];
                    unlink($outputFile);
                } else {
                    $result           = new EasyRdf\ParserPerfTest\TestResult(get_class($obj), basename($dataFile));
                    $result->errorMsg = substr($output, 0, strpos($output, "\nCommand exited with"));
                }
                $result->memoryMb = $memory;
                fwrite($param->output, ($N > 0 ? ",\n" : '') . json_encode($result, JSON_PRETTY_PRINT | JSON_INVALID_UTF8_IGNORE));
                $N++;
            }
        }
    }
    fwrite($param->output, "\n]\n");
    fclose($param->output);
}
