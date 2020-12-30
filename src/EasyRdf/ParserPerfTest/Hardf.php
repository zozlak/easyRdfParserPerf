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

namespace EasyRdf\ParserPerfTest;

use Exception;
use RuntimeException;
use EasyRdf\Graph;
use EasyRdf\Literal;
use pietercolpaert\hardf\TriGParser;
use pietercolpaert\hardf\Util;

/**
 * Description of Hardf
 *
 * @author zozlak
 */
class Hardf implements ParserPerfTestInterface {

    static private $formats = [
        'ttl'      => 'turtle',
        'trig'     => 'trig',
        'ntriples' => 'triple',
        'n3'       => 'n3',
    ];

    public function __construct() {
        
    }

    public function getSupportedFileExtensions(): array {
        return array_keys(self::$formats);
    }

    public function parse(string $filePath): Graph {
        $graph = new Graph();
        $parser = $this->getParser($filePath, $graph);
        $parser->parseChunk(file_get_contents($filePath));
        $parser->end();
        return $graph;
    }

    protected function getParser(string $filePath, Graph $graph): TriGParser {
        $tripleHandler = function(?Exception $error, ?array $triple) use ($graph) {
            if ($triple) {
                if (substr($triple['object'], 0, 1) !== '"') {
                    $object = $graph->resource($triple['object']);
                } else {
                    $value    = substr($triple['object'], 1, strrpos($triple['object'], '"') - 1); // as Util::getLiteralValue() doesn't work for multiline values
                    $lang     = Util::getLiteralLanguage($triple['object']);
                    $datatype = Util::getLiteralType($triple['object']);
                    $object   = new Literal($value, $lang, $datatype);
                }
                $graph->add($triple['subject'], $triple['predicate'], $object);
            } elseif ($error !== null) {
                throw $error;
            }
        };
        $options = ['format' => $this->getFormat($filePath)];
        $parser  = new TriGParser($options, $tripleHandler);
        return $parser;
    }

    private function getFormat(string $filePath): string {
        $fileName = basename($filePath);
        $ext      = substr($fileName, strrpos($fileName, '.') + 1);
        if (!isset(self::$formats[$ext])) {
            throw new RuntimeException("Can't determine format for $fileName");
        }
        return self::$formats[$ext];
    }

}
