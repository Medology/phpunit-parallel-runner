<?php namespace PHPUnit\ParallelRunner;

use InvalidArgumentException;
use PHPUnit\Framework\Exception;
use PHPUnit\TextUI\Command;
use PHPUnit\TextUI\TestRunner;
use PHPUnit\Util\Getopt;
use RuntimeException;

/**
 * A Parallel Command runner for CLI.
 */
class PHPUnit_Parallel_Command extends Command
{
    public function __construct()
    {
        $this->longOptions += [
            'current-node=' => null,
            'total-nodes='  => null,
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function createRunner(): TestRunner
    {
        return new PHPUnit_Parallel_TestRunner($this->arguments['loader']);
    }

    /**
     * {@inheritdoc}
     */
    protected function handleArguments(array $argv): void
    {
        try {
            $this->options = Getopt::getopt(
                $argv,
                'd:c:hv',
                array_keys($this->longOptions)
            );
        } catch (Exception $e) {
            throw new InvalidArgumentException($e->getMessage());
        }

        $this->arguments[PHPUnit_Parallel_TestRunner::PARALLEL_ARG] = [];

        foreach ($this->options[0] as $option) {
            switch ($option[0]) {
                case '--current-node':
                    $this->arguments[PHPUnit_Parallel_TestRunner::PARALLEL_ARG][0] = $option[1];

                    break;
                case '--total-nodes':
                    $this->arguments[PHPUnit_Parallel_TestRunner::PARALLEL_ARG][1] = $option[1];

                    break;
            }
        }

        if (count($this->arguments[PHPUnit_Parallel_TestRunner::PARALLEL_ARG]) == 0) {
            unset($this->arguments[PHPUnit_Parallel_TestRunner::PARALLEL_ARG]);
        } elseif (count($this->arguments[PHPUnit_Parallel_TestRunner::PARALLEL_ARG]) != 2) {
            throw new RuntimeException('Both --current-node and --total-nodes are required for parallelism');
        }

        parent::handleArguments($argv);
    }

    /**
     * {@inheritdoc}
     */
    protected function showHelp(): void
    {
        parent::showHelp();

        echo <<<'EOT'

Parallel Options:

  --current-node <num>  The index of this node in the parallel cluster. Default 0.
  --total-nodes  <num>  The total number of nodes in the parallel cluster. Default 1.

EOT;
    }
}
