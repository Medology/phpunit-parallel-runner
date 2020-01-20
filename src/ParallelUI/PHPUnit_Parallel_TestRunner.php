<?php

namespace PHPUnit\ParallelRunner;

use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestResult;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Runner\Filter\ExcludeGroupFilterIterator;
use PHPUnit\Runner\Filter\Factory;
use PHPUnit\Runner\Filter\IncludeGroupFilterIterator;
use PHPUnit\TextUI\TestRunner;
use ReflectionClass;
use ReflectionException;

/**
 * A Parallel test runner for CLI.
 */
class PHPUnit_Parallel_TestRunner extends TestRunner
{
    const PARALLEL_ARG = 'parallelNodes';

    /**
     * {@inheritdoc}
     */
    public function doRun(Test $suite, array $arguments = [], bool $exit = true): TestResult
    {
        $this->processSuiteFilters($suite, $arguments);

        return call_user_func_array(['parent', 'doRun'], func_get_args());
    }

    /**
     * Processes a potentially nested test suite based on various filters through the CLI.
     *
     * @param TestSuite $suite     The suite to filter
     * @param array     $arguments The CLI arguments
     *
     * @throws ReflectionException when an error occurred at accessing the class filters
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
}
