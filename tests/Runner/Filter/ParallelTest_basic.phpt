--TEST--
Test Parallel Filter when No Args Provided
--FILE--
<?php
use PHPUnit\ParallelRunner\PHPUnit_Parallel_Command;

// $_SERVER['argv'][0] = 'phpunit'; // this will be set by the shell
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--printer=CustomPrinter\TapPrinter';
$_SERVER['argv'][3] = __DIR__ . '/_files/BasicTestFile.php';

$dir = $_SERVER['PWD'];
require_once($dir . '/vendor/autoload.php');

PHPUnit_Parallel_Command::main();

--EXPECTF--
PHPUnit 7.5.14 by Sebastian Bergmann and contributors.

ok 1 - BasicTest::testBasic1
ok 2 - BasicTest::testBasic2
ok 3 - BasicTest::testBasic3
ok 4 - BasicTest::testBasic4
ok 5 - BasicTest::testBasic5
ok 6 - BasicTest::testBasic6
ok 7 - BasicTest::testBasic7
ok 8 - BasicTest::testBasic8
ok 9 - BasicTest::testBasic9
ok 10 - BasicTest::testBasic10
ok 11 - BasicTest::testBasic11
ok 12 - BasicTest::testBasic12
1..12
