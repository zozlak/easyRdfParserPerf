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

use DirectoryIterator;

/**
 * Runs a set of tests
 *
 * @author zozlak
 */
class Test {

    const TEST_INTERFACE = ParserPerfTestInterface::class;

    public function testObject(ParserPerfTestInterface $implementation,
                               string $dataPath): TestResult {
        $result                 = new TestResult(get_class($implementation), basename($dataPath));
        $result->dataFileSizeMb = filesize($dataPath) / 1024 / 1024;
        $t                      = microtime(true);
        $graph                  = $implementation->parse($dataPath);
        $result->time           = microtime(true) - $t;
        $result->triplesCount   = $graph->countTriples();
        return $result;
    }

    public function testClass(string $className, string $dataPath): TestResult {
        if (!in_array(self::TEST_INTERFACE, class_implements($className))) {
            throw new BadMethodCallException("$className doesn't implement the ParserPerfTestInterface");
        }
        return $this->testObject(new $className(), $dataPath);
    }

    public function getClasses(string $sourceCodeDirectory, string $namespace): iterable {
        foreach (new DirectoryIterator($sourceCodeDirectory) as $i) {
            if ($i->getExtension() === 'php' && strpos($i->getFilename(), 'Interface') === false) {
                $className = $namespace . '\\' . substr($i->getFilename(), 0, -4);
                if (in_array(self::TEST_INTERFACE, class_implements($className))) {
                    yield $className;
                }
            }
        }
    }

    public function getDataFiles(string $dataDir,
                                 ParserPerfTestInterface $implementation = null): iterable {
        $supported = $implementation === null ? null : $implementation->getSupportedFileExtensions();
        foreach (new DirectoryIterator($dataDir) as $i) {
            if ($i->isFile() && ($supported === null || in_array($i->getExtension(), $supported))) {
                yield $i->getPathname();
            }
        }
    }

}
