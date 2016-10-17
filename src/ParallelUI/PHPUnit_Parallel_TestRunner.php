<?php namespace PHPUnit\ParallelRunner;

use PHPUnit_Runner_Filter_Factory;
use PHPUnit_TextUI_TestRunner;
use PHPUnit_Framework_Test;
use PHPUnit_Framework_TestSuite;
use ReflectionClass;

/**
 * A Parallel test runner for CLI
 */
class PHPUnit_Parallel_TestRunner extends PHPUnit_TextUI_TestRunner
{
    const PARALLEL_ARG = 'parallelNodes';

    /**
     * Processes a potentially nested test suite based on various filters through the CLI.
     *
     * @param PHPUnit_Framework_TestSuite $suite     The suite to filter
     * @param array                       $arguments The CLI arguments
     */
    private function processSuiteFilters(PHPUnit_Framework_TestSuite $suite, array $arguments)
    {
        if (empty($arguments['filter']) &&
            empty($arguments[self::PARALLEL_ARG]) &&
            empty($arguments['groups']) &&
            empty($arguments['excludeGroups'])) {
            return;
        }

        $filterFactory = new PHPUnit_Runner_Filter_Factory();

        if (!empty($arguments['excludeGroups'])) {
            $filterFactory->addFilter(
                new ReflectionClass('PHPUnit_Runner_Filter_Group_Exclude'),
                $arguments['excludeGroups']
            );
        }

        if (!empty($arguments['groups'])) {
            $filterFactory->addFilter(
                new ReflectionClass('PHPUnit_Runner_Filter_Group_Include'),
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
    public function doRun(PHPUnit_Framework_Test $suite, array $arguments = array())
    {
        $this->processSuiteFilters($suite, $arguments);

        call_user_func_array(['parent', 'doRun'], func_get_args());
    }
}
