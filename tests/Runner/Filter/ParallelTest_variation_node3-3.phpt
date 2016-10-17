--TEST--
Test Parallel Filter for 3rd of 3 Nodes
--FILE--
<?php
use PHPUnit\ParallelRunner\PHPUnit_Parallel_Command;

// $_SERVER['argv'][0] = 'phpunit'; // this will be set by the shell
$_SERVER['argv'][1] = '--no-configuration';
$_SERVER['argv'][2] = '--tap';
$_SERVER['argv'][3] = '--current-node=2';
$_SERVER['argv'][4] = '--total-nodes=3';
$_SERVER['argv'][5] = __DIR__ . '/_files/BasicTestFile.php';

$dir = $_SERVER['PWD'];
require_once($dir . '/vendor/autoload.php');

PHPUnit_Parallel_Command::main();

--EXPECTF--
TAP version %s
ok 1 - BasicTest::testBasic3
ok 2 - BasicTest::testBasic6
ok 3 - BasicTest::testBasic9
ok 4 - BasicTest::testBasic12
1..4
