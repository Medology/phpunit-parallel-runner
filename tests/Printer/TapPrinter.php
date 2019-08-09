<?php namespace CustomPrinter;

use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestFailure;
use PHPUnit\Framework\TestListener;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\Warning;
use PHPUnit\TextUI\TestRunner;
use PHPUnit\Util\Printer;
use Symfony\Component\Yaml\Dumper;
use Throwable;

class TapPrinter extends Printer implements TestListener
{

    /**
     * @var int
     */
    protected $testNumber = 0;

    /**
     * @var int
     */
    protected $testSuiteLevel = 0;

    /**
     * @var bool
     */
    protected $testSuccessful = true;

    /**
     * @var int
     */
    protected $warningCount = 0;


    public function __construct($out = null)
    {
        parent::__construct($out);
        $this->write("TAP version 13\n");

    }


    /**
     * An error occurred.
     * @param Test $test
     * @param Throwable $t
     * @param float $time
     */
    public function addError(Test $test, Throwable $t, float $time): void
    {
        $this->writeNotOk($test, 'Error');
    }

    /**
     * A warning occurred.
     * @param Test $test
     * @param Warning $e
     * @param float $time
     */
    public function addWarning(Test $test, Warning $e, float $time): void
    {
        $this->warningCount++;
    }

    /**
     * A failure occurred.
     * @param Test $test
     * @param AssertionFailedError $e
     * @param float $time
     */
    public function addFailure(Test $test, AssertionFailedError $e, float $time): void
    {
        $this->writeNotOk($test, 'Failure');

        $message = explode(
            "\n",
            TestFailure::exceptionToString($e)
        );

        $diagnostic = array(
            'message' => $message[0],
            'severity' => 'fail'
        );

        if ($e instanceof ExpectationFailedException) {
            $cf = $e->getComparisonFailure();

            if ($cf !== null) {
                $diagnostic['data'] = array(
                    'got' => $cf->getActual(),
                    'expected' => $cf->getExpected()
                );
            }
        }
        $yaml = new Dumper();

        $this->write(
            sprintf(
                "  ---\n%s  ...\n",
                $yaml->dump($diagnostic, 2, 2)
            )
        );
    }

    /**
     * Incomplete test.
     * @param Test $test
     * @param Throwable $t
     * @param float $time
     */
    public function addIncompleteTest(Test $test, Throwable $t, float $time): void
    {
        $this->writeNotOk($test, '', 'TODO Incomplete Test');
    }

    /**
     * Risky test.
     * @param Test $test
     * @param Throwable $t
     * @param float $time
     */
    public function addRiskyTest(Test $test, Throwable $t, float $time): void
    {
        $this->write(
            sprintf(
                "ok %d - # RISKY%s\n",
                $this->testNumber,
                $t->getMessage() != '' ? ' ' . $t->getMessage() : ''
            )
        );

        $this->testSuccessful = false;
    }

    /**
     * Skipped test.
     * @param Test $test
     * @param Throwable $t
     * @param float $time
     */
    public function addSkippedTest(Test $test, Throwable $t, float $time): void
    {
        $this->write(
            sprintf(
                "ok %d - # SKIP%s\n",
                $this->testNumber,
                $t->getMessage() != '' ? ' ' . $t->getMessage() : ''
            )
        );

        $this->testSuccessful = false;
    }

    /**
     * A test suite started.
     * @param TestSuite $suite
     */
    public function startTestSuite(TestSuite $suite): void
    {
        $this->testSuiteLevel++;
    }

    /**
     * A test suite ended.
     * @param TestSuite $suite
     */
    public function endTestSuite(TestSuite $suite): void
    {
        $this->testSuiteLevel--;

        if ($this->testSuiteLevel === 0) {
            $this->write(sprintf("1..%d\n", $this->testNumber));
        }
    }

    /**
     * A test started.
     * @param Test $test
     */
    public function startTest(Test $test): void
    {
        $this->testNumber++;
        $this->testSuccessful = true;
    }

    /**
     * A test ended.
     * @param Test $test
     * @param float $time
     */
    public function endTest(Test $test, float $time): void
    {
        if ($this->testSuccessful === true) {
            $this->write(
                sprintf(
                    "ok %d - %s\n",
                    $this->testNumber,
                    \PHPUnit\Util\Test::describeAsString($test)
                )
            );
        }

        $this->writeDiagnostics($test);
    }

    /**
     * @param Test $test
     * @param string $prefix
     * @param string $directive
     */
    protected function writeNotOk(Test $test, $prefix = '', $directive = '')
    {
        $this->write(
            sprintf(
                "not ok %d - %s%s%s\n",
                $this->testNumber,
                $prefix != '' ? $prefix . ': ' : '',
                \PHPUnit\Util\Test::describe($test),
                $directive != '' ? ' # ' . $directive : ''
            )
        );

        $this->testSuccessful = false;
    }


    private function writeDiagnostics(Test $test)
    {
        if (!$test instanceof TestCase) {
            return;
        }

        if (!$test->hasOutput()) {
            return;
        }

        foreach (explode("\n", trim($test->getActualOutput())) as $line) {
            $this->write(
                sprintf(
                    "# %s\n",
                    $line
                )
            );
        }
    }
}
