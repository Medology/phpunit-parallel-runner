<?php

declare(strict_types=1);

namespace CustomPrinter;

use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestFailure;
use PHPUnit\Framework\TestListener;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\Warning;
use PHPUnit\Util\Printer;
use PHPUnit\Util\Test as UtilTest;
use Symfony\Component\Yaml\Dumper;
use Throwable;

/**
 * Class TapPrinter. Used to customize the output of PHPUnit tests execution.
 */
class TapPrinter extends Printer implements TestListener
{
    /** @var int Indicates the number of the current test executed. */
    protected $testNumber = 0;

    /** @var int Indicate the suite level in which the test is executed. */
    protected $testSuiteLevel = 0;

    /** @var bool The execution of the test */
    protected $testSuccessful = true;

    /** @var int Amount of warnings in the current test execution. */
    protected $warningCount = 0;

    /**
     * Adds an error to the output when an error occurred.
     *
     * @param Test      $test the current test being executed
     * @param Throwable $t    the throwable exception returned during the test execution
     * @param float     $time time of the test execution
     */
    public function addError(Test $test, Throwable $t, float $time): void
    {
        $this->writeNotOk($test, 'Error');
    }

    /**
     * Increments the warning count when a warning occurred during the test execution.
     *
     * @param Test    $test the current test being executed
     * @param Warning $e    the warning exception thrown during the test execution
     * @param float   $time time of the test execution
     */
    public function addWarning(Test $test, Warning $e, float $time): void
    {
        ++$this->warningCount;
    }

    /**
     * When a failure occurred, the exception is catch and added to the output.
     *
     * @param Test                 $test the current test being executed
     * @param AssertionFailedError $e    the exception thrown during the test execution
     * @param float                $time time of the test execution
     */
    public function addFailure(Test $test, AssertionFailedError $e, float $time): void
    {
        $this->writeNotOk($test, 'Failure');

        $message = explode("\n", TestFailure::exceptionToString($e));

        $diagnostic = [
            'message'  => $message[0],
            'severity' => 'fail',
        ];

        if ($e instanceof ExpectationFailedException) {
            $cf = $e->getComparisonFailure();

            if ($cf !== null) {
                $diagnostic['data'] = [
                    'got'      => $cf->getActual(),
                    'expected' => $cf->getExpected(),
                ];
            }
        }
        $yaml = new Dumper();

        $this->write(sprintf("  ---\n%s  ...\n", $yaml->dump($diagnostic, 2, 2)));
    }

    /**
     * When a test cannot finish the execution, mark them as Incomplete test.
     *
     * @param Test      $test the current test being executed
     * @param Throwable $t    the throwable exception returned during the test execution
     * @param float     $time time of the test execution
     */
    public function addIncompleteTest(Test $test, Throwable $t, float $time): void
    {
        $this->writeNotOk($test, '', 'TODO Incomplete Test');
    }

    /**
     * When a test is detected as Risky, mark them as Risky test.
     *
     * @param Test      $test the current test being executed
     * @param Throwable $t    the throwable exception returned during the test execution
     * @param float     $time time of the test execution
     */
    public function addRiskyTest(Test $test, Throwable $t, float $time): void
    {
        $message = $t->getMessage() !== '' ? ' ' . $t->getMessage() : '';
        $this->write(sprintf("ok %d - # RISKY%s\n", $this->testNumber, $message));

        $this->testSuccessful = false;
    }

    /**
     * When a test contains the Skipped annotation, the tests is marked as Skipped test.
     *
     * @param Test      $test the current test being executed
     * @param Throwable $t    the throwable exception returned during the test execution
     * @param float     $time time of the test execution
     */
    public function addSkippedTest(Test $test, Throwable $t, float $time): void
    {
        $message = $t->getMessage() !== '' ? ' ' . $t->getMessage() : '';
        $this->write(sprintf("ok %d - # SKIP%s\n", $this->testNumber, $message));

        $this->testSuccessful = false;
    }

    /**
     * A test suite started their execution.
     *
     * @param TestSuite $suite the current test suite which is going to been executed
     */
    public function startTestSuite(TestSuite $suite): void
    {
        ++$this->testSuiteLevel;
    }

    /**
     * A test suite ended their execution.
     *
     * @param TestSuite $suite the current test suite ended their execution
     */
    public function endTestSuite(TestSuite $suite): void
    {
        --$this->testSuiteLevel;

        if ($this->testSuiteLevel === 0) {
            $this->write(sprintf("1..%d\n", $this->testNumber));
        }
    }

    /**
     * A test started their execution.
     *
     * @param Test $test the current test being executed
     */
    public function startTest(Test $test): void
    {
        ++$this->testNumber;
        $this->testSuccessful = true;
    }

    /**
     * When a test ended, validates the execution status and output a diagnostics of the execution.
     *
     * @param Test  $test the current test being executed
     * @param float $time time of the test execution
     */
    public function endTest(Test $test, float $time): void
    {
        if ($this->testSuccessful === true) {
            $this->write(
                sprintf("ok %d - %s\n", $this->testNumber, UtilTest::describeAsString($test))
            );
        }

        $this->writeDiagnostics($test);
    }

    /**
     * When a test fails, writes to the output the status of the test and the failure reason.
     *
     * @param Test   $test      the current test being executed
     * @param string $prefix    Prefix of the test
     * @param string $directive Directive
     */
    protected function writeNotOk(Test $test, $prefix = '', $directive = ''): void
    {
        $str_prefix = $prefix !== '' ? $prefix . ': ' : '';
        $str_directive = $directive !== '' ? ' # ' . $directive : '';

        $this->write(
            sprintf(
                "not ok %d - %s%s%s\n",
                $this->testNumber,
                $str_prefix,
                UtilTest::describe($test),
                $str_directive
            )
        );

        $this->testSuccessful = false;
    }

    /**
     * Appends to the output the failure reason, unless is defined to not return and output.
     *
     * @param Test $test the current test being executed
     */
    private function writeDiagnostics(Test $test): void
    {
        if (!$test instanceof TestCase) {
            return;
        }

        if (!$test->hasOutput()) {
            return;
        }

        foreach (explode("\n", trim($test->getActualOutput())) as $line) {
            $this->write(sprintf("# %s\n", $line));
        }
    }
}
