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

/**
 * testOldReleases.php [release] [test.php parameters]
 *
 * Runs test.php on old EasyRdf releases
 * 
 * - Only releases >=1.0.0 are supported.
 * - If no release number is supported, all releases >= 1.0.0. are tested
 */
const VERSION_REGEX = '/^[0-9][.][0-9][.][0-9]$/';
const MIN_VERSION = '1.0.0';
const REPO_URL = 'https://api.github.com/repos/easyrdf/easyrdf/releases';

require_once __DIR__ . '/vendor/autoload.php';

if (preg_match(VERSION_REGEX, $argv[1] ?? '')) {
    $versions = [$argv[1]];
    $firstParam = 2;
} else {
    $firstParam = 1;
    $client   = new GuzzleHttp\Client();
    $headers  = ['Accept' => 'application/vnd.github.v3+json'];
    $request  = new GuzzleHttp\Psr7\Request('get', REPO_URL, $headers);
    $response = $client->sendRequest($request);
    $releases = json_decode($response->getBody());
    $versions = [];
    foreach ($releases as $n => $i) {
        if ($n > 0 && $i->tag_name >= MIN_VERSION) {
            $versions[] = $i->tag_name;
        } elseif ($n > 0) {
            break;
        }
    }
}
$test = new EasyRdf\ParserPerfTest\Test();

echo "[\n";
$N = 0;
foreach ($versions as $version) {
    $cmd = "composer require easyrdf/easyrdf:$version >/dev/null 2>&1 ";
    $cmd .= "&& php -f test.php -- --class 'EasyRdf\ParserPerfTest\EasyRdf'";
    for ($i = $firstParam; $i < $argc; $i++) {
        $cmd .= ' ' . escapeshellarg($argv[$i]);
    }
    $data = json_decode(shell_exec($cmd));
    foreach ($data as $i) {
        $i->class .= "-$version";
        echo ($N > 0 ? ",\n" : '') . json_encode($i, JSON_PRETTY_PRINT | JSON_INVALID_UTF8_IGNORE);
        $N++;
    }
}
echo "]\n";
