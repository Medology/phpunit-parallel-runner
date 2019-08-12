--TEST--
Test Parallel Filter for 2nd of 5 Nodes
--FILE--
<?php
use PHPUnit\ParallelRunner\PHPUnit_Parallel_Command;

// $_SERVER['argv'][0] = 'phpunit'; // this will be set by the shell
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--printer=CustomPrinter\TapPrinter';
$_SERVER['argv'][3] = '--current-node=1';
$_SERVER['argv'][4] = '--total-nodes=5';
$_SERVER['argv'][5] = __DIR__ . '/_files/BasicTestFile.php';

$dir = $_SERVER['PWD'];
require_once($dir . '/vendor/autoload.php');

PHPUnit_Parallel_Command::main();

--EXPECTF--
PHPUnit 7.5.14 by Sebastian Bergmann and contributors.

ok 1 - BasicTest::testBasic2
ok 2 - BasicTest::testBasic7
ok 3 - BasicTest::testBasic12
1..3
