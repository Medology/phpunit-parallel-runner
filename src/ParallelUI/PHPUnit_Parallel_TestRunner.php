<?php namespace PHPUnit\ParallelRunner;

use PHPUnit_Framework_Test as Test;
use PHPUnit_Framework_TestResult as TestResult;
use PHPUnit_Framework_TestSuite as TestSuite;
use PHPUnit_Runner_Filter_Group_Exclude as ExcludeGroupFilterIterator;
use PHPUnit_Runner_Filter_Factory as Factory;
use PHPUnit_Runner_Filter_Group_Include as IncludeGroupFilterIterator;
use PHPUnit_TextUI_TestRunner as TestRunner;
use ReflectionClass;
use ReflectionException;

/**
 * A Parallel test runner for CLI.
 */
class PHPUnit_Parallel_TestRunner extends TestRunner
{
    const PARALLEL_ARG = 'parallelNodes';

    /**
     * Processes a potentially nested test suite based on various filters through the CLI.
     *
     * @param  TestSuite           $suite     The suite to filter
     * @param  array               $arguments The CLI arguments
     * @throws ReflectionException When an error occurred at accessing the class filters.
     */
    private function processSuiteFilters(TestSuite $suite, array $arguments): void
    {
        if (empty($arguments['filter']) &&
            empty($arguments[self::PARALLEL_ARG]) &&
            empty($arguments['groups']) &&
            empty($arguments['excludeGroups'])) {
            return;
        }

        $filterFactory = new Factory();

        if (!empty($arguments['excludeGroups'])) {
            $filterFactory->addFilter(
                new ReflectionClass(ExcludeGroupFilterIterator::class),
                $arguments['excludeGroups']
            );
        }

        if (!empty($arguments['groups'])) {
            $filterFactory->addFilter(
                new ReflectionClass(IncludeGroupFilterIterator::class),
                $arguments['groups']
            );
        }

        if (!empty($arguments['filter'])) {
            $filterFactory->addFilter(
                new ReflectionClass('PHPUnit_Runner_Filter_Test'),
                $arguments['filter']
            );
        }

        if (!empty($arguments[self::PARALLEL_ARG])) {
            $filterFactory->addFilter(
                new ReflectionClass('PhpUnit\Runner\Filter\Parallel'),
                $arguments[self::PARALLEL_ARG]
            );
        }

        $suite->injectFilter($filterFactory);
    }

    /**
     * {@inheritdoc}
     */
    public function doRun(Test $suite, array $arguments = [],  $exit = true): TestResult
    {
        $this->processSuiteFilters($suite, $arguments);

        return call_user_func_array(['parent', 'doRun'], func_get_args());
    }
}
