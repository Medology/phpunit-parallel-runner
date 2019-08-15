<?php namespace PHPunit\ParallelRunner\Tests;

use Exception;
use PHPUnit\Framework\TestCase;
use PHPUnit\ParallelRunner\PHPUnit_Parallel_Command;
use PHPUnit\ParallelRunner\PHPUnit_Parallel_TestRunner;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use RuntimeException;

/**
 * Class CommandTest, contains tests related to the instantiation and execution of the parallel runner.
 */
class CommandTest extends TestCase
{
    /**
     * Access the hidden method and make it accessible for the test.
     *
     * @param  string              $class  String with the class name
     * @param  string              $method String with the method name.
     * @throws ReflectionException When the class or function name cannot be found/accessed.
     *
     * @return ReflectionMethod
     */
    private function getHiddenMethod(string $class, string $method): ReflectionMethod
    {
        $r = new ReflectionClass($class);
        $f = $r->getMethod($method);
        $f->setAccessible(true);

        return $f;
    }

    /**
     * Gets and return the stdOut output.
     *
     * @param  callable $trigger
     * @return string
     */
    private function getStdOut(callable $trigger): string
    {
        ob_start();
        $trigger();

        return ob_get_clean();
    }

    /**
     * Tests and assert the correct creation of an instance of the runner.
     *
     * @throws ReflectionException When the class or function name cannot be found/accessed.
     */
    public function testCreateRunnerReturnsParallelRunner(): void
    {
        $cmd = new PHPUnit_Parallel_Command();
        $f = $this->getHiddenMethod(get_class($cmd), 'createRunner');

        $this->assertInstanceOf(PHPUnit_Parallel_TestRunner::class, $f->invokeArgs($cmd, []));
    }

    /**
     * Tests and assert the correct display/return of the current options for the command showHelp.
     *
     * @throws ReflectionException When the class or function name cannot be found/accessed.
     */
    public function testHelpShowsParallelParameters(): void
    {
        $cmd = new PHPUnit_Parallel_Command();
        $f = $this->getHiddenMethod(get_class($cmd), 'showHelp');

        $help = $this->getStdOut(function () use ($cmd, $f) {
            $f->invokeArgs($cmd, []);
        });

        $this->assertContains('--current-node', $help);
        $this->assertContains('--total-nodes', $help);
    }

    /**
     * Return the data for the tests.
     *
     * @return array
     */
    public function singleParameterProvider(): array
    {
        return [
            [['--current-node=0']],
            [['--total-nodes=1']],
        ];
    }

    /**
     * Tests assert the command would fail when both parameters are not provided.
     *
     * @dataProvider singleParameterProvider The helper function used to get the information for tests.
     * @param  array               $args Arguments used for the test.
     * @throws ReflectionException When the class or function name cannot be found/accessed.
     */
    public function testCmdFailsWhenBothParamsAreNotProvided(array $args): void
    {
        $cmd = new PHPUnit_Parallel_Command();
        $f = $this->getHiddenMethod(get_class($cmd), 'handleArguments');

        try {
            $f->invokeArgs($cmd, [$args]);
            $this->expectException(RuntimeException::class);
        } catch (Exception $e) {
            $this->assertInstanceOf(RuntimeException::class, $e);
            $this->assertContains('Both --current-node and --total-nodes are required for parallelism', $e->getMessage());
        }
    }
}
