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

use EasyRdf\Graph;

/**
 * Description of EasyRdf
 *
 * @author zozlak
 */
class EasyRdf implements ParserPerfTestInterface {

    static private $formats = [
        'ttl'      => 'text/turtle',
        'ntriples' => 'application/ntriples',
        'xml'      => 'text/xml',
    ];

    public function __construct() {
        
    }

    public function parse(string $filePath): \EasyRdf\Graph {
        $fileName = basename($filePath);
        $ext      = substr($fileName, strrpos($fileName, '.') + 1);

        $graph = new Graph();
        $graph->parseFile($filePath, self::$formats[$ext]);
        return $graph;
    }

    public function getSupportedFileExtensions(): array {
        return ['ttl', 'xml', 'ntriples'];
    }

}
